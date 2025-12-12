<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Config;

class SettingsController extends Controller
{
    public function index()
    {
        $config = [
            'site_name' => Config::get('site_name',''),
            'site_title' => Config::get('site_title',''),
            'site_tagline' => Config::get('site_tagline',''),
            'contact_mobile' => Config::get('contact_mobile',''),
            'contact_email' => Config::get('contact_email',''),
            'logo_path' => Config::get('logo_path',''),
        ];
        return view('settings.index', compact('config'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_title' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'contact_mobile' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
            'sidebar_logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
            Config::set('logo_path', $path);
        }
        if ($request->hasFile('sidebar_logo')) {
            $sidebarPath = $request->file('sidebar_logo')->store('logos', 'public');
            Config::set('sidebar_logo_path', $sidebarPath);
        }
        // Persist as key-value per business
        foreach (['site_name','site_title','site_tagline','contact_mobile','contact_email'] as $key) {
            if (array_key_exists($key, $validated)) {
                Config::set($key, $validated[$key]);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
