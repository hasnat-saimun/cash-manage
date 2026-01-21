<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TransactionDetail;

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
            ->orderBy('mobile_entries.date','asc')
            ->orderBy('mobile_entries.id','asc')
            ->get();
        // Recent should show only today's entries
        $recent = DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $today)
            ->orderBy('mobile_entries.date','asc')
            ->orderBy('mobile_entries.id','asc')
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

    public function cashCalculator()
    {
        $bizId = request()->session()->get('business_id');
        $today = now()->toDateString();
        
        // Get all mobile accounts
        $accounts = DB::table('mobile_accounts')
            ->where('business_id', $bizId)
            ->orderBy('provider')
            ->orderBy('number')
            ->get();
        
        // Get today's mobile entries
        $todayEntries = DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $today)
            ->select('mobile_entries.*','mobile_accounts.number','mobile_accounts.provider')
            ->get();
        
        // Get yesterday's total balance for comparison
        $yesterday = now()->subDay()->toDateString();
        $yesterdayBalance = DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $yesterday)
            ->sum('mobile_entries.balance') ?? 0;
        
        // Calculate today's total balance
        $todayTotalBalance = $todayEntries->sum('balance') ?? 0;
        
        // Calculate cash difference (debit/credit)
        $cashDifference = $todayTotalBalance - $yesterdayBalance;
        
        // Get today's debit/credit records with transaction details
        $cashRecords = DB::table('daily_cash_records')
            ->leftJoin('transaction_details', 'daily_cash_records.transaction_detail_id', '=', 'transaction_details.id')
            ->where('daily_cash_records.business_id', $bizId)
            ->where('daily_cash_records.date', $today)
            ->select('daily_cash_records.*', 'transaction_details.name as detail_name')
            ->orderBy('daily_cash_records.created_at', 'asc')
            ->get();
        
        // Calculate totals
        $totalDebit = $cashRecords->where('type', 'debit')->sum('amount') ?? 0;
        $totalCredit = $cashRecords->where('type', 'credit')->sum('amount') ?? 0;
        
        // Get transaction details for dropdown
        $transactionDetails = TransactionDetail::activeForBusiness($bizId);
        
        return view('mobile.cashCalculator', compact('accounts', 'todayEntries', 'todayTotalBalance', 'yesterdayBalance', 'cashDifference', 'today', 'cashRecords', 'totalDebit', 'totalCredit', 'transactionDetails'));
    }

    public function addCashRecord(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'transaction_detail_id' => 'nullable|integer|exists:transaction_details,id',
            'new_detail' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
        ]);
        
        $bizId = $request->session()->get('business_id');
        $detailId = $validated['transaction_detail_id'];
        
        // If new detail provided, create it
        if (!empty($validated['new_detail']) && empty($detailId)) {
            $transactionDetail = TransactionDetail::create([
                'business_id' => $bizId,
                'name' => $validated['new_detail'],
                'type' => null, // null means it can be used for both debit and credit
                'is_active' => true,
            ]);
            $detailId = $transactionDetail->id;
        }
        
        DB::table('daily_cash_records')->insert([
            'business_id' => $bizId,
            'date' => $validated['date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'transaction_detail_id' => $detailId,
            'reference_no' => $validated['reference_no'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return back()->with('success', ucfirst($validated['type']) . ' record added successfully.');
    }

    public function updateCashRecord(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:daily_cash_records,id',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'transaction_detail_id' => 'nullable|integer|exists:transaction_details,id',
            'new_detail' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
        ]);
        
        $bizId = request()->session()->get('business_id');
        $detailId = $validated['transaction_detail_id'];
        
        // If new detail provided, create it
        if (!empty($validated['new_detail']) && empty($detailId)) {
            $transactionDetail = TransactionDetail::create([
                'business_id' => $bizId,
                'name' => $validated['new_detail'],
                'type' => null,
                'is_active' => true,
            ]);
            $detailId = $transactionDetail->id;
        }
        
        DB::table('daily_cash_records')->where('id', $validated['id'])->update([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'transaction_detail_id' => $detailId,
            'reference_no' => $validated['reference_no'],
            'updated_at' => now(),
        ]);
        
        return back()->with('success', 'Record updated successfully.');
    }

    public function deleteCashRecord($id)
    {
        DB::table('daily_cash_records')->where('id', $id)->delete();
        return back()->with('success', 'Record deleted successfully.');
    }

    public function createTransactionDetail(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:debit,credit',
        ]);
        
        $bizId = $request->session()->get('business_id');
        
        // Check if detail already exists
        $exists = TransactionDetail::where('business_id', $bizId)
            ->where('name', $validated['name'])
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction detail already exists.'
            ], 422);
        }
        
        $detail = TransactionDetail::create([
            'business_id' => $bizId,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'is_active' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Transaction detail created successfully.',
            'id' => $detail->id,
            'name' => $detail->name,
        ]);
    }

    public function bulkDeleteAccounts(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);
        
        try {
            DB::table('mobile_accounts')->whereIn('id', $data['ids'])->delete();
            return redirect()->route('mobile.index')->with('success','Selected mobile accounts deleted.');
        } catch (\Throwable $e) {
            return redirect()->route('mobile.index')->with('error','Failed to delete selected mobile accounts.');
        }
    }

    public function bulkDeleteEntries(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);
        
        try {
            DB::table('mobile_entries')->whereIn('id', $data['ids'])->delete();
            return redirect()->route('mobile.index')->with('success','Selected mobile entries deleted.');
        } catch (\Throwable $e) {
            return redirect()->route('mobile.index')->with('error','Failed to delete selected mobile entries.');
        }
    }
}
