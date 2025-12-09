<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\clientCreation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class clintController extends Controller
{
	// show client list
	public function clientCreation()
	{
		$allClient = clientCreation::orderBy('client_name')->get();
		return view('client.clientCreation', compact('allClient'));
	}

	// open edit view (reuses same blade; passes itemId)
	public function clientEdit($id)
	{
		$allClient = clientCreation::orderBy('client_name')->get();
		$itemId = $id;
		return view('client.clientCreation', compact('allClient','itemId'));
	}

	// save new client
	public function saveClient(Request $request)
	{
		$validated = $request->validate([
			'fullName' => 'required|string|max:255',
			'clientOpBalance' => 'required|numeric',
			'registerDate' => 'required|date',
			'email' => 'nullable|email|max:255',
			'mobileNo' => 'nullable|string|max:50',
		]);

		$client = new clientCreation();
		$client->client_name = $validated['fullName'];
		$client->client_email = $validated['email'] ?? null;
		$client->client_phone = $validated['mobileNo'] ?? null;
		$client->client_regDate = $validated['registerDate'];
		$client->save();

		// Persist opening balance into client_balances only (do NOT save to client_creations)
		DB::table('client_balances')->updateOrInsert(
			['client_id' => $client->id],
			[
				'balance' => (float)$validated['clientOpBalance'],
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]
		);

		return redirect()->route('clientCreation')->with('success','Client saved.');
	}

	// update existing client
	public function updateClient(Request $request)
	{
		$validated = $request->validate([
			'itemId' => 'required|integer|exists:client_creations,id',
			'fullName' => 'required|string|max:255',
			'clientOpBalance' => 'required|numeric',
			'registerDate' => 'required|date',
			'email' => 'nullable|email|max:255',
			'mobileNo' => 'nullable|string|max:50',
		]);

		$client = clientCreation::find($validated['itemId']);
		if (!$client) {
			return redirect()->route('clientCreation')->with('error','Client not found.');
		}

		$client->client_name = $validated['fullName'];
		$client->client_email = $validated['email'] ?? null;
		$client->client_phone = $validated['mobileNo'] ?? null;
		$client->client_regDate = $validated['registerDate'];
		// do NOT overwrite client_opBalance column: we removed storing balance on client_creations
		$client->save();

		// Update client_balances with new balance (replace opening/current balance)
		DB::table('client_balances')->updateOrInsert(
			['client_id' => $client->id],
			[
				'balance' => (float)$validated['clientOpBalance'],
				'updated_at' => Carbon::now(),
				'created_at' => DB::raw('COALESCE(created_at, "' . Carbon::now()->toDateTimeString() . '")')
			]
		);

		return redirect()->route('clientCreation')->with('success','Client updated.');
	}

	// delete client and its balance row
	public function deleteClient($id)
	{
		$client = clientCreation::find($id);
		if ($client) {
			$client->delete();
			DB::table('client_balances')->where('client_id', $id)->delete();
		}
		return redirect()->route('clientCreation')->with('success','Client deleted.');
	}

}
