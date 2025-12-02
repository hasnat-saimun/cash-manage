<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user'=>Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['name'=>'required|string','email'=>"required|email|unique:users,email,{$user->id}"]);
        $user->update($data);
        return back()->with('success','Profile updated');
    }

    public function changePassword(Request $request)
    {
        $request->validate(['current_password'=>'required','password'=>'required|confirmed|min:6']);
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password'=>'Current password incorrect']);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('success','Password changed');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar'=>'required|image|max:2048']);
        $user = Auth::user();
        $path = $request->file('avatar')->store('avatars','public');
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->avatar = $path;
        $user->save();
        return back()->with('success','Avatar updated');
    }
}
