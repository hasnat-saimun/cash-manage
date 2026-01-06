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
            $exists = DB::table('client_balances')
                ->where('client_id', $clientId)
                ->where('client_balances.business_id', request()->session()->get('business_id'))
                ->lockForUpdate()->first();

            if ($exists) {
                // update existing balance
                DB::table('client_balances')
                    ->where('client_id', $clientId)
                    ->where('client_balances.business_id', request()->session()->get('business_id'))
                    ->update([
                    'balance'    => DB::raw("balance + ({$delta})"),
                    'updated_at' => $now,
                ]);
            } else {
                // insert new balance row
                DB::table('client_balances')->insert([
                    'client_id'  => $clientId,
                    'business_id' => request()->session()->get('business_id'),
                    'balance'    => $delta,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // fetch the new balance
            $newBalance = (float) DB::table('client_balances')
                ->where('client_id', $clientId)
                ->where('client_balances.business_id', request()->session()->get('business_id'))
                ->value('balance');

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
                    $newBalance = (float) DB::table('client_balances')
                        ->where('client_id', $clientId)
                        ->where('client_balances.business_id', request()->session()->get('business_id'))
                        ->value('balance');
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
                $txn->business_id = $request->session()->get('business_id');
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
            return redirect()->back()->with('success','Transaction saved.');
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
        $exists = DB::table('client_balances')
            ->where('client_id', $clientId)
            ->where('client_balances.business_id', request()->session()->get('business_id'))
			->first();
        if ($exists) {
            DB::table('client_balances')
                ->where('client_id', $clientId)
                ->where('client_balances.business_id', request()->session()->get('business_id'))
                ->update([
                'balance' => $closing,
                'updated_at' => Carbon::now(),
            ]);
        } else {
            DB::table('client_balances')->insert([
                'client_id'  => $clientId,
                'business_id' => request()->session()->get('business_id'),
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
        $bizId = request()->session()->get('business_id');
        $transactions = transaction::join('client_creations', 'transactions.transaction_client_name', '=', 'client_creations.id')
            ->where('transactions.business_id', $bizId)
            ->select('client_creations.client_name','transactions.*')
            ->orderBy('transactions.date', 'desc')
            ->orderBy('transactions.id', 'desc')
            ->get();

        // Top calculations based on client transaction history
        $today = now()->toDateString();
        $weekStart = now()->copy()->subDays(6)->toDateString();
        $monthStart = now()->copy()->subDays(29)->toDateString();

        $dailyNet = \DB::table('transactions')
            ->where('business_id', $bizId)
            ->whereDate('date', $today)
            ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(type) = "credit" THEN amount ELSE -amount END),0) as net')
            ->value('net');
        $weeklyNet = \DB::table('transactions')
            ->where('business_id', $bizId)
            ->whereBetween('date', [$weekStart, $today])
            ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(type) = "credit" THEN amount ELSE -amount END),0) as net')
            ->value('net');
        $monthlyNet = \DB::table('transactions')
            ->where('business_id', $bizId)
            ->whereBetween('date', [$monthStart, $today])
            ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(type) = "credit" THEN amount ELSE -amount END),0) as net')
            ->value('net');

        $lastTxn = \DB::table('transactions')
            ->where('business_id', $bizId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->select('amount','type','date')
            ->first();

        $outstandingBalance = \DB::table('client_balances')
            ->where('business_id', $bizId)
            ->sum('balance');

        return view('transaction.clientTransactionList', [
            'transactions' => $transactions,
            'dailyNet' => (float)($dailyNet ?? 0),
            'weeklyNet' => (float)($weeklyNet ?? 0),
            'monthlyNet' => (float)($monthlyNet ?? 0),
            'lastTxn' => $lastTxn,
            'outstandingBalance' => (float)$outstandingBalance,
        ]);
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

    // Bulk delete transactions and reverse their effects
    public function bulkDeleteTransactions(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);

        $bizId = $request->session()->get('business_id');

        DB::beginTransaction();
        try {
            $ids = $data['ids'];

            // Fetch transactions with lock to avoid race conditions
            $txns = transaction::whereIn('id', $ids)
                ->where('business_id', $bizId)
                ->lockForUpdate()
                ->get();

            if ($txns->isEmpty()) {
                DB::rollBack();
                return redirect()->route('transactionList')->with('error','No transactions found to delete.');
            }

            // Group deltas by client so we can apply balance updates once per client
            $deltasByClient = [];
            foreach ($txns as $txn) {
                $clientId = (int) $txn->transaction_client_name;
                $amount = (float) $txn->amount;
                $type = strtolower((string) $txn->type);
                $reverse = ($type === 'credit') ? -$amount : $amount;
                if (!isset($deltasByClient[$clientId])) {
                    $deltasByClient[$clientId] = 0.0;
                }
                $deltasByClient[$clientId] += $reverse;
            }

            // Delete transactions
            transaction::whereIn('id', $txns->pluck('id'))->delete();

            // Apply balance deltas per client
            foreach ($deltasByClient as $clientId => $delta) {
                $this->applyClientBalanceDelta($clientId, $delta);
            }

            DB::commit();
            return redirect()->route('transactionList')->with('success','Selected transactions deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('transactionList')->with('error','Failed to delete selected transactions.');
        }
    }

    //bank transaction creation
    public function bankTransactionCreation(Request $request)
    {
        $accounts = DB::table('bank_accounts')
            ->where('business_id', request()->session()->get('business_id'))
            ->get();
        $itemId = $request->query('id');
        $editData = null;
        if ($itemId) {
            $editData = DB::table('bank_transactions')->where('id', $itemId)->first();
        }
        return view('transaction.bankTransactionCreation', compact('accounts', 'editData', 'itemId'));
    }

    // Helper: detect columns for bank_transactions
    protected function detectBankTransactionTypeColumn()
    {
        $candidates = ['type','transaction_type','txn_type','payment_type','tran_type','txnType','trx_type'];
        foreach ($candidates as $c) {
            if (Schema::hasColumn('bank_transactions', $c)) return $c;
        }
        return 'type';
    }
    protected function detectBankTransactionAmountColumn()
    {
        $candidates = ['amount','txn_amount','transaction_amount','credit_amount','debit_amount','amount_paid'];
        foreach ($candidates as $c) {
            if (Schema::hasColumn('bank_transactions', $c)) return $c;
        }
        return 'amount';
    }
    protected function detectBankTransactionDateColumn()
    {
        $candidates = ['date', 'txn_date', 'transaction_date', 'trans_date', 'entry_date'];
        foreach ($candidates as $c) {
            if (Schema::hasColumn('bank_transactions', $c)) return $c;
        }
        return 'date';
    }

    // Helper: update bank_balances incrementally
    protected function applyBankBalanceDelta(int $bankAccountId, float $delta)
    {
        $now = \Carbon\Carbon::now();
        \DB::table('bank_balances')->updateOrInsert(
            ['bank_account_id' => $bankAccountId],
            [
                'balance' => \DB::raw('COALESCE(balance,0) + ' . $delta),
                'updated_at' => $now,
                'created_at' => \DB::raw('COALESCE(created_at, "' . $now->toDateTimeString() . '")')
            ]
        );
    }

    // Save or update bank transaction
    public function saveBankTransaction(Request $request)
    {
        $typeCol = $this->detectBankTransactionTypeColumn();
        $amtCol = $this->detectBankTransactionAmountColumn();
        $dateCol = $this->detectBankTransactionDateColumn();

        $validated = $request->validate([
            'bank_account_id' => 'required|integer|exists:bank_accounts,id',
            'type' => 'required|in:Debit,Credit,credit,debit',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'itemId' => 'nullable|integer'
        ]);

        $bankAccountId = $validated['bank_account_id'];
        $type = strtolower($validated['type']);
        $amount = (float)$validated['amount'];

        \DB::beginTransaction();
        try {
            if (!empty($validated['itemId'])) {
                // Update: reverse old effect, apply new effect
                $txn = \DB::table('bank_transactions')->where('id', $validated['itemId'])->first();
                if ($txn) {
                    $oldType = strtolower($txn->{$typeCol});
                    $oldAmount = (float)$txn->{$amtCol};
                    $oldEffect = $oldType === 'credit' ? $oldAmount : -$oldAmount;
                    $newEffect = $type === 'credit' ? $amount : -$amount;
                    $delta = $newEffect - $oldEffect;
                    $this->applyBankBalanceDelta($bankAccountId, $delta);

                    \DB::table('bank_transactions')->where('id', $validated['itemId'])->update([
                        'bank_account_id' => $bankAccountId,
                        $typeCol => $validated['type'],
                        $amtCol => $validated['amount'],
                        $dateCol => $validated['date'],
                        'description' => $validated['description'] ?? null,
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);
                }
                $msg = 'Bank transaction updated successfully.';
            } else {
                // Insert: apply effect
                $effect = $type === 'credit' ? $amount : -$amount;
                $this->applyBankBalanceDelta($bankAccountId, $effect);

                \DB::table('bank_transactions')->insert([
                    'bank_account_id' => $bankAccountId,
                    'business_id' => $request->session()->get('business_id'),
                    $typeCol => $validated['type'],
                    $amtCol => $validated['amount'],
                    $dateCol => $validated['date'],
                    'description' => $validated['description'] ?? null,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
                $msg = 'Bank transaction saved successfully.';
            }
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->route('bankTransactionList')->with('error', 'Failed to save transaction.');
        }
        return redirect()->route('bankTransactionList')->with('success', $msg);
    }

    // Delete bank transaction
    public function deleteBankTransaction($id)
    {
        $typeCol = $this->detectBankTransactionTypeColumn();
        $amtCol = $this->detectBankTransactionAmountColumn();

        \DB::beginTransaction();
        try {
            $txn = \DB::table('bank_transactions')->where('id', $id)->first();
            if ($txn) {
                $bankAccountId = $txn->bank_account_id;
                $type = strtolower($txn->{$typeCol});
                $amount = (float)$txn->{$amtCol};
                $effect = $type === 'credit' ? -$amount : $amount;
                $this->applyBankBalanceDelta($bankAccountId, $effect);
                \DB::table('bank_transactions')->where('id', $id)->delete();
            }
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->route('bankTransactionList')->with('error', 'Failed to delete transaction.');
        }
        return redirect()->route('bankTransactionList')->with('success', 'Bank transaction deleted.');
    }

    // Bulk delete bank transactions and adjust balances
    public function bulkDeleteBankTransactions(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);

        $typeCol = $this->detectBankTransactionTypeColumn();
        $amtCol = $this->detectBankTransactionAmountColumn();

        $bizId = $request->session()->get('business_id');

        \DB::beginTransaction();
        try {
            $ids = $data['ids'];

            $txns = \DB::table('bank_transactions')
                ->whereIn('id', $ids)
                ->where('business_id', $bizId)
                ->lockForUpdate()
                ->get();

            if ($txns->isEmpty()) {
                \DB::rollBack();
                return redirect()->route('bankTransactionList')->with('error', 'No bank transactions found to delete.');
            }

            $deltasByAccount = [];
            foreach ($txns as $txn) {
                $accountId = (int)$txn->bank_account_id;
                $type = strtolower((string)$txn->{$typeCol});
                $amount = (float)$txn->{$amtCol};
                $reverse = $type === 'credit' ? -$amount : $amount;
                if (!isset($deltasByAccount[$accountId])) {
                    $deltasByAccount[$accountId] = 0.0;
                }
                $deltasByAccount[$accountId] += $reverse;
            }

            \DB::table('bank_transactions')->whereIn('id', $txns->pluck('id'))->delete();

            foreach ($deltasByAccount as $accountId => $delta) {
                $this->applyBankBalanceDelta($accountId, $delta);
            }

            \DB::commit();
            return redirect()->route('bankTransactionList')->with('success', 'Selected bank transactions deleted.');
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->route('bankTransactionList')->with('error', 'Failed to delete selected bank transactions.');
        }
    }

    // List bank transactions
    public function bankTransactionList()
    {
        $typeCol = $this->detectBankTransactionTypeColumn();
        $amtCol = $this->detectBankTransactionAmountColumn();
        $dateCol = $this->detectBankTransactionDateColumn();

        $bizId = request()->session()->get('business_id');
        $txns = \DB::table('bank_transactions')
            ->join('bank_accounts', 'bank_transactions.bank_account_id', '=', 'bank_accounts.id')
            ->select('bank_transactions.*', 'bank_accounts.account_name', 'bank_accounts.account_number')
            ->where('bank_transactions.business_id', $bizId)
            ->orderBy($dateCol, 'asc')
            ->orderBy('bank_transactions.id', 'asc')
            ->get();

        return view('transaction.bankTransactionList', compact('txns','typeCol','amtCol','dateCol'));
    }
}

