<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileProviderController extends Controller
{
    public function index()
    {
        $bizId = request()->session()->get('business_id');
        $providers = DB::table('mobile_providers')
            ->where('business_id', $bizId)
            ->orderBy('name')
            ->get();
        return view('mobile.providers', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);
        $bizId = $request->session()->get('business_id');
        $exists = DB::table('mobile_providers')
            ->where('business_id', $bizId)
            ->where('name', $validated['name'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'Provider already exists.'])->withInput();
        }
        DB::table('mobile_providers')->insert([
            'business_id' => $bizId,
            'name' => $validated['name'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success','Provider added.');
    }

    public function delete($id)
    {
        DB::table('mobile_providers')->where('id',$id)->delete();
        return back()->with('success','Provider deleted.');
    }
}
