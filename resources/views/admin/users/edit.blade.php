@extends('include')
@section('bodyTitleFrist') Edit User @endsection
@section('bodyTitleEnd') Update user details @endsection
@section('bodyContent')
<div class="col-12 col-lg-8">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update',$user) }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name',$user->name) }}" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email',$user->email) }}" required></div>
                <div class="mb-3"><label class="form-label">Role</label>
                    <select class="form-select" name="role" required>
                        <option value="cashier" @selected(old('role',$user->role)==='cashier')>Cashier</option>
                        <option value="general admin" @selected(old('role',$user->role)==='general admin')>General Admin</option>
                        <option value="superAdmin" @selected(old('role',$user->role)==='superAdmin')>Super Admin</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">New Password (optional)</label><input type="password" class="form-control" name="password"></div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="needNewBiz" name="need_new_business" value="1" @checked(old('need_new_business', (bool)($user->need_new_business ?? false)))>
                    <label class="form-check-label" for="needNewBiz">Need New Business</label>
                </div>
                @include('admin.users.form-permissions')
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-link">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
