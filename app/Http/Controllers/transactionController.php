<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;

class transactionController extends Controller
{
    public function transactionCreation()
    {
        return view('transaction.transactionCreation');
    }

    public function saveTransaction(Request $request)
    {
    
         if(empty($request->itemId)):
            $transaction   = new transaction();
        else:
            $transaction   = transaction::find($request->itemId);
        endif;
        $transaction->account_number = $request->accNumId;
        $transaction->type = $request->type;
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
        $transactions = transaction::join('client_creations', 'transactions.account_number', '=', 'client_creations.id')->select('client_creations.client_name', 'client_creations.client_acNum','transactions.*')->get();
        return view('transaction.transactionList', ['transactions' => $transactions]);
    }

    public function transactionEdit($id)
    {
        $transaction = transaction::find($id);

        return view('transaction.transactionCreation', ['itemId' => $id]);
    }

}
