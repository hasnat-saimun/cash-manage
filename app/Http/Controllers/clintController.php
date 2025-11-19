<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class clintController extends Controller
{
    public function clientCreation(){
        return view('client.clientCreation');
    }

    public function saveClient(Request $request){
        // Validate the request data
        $validatedData = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|unique:client_creations,client_email',
            'client_phone' => 'required|string|max:15',
            'client_acNum' => 'required|string|max:50',
            'client_regDate' => 'required|date',
        ]);

        // Create a new client record
        \App\Models\ClientCreation::create($validatedData);

        // Redirect back with a success message
        return redirect()->route('clientCreation')->with('success', 'Client created successfully!');
    }
}
