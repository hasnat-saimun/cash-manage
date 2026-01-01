@extends('include')

@section('bodyTitleFrist')
Business Management
@endsection

@section('bodyTitleEnd')
Manage businesses
@endsection

@section('bodyContent')
<div class="container py-3">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-4">
    <div class="card-header">Current Business</div>
    <div class="card-body">
      <p class="mb-0">Business ID in session: <strong>{{ $currentId ?? 'none' }}</strong></p>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">Your Businesses</div>
    <div class="card-body">
      <form class="row g-2" method="POST" action="{{ route('business.switch') }}">
        @csrf
        <div class="col-12 col-md-6">
          <select name="business_id" class="form-select">
            @foreach($businesses as $biz)
              <option value="{{ $biz->id }}" {{ empty($currentId) ? ($loop->first ? 'selected' : '') : (($currentId==$biz->id)?'selected':'') }}>{{ $biz->name }} (ID: {{ $biz->id }})</option>
            @endforeach
          </select>
        </div>
        <div class="col">
          <button type="submit" class="btn btn-primary">Switch</button>
        </div>
      </form>

      <form class="row g-2 mt-3" method="POST" action="{{ route('business.update') }}">
        @csrf
        @method('PATCH')
        <div class="col-12 col-md-4">
          <select name="business_id" class="form-select">
            @foreach($businesses as $biz)
              <option value="{{ $biz->id }}">{{ $biz->name }} (ID: {{ $biz->id }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-5">
          <input type="text" name="name" class="form-control" placeholder="New business name" required />
        </div>
        <div class="col">
          <button type="submit" class="btn btn-warning">Rename</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Create New Business</div>
    <div class="card-body">
      <form class="row g-2" method="POST" action="{{ route('business.store') }}">
        @csrf
        <div class="col-12 col-md-6">
          <input type="text" name="name" class="form-control" placeholder="Business name" required />
        </div>
        <div class="col">
          <button type="submit" class="btn btn-success">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
