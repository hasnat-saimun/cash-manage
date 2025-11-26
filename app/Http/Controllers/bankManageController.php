<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\bankManage;
use App\Models\bankAccount;

class bankManageController extends Controller
{
    // bank manage methods will go here
    //bank manage route
    public function bankManageView()
    {
        $bnakManages = bankManage::all();
        return view('bank.bankManage', ['bankManages' => $bnakManages]);
    }

    //bank manage save route
    public function saveBankManage(Request $request)
    {
        $data = new bankManage();
        $data->bank_name = $request->bankName;
        $data->branch_name = $request->branchName;
        $data->routing_number = $request->routingNumber;

        if ($data->save()) :
            return back()->with('success', 'Success! data added successfully');
        else :
            return back()->with('error', 'Opps! data addition failed. Please try later');
        endif;
    }

    //bank manage edit function
    public function bankManageEdit($id)
    {
        $bankManage = bankManage::find($id);
        return view('bank.bankManage', [
            'itemId' => $id,
        ]);
    }

    //bank manage update function
    public function updateBankManage(Request $request)
    {
        $data = bankManage::find($request->itemId);
        $data->bank_name = $request->bankName;
        $data->branch_name = $request->branchName;
        $data->routing_number = $request->routingNumber;  
        if ($data->save()) :
            return redirect(route('bankManageView'))->with('success', 'Success! data updated successfully');
        else :
            return back()->with('error', 'Opps! data update failed. Please try later');
        endif;  
    }

    //bank manage delete function
    public function deleteBankManage($id)
    {
        $data = bankManage::find($id);
        if ($data->delete()) :
            return back()->with('success', 'Success! data deleted successfully');
        else :
            return back()->with('error', 'Opps! data deletion failed. Please try later');
        endif;
    }

    //bank account creation view function
    public function bankAccountCreationView()
    {
        $bankAccounts = bankAccount::join('bank_manages', 'bank_accounts.bank_manage_id', '=', 'bank_manages.id')
                        ->select('bank_manages.bank_name','bank_manages.branch_name','bank_manages.routing_number','bank_accounts.*')
                        ->get();
        return view('bank.bankAccountCreation', ['bankAccounts' => $bankAccounts]);   
    }

    //bank account save function
    public function saveBankAccount(Request $request)
    {
        $data = new bankAccount();
        $data->account_name     = $request->fullName;
        $data->account_number   = $request->accountNumber;
        $data->bank_manage_id   = $request->bankManageId;
        $data->entry_date       = $request->entryDate;
        $data->opning_balance   = $request->opningBalance;    
        if ($data->save()) :
            return back()->with('success', 'Success! Bank Account created successfully');
        else :
            return back()->with('error', 'Opps! Bank Account creation failed. Please try later');
        endif;
    }


    

}
