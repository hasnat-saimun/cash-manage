<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\bankManage;
use App\Models\bankAccount;
use Illuminate\Support\Facades\DB;

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

    // Save new bank account and initial balance into bank_balances
	public function saveBankAccount(Request $request)
	{
		$validated = $request->validate([
			'account_name'    => 'required|string|max:255',
			'accountNumber'   => 'nullable|string|max:255',
			'bankManageId'    => 'nullable|integer|exists:bank_manages,id',
			'entryDate'       => 'nullable|date',
			'currentBalance'  => 'nullable|numeric',
		]);

		$accountId = \DB::table('bank_accounts')->insertGetId([
			'account_name'   => $validated['account_name'],
			'account_number' => $validated['accountNumber'] ?? null,
			'bank_manage_id' => $validated['bankManageId'] ?? null,
			'entry_date'     => $validated['entryDate'] ?? null,
			'created_at'     => now(),
			'updated_at'     => now(),
		]);

		// Persist current balance into bank_balances (create row)
		$balance = (float) ($validated['currentBalance'] ?? 0);
		\DB::table('bank_balances')->updateOrInsert(
			['bank_account_id' => $accountId],
			[
				'balance' => $balance,
				'updated_at' => now(),
				// set created_at only if inserting (updateOrInsert will not overwrite existing created_at)
				'created_at' => now()
			]
		);

		return redirect()->route('bankAccountCreationView')->with('success', 'Bank account created.');
	}

    //bank account edit function
    public function bankAccountEdit($id)
    {
        $editBankAccount = bankAccount::find($id)::join('bank_manages', 'bank_accounts.bank_manage_id', '=', 'bank_manages.id')
                        ->select('bank_manages.bank_name','bank_manages.branch_name','bank_manages.routing_number','bank_accounts.*')
                        ->first();
        $balanceRow = \DB::table('bank_balances')->where('bank_account_id', $id)->first();
        $currentBalance = $balanceRow ? $balanceRow->balance : 0;
        return view('bank.bankAccountCreation', [
            'bankAccount' => $editBankAccount,
            'itemId' => $id,
            'opening_balance' => $currentBalance,
        ]);
    }

    // Update bank account and ensure bank_balances row exists/updated
	public function updateBankAccount(Request $request)
	{
		$validated = $request->validate([
			'id'              => 'required|integer|exists:bank_accounts,id',
			'account_name'    => 'required|string|max:255',
			'accountNumber'   => 'nullable|string|max:255',
			'bankManageId'    => 'nullable|integer|exists:bank_manages,id',
			'entryDate'       => 'nullable|date',
			'currentBalance'  => 'nullable|numeric',
		]);

		\DB::table('bank_accounts')->where('id', $validated['id'])->update([
			'account_name'   => $validated['account_name'],
			'account_number' => $validated['accountNumber'] ?? null,
			'bank_manage_id' => $validated['bankManageId'] ?? null,
			'entry_date'     => $validated['entryDate'] ?? null,
			'updated_at'     => now(),
		]);

		// create or update the bank_balances row with currentBalance
		$balance = (float) ($validated['currentBalance'] ?? 0);

		// If the row exists, only update balance and updated_at; if not, insert created_at too.
		$exists = \DB::table('bank_balances')->where('bank_account_id', $validated['id'])->exists();
		if ($exists) {
			\DB::table('bank_balances')->where('bank_account_id', $validated['id'])
				->update(['balance' => $balance, 'updated_at' => now()]);
		} else {
			\DB::table('bank_balances')->insert([
				'bank_account_id' => $validated['id'],
				'balance' => $balance,
				'created_at' => now(),
				'updated_at' => now(),
			]);
		}

		return redirect()->route('bankAccountCreationView')->with('success', 'Bank account updated.');
	}

    //bank account delete function
    public function deleteBankAccount($id)
    {
        $data = bankAccount::find($id);
        if ($data->delete()) :
            return back()->with('success', 'Success! Bank Account deleted successfully');
        else : 
            return back()->with('error', 'Opps! Bank Account deletion failed. Please try later');
        endif;
    }
}