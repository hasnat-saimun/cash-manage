<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;

class ConfigController extends Controller
{
    public function edit()
    {
        return view('config.edit', [
            'site_name' => Config::get('site_name','Cash Manage'),
            'address' => Config::get('address',''),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['site_name'=>'nullable|string','address'=>'nullable|string']);
        Config::set('site_name', $data['site_name'] ?? '');
        Config::set('address', $data['address'] ?? '');
        return back()->with('success','Configuration saved');
    }
}
