<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;

class transactionController extends Controller
{
    public function transactionCreation()
    {
        $clients = clientCreation::all();
        return view('transaction.transactionCreation',['clients'=>$clients]);
    }

    public function saveTransaction(Request $request)
    {
        $transaction = new transaction();
        $transaction->account_number = $request->accNumId;
        $transaction->type = $request->type;
        $transaction->amount = $request->amount;
        $transaction->date = $request->date;
        $transaction->description = $request->description;
        $transaction->save();

        if ($transaction->save()) :
        return redirect()->route('transactionCreation')->with('success', 'Transaction saved successfully.');
        else :
        return redirect()->route('transactionCreation')->with('error', 'Failed to save transaction. Please try again.');
        endif;
    }
    
    public function transactionList()
    {
        $transactions = transaction::join('client_creations', 'transactions.account_number', '=', 'client_creations.id')->select('client_creations.client_name', 'client_creations.client_acNum','transactions.*')->get();
        return view('transaction.transactionList', ['transactions' => $transactions]);
    }

}
