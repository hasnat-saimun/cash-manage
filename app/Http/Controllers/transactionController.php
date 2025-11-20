<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class transactionController extends Controller
{
    
    // transaction creation view load
    public function transactionCreation()
    {
        return view('transaction.transactionCreation');
    }
}
