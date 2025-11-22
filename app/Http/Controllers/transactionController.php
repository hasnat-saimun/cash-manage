<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\Transaction;

class transactionController extends Controller
{
    public function transactionCreation()
    {
        $clients = clientCreation::all();
        return view('transaction.transactionCreation',['clients'=>$clients]);
    }
}
