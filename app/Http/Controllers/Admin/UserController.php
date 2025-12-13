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

    public function index() {
        $current = auth()->user();
        $query = User::query();
        if (!$current->isSuperAdmin()) {
            // Guard: only filter by created_by if column exists
            if (\Illuminate\Support\Facades\Schema::hasColumn('users','created_by')) {
                $query->where('created_by', $current->id);
            }
        }
        return view('admin.users.index', ['users'=>$query->paginate(20)]);
    }
    public function create() {
        // Non-super admins can only assign permissions they themselves have
        return view('admin.users.create');
    }
    public function store(Request $r) {
        $r->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|confirmed',
            'role'=>'required',
            'permissions' => 'array'
        ]);
        // Enforce permission bounds for non-super admins
        $current = auth()->user();
        $requestedPerms = collect($r->input('permissions', []))->filter()->values();
        if (!$current->isSuperAdmin()) {
            $currentPerms = collect($current->permissions ?? []);
            $diff = $requestedPerms->diff($currentPerms);
            if ($diff->isNotEmpty()) {
                return back()->with('error','You cannot grant permissions you do not have.')->withInput();
            }
        }
        User::create([
            'name'=>$r->name,
            'email'=>$r->email,
            'password'=>Hash::make($r->password),
            'role'=>$r->role,
            'permissions' => $requestedPerms->all(),
            'created_by' => $current->id,
            'need_new_business' => (bool)$r->boolean('need_new_business')
        ]);
        return redirect()->route('admin.users.index')->with('success','User created');
    }
    public function edit(User $user) {
        $current = auth()->user();
        if (!$current->isSuperAdmin() && $user->created_by !== $current->id) {
            abort(403);
        }
        return view('admin.users.edit',['user'=>$user]);
    }
    public function update(Request $r, User $user) {
        $r->validate([
            'name'=>'required',
            'email'=>"required|email|unique:users,email,{$user->id}",
            'role'=>'required',
            'permissions' => 'array'
        ]);
        $current = auth()->user();
        if (!$current->isSuperAdmin() && $user->created_by !== $current->id) {
            abort(403);
        }
        $requestedPerms = collect($r->input('permissions', []))->filter()->values();
        if (!$current->isSuperAdmin()) {
            $currentPerms = collect($current->permissions ?? []);
            $diff = $requestedPerms->diff($currentPerms);
            if ($diff->isNotEmpty()) {
                return back()->with('error','You cannot grant permissions you do not have.')->withInput();
            }
        }
        $user->update([
            'name' => $r->name,
            'email' => $r->email,
            'role' => $r->role,
            'need_new_business' => (bool)$r->boolean('need_new_business')
        ]);
        $user->permissions = $requestedPerms->all();
        $user->save();
        if ($r->filled('password')) { $user->password = Hash::make($r->password); $user->save(); }
        return redirect()->route('admin.users.index')->with('success','User updated');
    }
    public function destroy(User $user) {
        $current = auth()->user();
        if (!$current->isSuperAdmin() && $user->created_by !== $current->id) {
            abort(403);
        }
        $user->delete();
        return back()->with('success','User deleted');
    }

    // Permissions mapping UI
    public function permissions(User $user) {
        $current = auth()->user();
        if (!$current->isSuperAdmin() && $user->created_by !== $current->id) {
            abort(403);
        }
        $available = [
            'dashboard.view' => 'Dashboard',
            'admin.users' => 'Admin Users',
            'business.manage' => 'Business Management',
            'clients.manage' => 'Clients Manage',
            'source.manage' => 'Source Manage',
            'transactions.view' => 'Client Transactions View',
            'transactions.create' => 'Client Transactions Create',
            'reports.client' => 'Client Reports',
            'bank.manage' => 'Bank Manage',
            'bank.transactions.view' => 'Bank Transactions View',
            'bank.transactions.create' => 'Bank Transactions Create',
            'reports.bank' => 'Bank Reports',
            'reports.capital' => 'Capital Account',
            'mobile.manage' => 'Mobile Banking',
            'settings.manage' => 'Settings',
        ];
        return view('admin.users.permissions', ['user'=>$user,'available'=>$available]);
    }

    public function updatePermissions(Request $r, User $user) {
        $current = auth()->user();
        if (!$current->isSuperAdmin() && $user->created_by !== $current->id) {
            abort(403);
        }
        $requested = collect($r->input('permissions', []))->filter()->values();
        if (!$current->isSuperAdmin()) {
            $currentPerms = collect($current->permissions ?? []);
            $diff = $requested->diff($currentPerms);
            if ($diff->isNotEmpty()) {
                return back()->with('error','You cannot grant permissions you do not have.');
            }
        }
        $user->permissions = $requested->all();
        $user->save();
        return redirect()->route('admin.users.index')->with('success','Permissions updated');
    }
}
