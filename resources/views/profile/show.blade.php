@extends('include')
@section('bodyTitleFrist') Profile @endsection
@section('bodyContent')
<div class="row">
    <div class="col-md-6">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="mb-3"><label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required></div>
            <div class="mb-3"><label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required></div>
            <button class="btn btn-primary">Save profile</button>
        </form>

        <hr>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            <div class="mb-3"><label>Current password</label>
                <input type="password" name="current_password" class="form-control" required></div>
            <div class="mb-3"><label>New password</label>
                <input type="password" name="password" class="form-control" required></div>
            <div class="mb-3"><label>Confirm</label>
                <input type="password" name="password_confirmation" class="form-control" required></div>
            <button class="btn btn-secondary">Change password</button>
        </form>
    </div>

    <div class="col-md-6">
        <h5>Avatar</h5>
        @if($user->avatar)
            <img src="{{ asset('public/storage/'.$user->avatar) }}" alt="avatar" class="img-thumbnail mb-3" style="max-width:150px;">
        @endif
        <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="avatar" class="form-control" accept="image/*" required>
            </div>
            <button class="btn btn-info">Upload avatar</button>
        </form>
    </div>
</div>
@endsection
