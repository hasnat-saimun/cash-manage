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
}
