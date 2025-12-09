<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    public function saveTransaction(Request $request)
    {
        // Determine if create or update and compute balance delta
        if (empty($request->itemId)) {
            // creating new transaction
            $transaction = new transaction();
            $oldSigned = 0.0;
        } else {
            // editing existing transaction - capture old signed amount
            $transaction = transaction::find($request->itemId);
            $oldType = $transaction->type ?? null;
            $oldAmount = (float) ($transaction->amount ?? 0);
            $oldSigned = ($oldType === 'Debit') ? $oldAmount : -$oldAmount;
        }

        // assign new values
        $transaction->transaction_client_name = $request->clientId;
        $transaction->type = $request->type;
        $transaction->transaction_source = $request->sourceId;
        $transaction->amount = $request->amount;
        $transaction->date = $request->date;
        $transaction->description = $request->description;

        if ($transaction->save()) {
            // compute signed new amount: Debit -> +amount, Credit -> -amount
            $newSigned = ($request->type === 'Debit') ? (float) $request->amount : - (float) $request->amount;
            // delta to apply to client balance
            $delta = $newSigned - $oldSigned;

            // update or create client balance row
            $clientId = $request->clientId;
            $cb = clientBalance::firstOrCreate(
                ['client_id' => $clientId],
                ['balance' => 0.0]
            );

            // apply delta
            $cb->balance = round((float)$cb->balance + $delta, 2);
            $cb->save();

            return back()->with('success', 'Transaction saved successfully.');
        } else {
            return back()->with('error', 'Failed to save transaction. Please try again.');
        }
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

    public function deleteTransaction($id)
    {
        $data = transaction::find($id);
        if ($data->delete()) :
            return back()->with('success', 'Success! transaction deleted successfully');
        else :
            return back()->with('error', 'Opps! transaction deletion failed. Please try later');
        endif;
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

