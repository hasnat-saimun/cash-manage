@extends('include')
@section('backTitle') User Permissions @endsection
@section('bodyTitleFrist') Map Permissions: {{ $user->name }} @endsection
@section('bodyTitleEnd') @endsection
@section('bodyContent')
<div class="col-12">
  <div class="card">
    <div class="card-body">
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}" class="row g-3">
        @csrf
        <div class="col-12">
          <div class="row">
            @php($userPerms = collect($user->permissions ?? []))
            @foreach($available as $key => $label)
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ str_replace('.', '_', $key) }}" {{ $userPerms->contains($key) ? 'checked' : '' }}>
                  <label class="form-check-label" for="perm_{{ str_replace('.', '_', $key) }}">{{ $label }}</label>
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <div class="col-12 d-grid">
          <button type="submit" class="btn btn-primary">Save Permissions</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
