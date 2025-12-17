@extends('include')
@section('bodyTitleFrist') Create User @endsection
@section('bodyTitleEnd') Add new user @endsection
@section('bodyContent')
<div class="col-12 col-lg-8">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
                <div class="mb-3"><label class="form-label">Role</label>
                    <select class="form-select" name="role" required>
                        <option value="cashier" @selected(old('role','cashier')==='cashier')>Cashier</option>
                        <option value="general admin" @selected(old('role')==='general admin')>General Admin</option>
                        <option value="superAdmin" @selected(old('role')==='superAdmin')>Super Admin</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
                <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="password_confirmation" required></div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="needNewBiz" name="need_new_business" value="1" @checked(old('need_new_business'))>
                    <label class="form-check-label" for="needNewBiz">Need New Business</label>
                </div>
                @php($user = (object)['permissions'=>old('permissions',[])])
                @include('admin.users.form-permissions')
                <button class="btn btn-primary">Create</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
