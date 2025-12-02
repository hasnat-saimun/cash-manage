<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Basic check: only superAdmin/general admin allowed, customize as needed
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['superAdmin','general admin'])) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index() { return view('admin.users.index', ['users'=>User::paginate(20)]); }
    public function create() { return view('admin.users.create'); }
    public function store(Request $r) {
        $r->validate(['name'=>'required','email'=>'required|email|unique:users','password'=>'required|confirmed','role'=>'required']);
        User::create(['name'=>$r->name,'email'=>$r->email,'password'=>Hash::make($r->password),'role'=>$r->role]);
        return redirect()->route('users.index')->with('success','User created');
    }
    public function edit(User $user) { return view('admin.users.edit',['user'=>$user]); }
    public function update(Request $r, User $user) {
        $r->validate(['name'=>'required','email'=>"required|email|unique:users,email,{$user->id}",'role'=>'required']);
        $user->update($r->only('name','email','role'));
        if ($r->filled('password')) { $user->password = Hash::make($r->password); $user->save(); }
        return redirect()->route('users.index')->with('success','User updated');
    }
    public function destroy(User $user) { $user->delete(); return back()->with('success','User deleted'); }
}
