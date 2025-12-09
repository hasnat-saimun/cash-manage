<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\clientCreation;
use App\Models\transaction;
use App\Models\bankTransaction;
use App\Models\clientBalance;

class transactionController extends Controller
{
    public function transactionCreation()
    {
        return view('transaction.clientTransactionCreation');
    }

    // helper: apply delta to client's current balance in client_balances and return new balance
    protected function applyClientBalanceDelta(int $clientId, float $delta): float
    {
        $now = Carbon::now();

        // Use DB transaction and lock to avoid race conditions
        DB::beginTransaction();
        try {
            // try to lock existing row
            $exists = DB::table('client_balances')->where('client_id', $clientId)->lockForUpdate()->first();

            if ($exists) {
                // update existing balance
                DB::table('client_balances')->where('client_id', $clientId)->update([
                    'balance'    => DB::raw("balance + ({$delta})"),
                    'updated_at' => $now,
                ]);
            } else {
                // insert new balance row
                DB::table('client_balances')->insert([
                    'client_id'  => $clientId,
                    'balance'    => $delta,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // fetch the new balance
            $newBalance = (float) DB::table('client_balances')->where('client_id', $clientId)->value('balance');

            DB::commit();
            return $newBalance;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // save or update client transaction
    public function saveTransaction(Request $request)
    {
        // basic validation (adjust rules to your schema)
        $data = $request->validate([
            'itemId'   => 'nullable|integer',
            'clientId' => 'required|integer|exists:client_creations,id',
            'sourceId' => 'nullable',
            'type'     => 'required|in:Debit,Credit',
            'amount'   => 'required|numeric',
            'date'     => 'required|date',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $itemId = $data['itemId'] ?? null;
            $clientId = (int)$data['clientId'];
            $type = $data['type'];
            $amount = (float)$data['amount'];

            if ($itemId) {
                // update existing
                $txn = transaction::find($itemId);
                if (!$txn) {
                    DB::rollBack();
                    return redirect()->back()->with('error','Transaction not found.');
                }

                $oldType = $txn->type;
                $oldAmount = (float) $txn->amount;
                $oldEffect = (strtolower($oldType) === 'credit') ? $oldAmount : -$oldAmount;
                $newEffect = (strtolower($type) === 'credit') ? $amount : -$amount;
                $delta = $newEffect - $oldEffect;

                // update transaction fields first
                $txn->transaction_client_name = $clientId;
                $txn->transaction_source = $data['sourceId'] ?? null;
                $txn->type = $type;
                $txn->amount = $amount;
                $txn->date = $data['date'];
                $txn->description = $data['description'] ?? null;
                $txn->save();

                // apply delta and get new balance
                if ($delta != 0) {
                    $newBalance = $this->applyClientBalanceDelta($clientId, $delta);
                } else {
                    // no change to balance
                    $newBalance = (float) DB::table('client_balances')->where('client_id', $clientId)->value('balance');
                }

                // save txnBalance on transaction
                $txn->txnBalance = $newBalance;
                $txn->save();
            } else {
                // create new transaction
                $txn = new transaction();
                $txn->transaction_client_name = $clientId;
                $txn->transaction_source = $data['sourceId'] ?? null;
                $txn->type = $type;
                $txn->amount = $amount;
                $txn->date = $data['date'];
                $txn->description = $data['description'] ?? null;
                $txn->save();

                // effect: +amount for Credit, -amount for Debit
                $effect = (strtolower($type) === 'credit') ? $amount : -$amount;

                // apply effect and get new balance
                $newBalance = $this->applyClientBalanceDelta($clientId, $effect);

                // persist txnBalance
                $txn->txnBalance = $newBalance;
                $txn->save();
            }

            DB::commit();
            return redirect()->route('transactionList')->with('success','Transaction saved.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // optionally log $e->getMessage()
            return redirect()->back()->with('error','Failed to save transaction.');
        }
    }

    // helper: recalculate client's closing balance, persist into client_balances and client creation table
    protected function recalculateClientBalance(int $clientId)
    {
        $client = clientCreation::find($clientId);
        if (!$client) {
            return;
        }

        // Determine base/opening balance using best-known columns (don't assume 'balance' exists)
        $opening = 0;
        if (isset($client->opening_balance)) {
            $opening = (float) $client->opening_balance;
        } elseif (isset($client->initial_balance)) {
            $opening = (float) $client->initial_balance;
        } elseif (isset($client->client_balance)) {
            $opening = (float) $client->client_balance;
        } elseif (isset($client->amount)) {
            $opening = (float) $client->amount;
        } elseif (isset($client->balance)) {
            $opening = (float) $client->balance;
        }

        // Sum credits and debits for this client
        $totals = transaction::where('transaction_client_name', $clientId)
            ->selectRaw("
                SUM(CASE WHEN LOWER(type) = 'credit' THEN amount ELSE 0 END) as total_credit,
                SUM(CASE WHEN LOWER(type) = 'debit'  THEN amount ELSE 0 END) as total_debit
            ")
            ->first();

        $totalCredit = (float) ($totals->total_credit ?? 0);
        $totalDebit  = (float) ($totals->total_debit ?? 0);

        $closing = $opening + $totalCredit - $totalDebit;

        // Persist into client_balances table (insert or update)
        $exists = DB::table('client_balances')->where('client_id', $clientId)->first();
        if ($exists) {
            DB::table('client_balances')->where('client_id', $clientId)->update([
                'balance' => $closing,
                'updated_at' => Carbon::now(),
            ]);
        } else {
            DB::table('client_balances')->insert([
                'client_id'  => $clientId,
                'balance'    => $closing,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Update client creation table's balance column if one exists (try common names)
        $clientTable = $client->getTable();
        $columnsToTry = ['balance', 'client_balance', 'client_amount', 'amount', 'opening_balance', 'initial_balance'];

        $updated = false;
        foreach ($columnsToTry as $col) {
            if (Schema::hasColumn($clientTable, $col)) {
                try {
                    $client->$col = $closing;
                    $client->save();
                    $updated = true;
                } catch (\Throwable $e) {
                    // silently continue; we already persisted to client_balances
                }
                break;
            }
        }

        // If no known column exists on client table, do nothing further (closing stored in client_balances)
    }
    
    public function transactionList()
    {
        $transactions = transaction::join('client_creations', 'transactions.transaction_client_name', '=', 'client_creations.id')->select('client_creations.client_name','transactions.*')->get();
        return view('transaction.clientTransactionList', ['transactions' => $transactions]);
    }

    public function transactionEdit($id)
    {
        $transaction = transaction::find($id);

        return view('transaction.clientTransactionCreation', ['itemId' => $id]);
    }

    // Delete transaction and reverse its effect on client balance
    public function deleteTransaction($id)
    {
        DB::beginTransaction();
        try {
            $txn = transaction::find($id);
            if (!$txn) {
                DB::rollBack();
                return redirect()->route('transactionList')->with('error','Transaction not found.');
            }

            $clientId = (int) $txn->transaction_client_name;
            $amount = (float) $txn->amount;
            $type = $txn->type;

            // reverse effect: if Credit then subtract, if Debit then add
            $reverse = (strtolower($type) === 'credit') ? -$amount : $amount;
            // apply reverse and get new balance (we don't need to store txnBalance for deleted record)
            $this->applyClientBalanceDelta($clientId, $reverse);

            $txn->delete();

            DB::commit();
            return redirect()->route('transactionList')->with('success','Transaction deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('transactionList')->with('error','Failed to delete transaction.');
        }
    }

    //bank transaction creation
    public function bankTransactionCreation()
    {
        return view('transaction.bankTransactionCreation');  
    }

    //bank transaction save
    public function saveBankTransaction(Request $request)
    {
        if(empty($request->itemId)):
            $transaction   = new bankTransaction();
        else:
            $transaction   = bankTransaction::find($request->itemId);
        endif;
        $transaction->bank_account_id = $request->accountId;
        $transaction->transaction_type = $request->type;
        $transaction->amount = $request->amount;
        $transaction->transaction_date = $request->date;
        $transaction->description = $request->description;  

        if ($transaction->save()) :
        return back()->with('success', 'Transaction saved successfully.');
        else :
        return back()->with('error', 'Failed to save transaction. Please try again.');
        endif;
    }

    //bank transaction list
    public function bankTransactionList()
    {
        $transactions = bankTransaction::join('bank_accounts', 'bank_transactions.bank_account_id', '=', 'bank_accounts.id')->select('bank_accounts.account_name','bank_accounts.account_number','bank_transactions.*')->get();
        return view('transaction.bankTransactionList', ['transactions' => $transactions]);
    }

    //bank transaction edit
    public function bankTransactionEdit($id)
    {
        $transaction = bankTransaction::find($id)::join('bank_accounts', 'bank_transactions.bank_account_id', '=', 'bank_accounts.id')->select('bank_accounts.account_name','bank_accounts.account_number','bank_transactions.*')->where('bank_transactions.id', $id)->first();
        return view('transaction.bankTransactionCreation', ['itemId' => $id], ['transaction' => $transaction]);
    }

    //bank transaction delete
    public function deleteBankTransaction($id)
    {
        $data = bankTransaction::find($id);
        if ($data->delete()) :
            return back()->with('success', 'Success! transaction deleted successfully');
        else :
            return back()->with('error', 'Opps! transaction deletion failed. Please try later');
        endif;
    }

}

