<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;

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

        return view('transaction.transactionCreation', ['itemId' => $id]);
    }

}
