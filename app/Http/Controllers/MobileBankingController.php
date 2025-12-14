<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileBankingController extends Controller
{
    public function index()
    {
        $bizId = request()->session()->get('business_id');
        $today = now()->toDateString();
        $providers = DB::table('mobile_providers')
            ->where('business_id', $bizId)
            ->orderBy('name')
            ->get();
        $accounts = DB::table('mobile_accounts')
            ->where('business_id', $bizId)
            ->orderBy('provider')
            ->orderBy('number')
            ->get();
        $entries = DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $today)
            ->select('mobile_entries.*','mobile_accounts.number','mobile_accounts.provider')
            ->get();
        // Recent should show only today's entries
        $recent = DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $today)
            ->orderByDesc('mobile_entries.updated_at')
            ->select('mobile_entries.*','mobile_accounts.number','mobile_accounts.provider')
            ->get();
        return view('mobile.index', compact('accounts','entries','recent','today','providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|integer|exists:mobile_accounts,id',
            'date' => 'required|date',
            'balance' => 'required|numeric',
        ]);
        $duplicate = DB::table('mobile_entries')
            ->where('mobile_account_id', $validated['account_id'])
            ->where('date', $validated['date'])
            ->exists();
        if ($duplicate) {
            return back()->withErrors(['date' => 'An entry for this account and date already exists. Use Edit to update it.'])->withInput();
        }
        DB::table('mobile_entries')->insert([
            'mobile_account_id' => $validated['account_id'],
            'date' => $validated['date'],
            'balance' => $validated['balance'],
            // rate_per_thousand and profit removed from workflow; leave null if columns exist
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('mobile.index')->with('success','Mobile banking entry saved.');
    }

    public function addAccount(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|integer|exists:mobile_providers,id',
            'number' => 'required|string|max:30',
        ]);
        $providerName = null;
        if (!empty($validated['provider_id'])) {
            $providerName = DB::table('mobile_providers')->where('id',$validated['provider_id'])->value('name');
        }
        $bizId = $request->session()->get('business_id');
        $exists = DB::table('mobile_accounts')
            ->where('business_id', $bizId)
            ->where('number', $validated['number'])
            ->where(function($q) use ($providerName) {
                if ($providerName === null) {
                    $q->whereNull('provider');
                } else {
                    $q->where('provider', $providerName);
                }
            })
            ->exists();
        if ($exists) {
            return back()->withErrors(['number' => 'This mobile number already exists for the selected provider.'])->withInput();
        }
        try {
            DB::table('mobile_accounts')->insert([
                'business_id' => $bizId,
                'number' => $validated['number'],
                'provider' => $providerName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return back()->with('success','Mobile banking number added.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                return back()->withErrors(['number' => 'This mobile number already exists for the selected provider.'])->withInput();
            }
            return back()->with('error','Failed to add mobile number.')->withInput();
        }
    }

    public function updateAccount(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:mobile_accounts,id',
            'provider_id' => 'nullable|integer|exists:mobile_providers,id',
            'number' => 'required|string|max:30',
        ]);
        $providerName = null;
        if (!empty($validated['provider_id'])) {
            $providerName = DB::table('mobile_providers')->where('id',$validated['provider_id'])->value('name');
        }
        DB::table('mobile_accounts')->where('id', $validated['id'])->update([
            'provider' => $providerName,
            'number' => $validated['number'],
            'updated_at' => now(),
        ]);
        return back()->with('success','Mobile banking number updated.');
    }

    public function deleteAccount($id)
    {
        DB::table('mobile_accounts')->where('id', $id)->delete();
        // cascade delete entries
        DB::table('mobile_entries')->where('mobile_account_id', $id)->delete();
        return back()->with('success','Mobile banking number deleted.');
    }

    public function deleteEntry($id)
    {
        DB::table('mobile_entries')->where('id', $id)->delete();
        return back()->with('success','Mobile banking entry deleted.');
    }

    public function updateEntry(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:mobile_entries,id',
            'balance' => 'required|numeric',
        ]);
        DB::table('mobile_entries')->where('id', $validated['id'])->update([
            'balance' => $validated['balance'],
            // rate_per_thousand and profit removed from workflow
            'updated_at' => now(),
        ]);
        return back()->with('success','Mobile banking entry updated.');
    }
}
