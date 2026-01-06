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

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:mobile_providers,id',
            'name' => 'required|string|max:50',
        ]);
        $bizId = $request->session()->get('business_id');
        $exists = DB::table('mobile_providers')
            ->where('business_id', $bizId)
            ->where('name', $validated['name'])
            ->where('id', '!=', $validated['id'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'Provider name already exists.'])->withInput();
        }
        DB::table('mobile_providers')->where('id', $validated['id'])->update([
            'name' => $validated['name'],
            'updated_at' => now(),
        ]);
        return back()->with('success','Provider updated.');
    }

    public function delete($id)
    {
        DB::table('mobile_providers')->where('id',$id)->delete();
        return back()->with('success','Provider deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);
        
        try {
            DB::table('mobile_providers')->whereIn('id', $data['ids'])->delete();
            return redirect()->route('mobile.providers.index')->with('success','Selected mobile providers deleted.');
        } catch (\Throwable $e) {
            return redirect()->route('mobile.providers.index')->with('error','Failed to delete selected mobile providers.');
        }
    }
}
