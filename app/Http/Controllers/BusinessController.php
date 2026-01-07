<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $businesses = Auth::user()->businesses()->get();
        $currentId = $request->session()->get('business_id');
        return view('business.index', compact('businesses','currentId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $business = Business::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(6),
        ]);
        Auth::user()->businesses()->attach($business->id, ['role' => 'owner']);
        $request->session()->put('business_id', $business->id);
        // If the user was flagged to need a new business, clear the flag now
        $user = Auth::user();
        if ($user && (bool)($user->need_new_business ?? false)) {
            $user->need_new_business = false;
            $user->save();
        }
        return redirect()->route('business.index')->with('success','Business created');
    }

    public function switch(Request $request)
    {
        $request->validate(['business_id' => 'required|integer']);
        $bizId = (int) $request->input('business_id');
        $owns = Auth::user()->businesses()->where('business_user.business_id',$bizId)->exists();
        if (!$owns) {
            return back()->withErrors(['business_id'=>'Not authorized for this business']);
        }
        $business = Auth::user()->businesses()->where('business_user.business_id',$bizId)->first();
        $request->session()->put('business_id', $bizId);
        $request->session()->put('business_name', $business->name);
        return redirect()->route('dashboard')->with('success','Switched business');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_id' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        $business = Auth::user()->businesses()
            ->where('business_user.business_id', $data['business_id'])
            ->first();

        if (!$business) {
            return back()->withErrors(['business_id' => 'Not authorized for this business'])->withInput();
        }

        $business->name = $data['name'];
        $business->slug = Str::slug($data['name']).'-'.Str::random(6);
        $business->save();

        return back()->with('success', 'Business updated');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'business_id' => 'required|integer',
            'delete_type' => 'required|in:full,business_only',
        ]);

        $business = Auth::user()->businesses()
            ->where('business_user.business_id', $data['business_id'])
            ->first();

        if (!$business) {
            return back()->withErrors(['business_id' => 'Not authorized for this business']);
        }

        // Check if user is owner
        $pivot = Auth::user()->businesses()
            ->where('business_user.business_id', $data['business_id'])
            ->first()
            ->pivot;
        
        if ($pivot->role !== 'owner') {
            return back()->withErrors(['error' => 'Only owner can delete the business']);
        }

        if ($data['delete_type'] === 'full') {
            // Delete all related data
            // The cascadeOnDelete foreign keys will handle most of the cleanup
            // But we need to manually delete records in tables without cascade
            \DB::table('daily_cash_records')->where('business_id', $data['business_id'])->delete();
            \DB::table('mobile_balances')->where('business_id', $data['business_id'])->delete();
            \DB::table('mobile_providers')->where('business_id', $data['business_id'])->delete();
            \DB::table('mobile_accounts')->where('business_id', $data['business_id'])->delete();
            
            // Delete the business (cascade will handle business_user, client_creations, transactions, etc.)
            $business->delete();
        } else {
            // Only delete business record but keep other data
            // First, we need to remove the business_id association from related tables
            \DB::table('client_creations')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('transactions')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('sources')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('bank_manages')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('bank_accounts')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('bank_transactions')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('client_balances')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('bank_balances')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('daily_cash_records')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('mobile_balances')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('mobile_providers')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('mobile_accounts')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            \DB::table('configs')->where('business_id', $data['business_id'])->update(['business_id' => null]);
            
            // Delete the business and business_user pivot records
            $business->delete();
        }

        // Clear session if the deleted business was active
        if ($request->session()->get('business_id') == $data['business_id']) {
            $request->session()->forget('business_id');
            $request->session()->forget('business_name');
        }

        return redirect()->route('business.index')->with('success', 'Business deleted successfully');
    }
}
