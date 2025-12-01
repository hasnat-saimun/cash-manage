<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;
use App\Models\bankTransaction;

class transactionController extends Controller
{
    public function transactionCreation()
    {
        return view('transaction.clientTransactionCreation');
    }

    public function saveTransaction(Request $request)
    {
    
         if(empty($request->itemId)):
            $transaction   = new transaction();
        else:
            $transaction   = transaction::find($request->itemId);
        endif;
        $transaction->transaction_client_name = $request->clientId;
        $transaction->type = $request->type;
        $transaction->transaction_source = $request->sourceId;
        $transaction->amount = $request->amount;
        $transaction->date = $request->date;
        $transaction->description = $request->description;

        if ($transaction->save()) :
        return back()->with('success', 'Transaction saved successfully.');
        else :
        return back()->with('error', 'Failed to save transaction. Please try again.');
        endif;
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

