<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If no users exist yet, send the visitor to the registration page to create the first admin.
        if (User::count() === 0) {
            return redirect()->route('auth.register');
        }

        return view('login.userLogin');
    }

    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        $credentials = $request->only('email','password');
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            // redirect to dashboard after login
            return redirect()->intended(route('dashboard'));
        }
        return back()->withErrors(['email'=>'Credentials do not match'])->onlyInput('email');
    }

    public function showRegister()
    {
        if (User::count() > 0) {
            return redirect()->route('login')->with('error','Registration closed');
        }

        // Use the renamed view
        return view('login.userRegister');
    }

    public function register(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('login')->with('error','Registration closed');
        }

        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|confirmed|min:6',
            'role'=>'nullable|string'
        ]);

        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'role'=> $data['role'] ?? 'superAdmin'
        ]);

        Auth::login($user);
        // go to dashboard after registration
        return redirect()->route('dashboard')->with('success','Admin account created');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
