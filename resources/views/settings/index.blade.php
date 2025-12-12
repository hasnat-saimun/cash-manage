@extends('include')
@section('backTitle') Settings @endsection
@section('bodyTitleFrist') Website Settings @endsection
@section('bodyTitleEnd') @endsection
@section('bodyContent')
<div class="col-12">
  <div class="card">
    <div class="card-body">
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

      <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Site Name</label>
          <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $config['site_name'] ?? '') }}" placeholder="Your company name">
        </div>
        <div class="col-md-6">
          <label class="form-label">Site Title</label>
          <input type="text" name="site_title" class="form-control" value="{{ old('site_title', $config['site_title'] ?? '') }}" placeholder="Window title">
        </div>
        <div class="col-md-12">
          <label class="form-label">Tagline</label>
          <input type="text" name="site_tagline" class="form-control" value="{{ old('site_tagline', $config['site_tagline'] ?? '') }}" placeholder="Short description">
        </div>
        <div class="col-md-6">
          <label class="form-label">Mobile</label>
          <input type="text" name="contact_mobile" class="form-control" value="{{ old('contact_mobile', $config['contact_mobile'] ?? '') }}" placeholder="e.g., +1 555-1234">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $config['contact_email'] ?? '') }}" placeholder="hello@example.com">
        </div>
        <div class="col-md-6">
          <label class="form-label">Logo</label>
          <input type="file" name="logo" class="form-control" accept="image/*">
            @if(!empty($config['logo_path'] ?? ''))
              <div class="mt-2">
                <img src="{{ asset('public/storage/'.$config['logo_path']) }}" alt="Logo" style="max-height:60px;">
              </div>
            @endif
        </div>
          <div class="col-md-6">
            <label class="form-label">Sidebar Top Logo</label>
            <input type="file" name="sidebar_logo" class="form-control" accept="image/*">
            @php($sidebarLogo = \App\Models\Config::get('sidebar_logo_path'))
              @if(!empty($sidebarLogo))
                <div class="mt-2 p-3 rounded" style="background:#121826;">
                  <img src="{{ asset('public/storage/'.$sidebarLogo) }}" alt="Sidebar Logo" style="max-height:60px;">
                </div>
                <small class="text-muted d-block mt-1">Preview on sidebar-like background</small>
              @endif
          </div>
        <div class="col-12 d-grid">
          <button class="btn btn-primary" type="submit">Save Settings</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
