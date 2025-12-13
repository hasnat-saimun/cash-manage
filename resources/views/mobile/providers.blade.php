@extends('include')
@section('backTitle') Mobile Banking Providers @endsection
@section('bodyTitleFrist') Manage Mobile Providers @endsection
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

      <form method="POST" action="{{ route('mobile.providers.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Provider Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g., bKash, Nagad" required>
        </div>
        <div class="col-md-3 d-grid">
          <label class="form-label">&nbsp;</label>
          <button class="btn btn-primary" type="submit">Add Provider</button>
        </div>
      </form>

      <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($providers as $p)
              <tr>
                <td>{{ $p->name }}</td>
                <td class="text-end">
                  <form method="POST" action="{{ route('mobile.providers.delete', $p->id) }}" onsubmit="return confirm('Delete this provider?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted">No providers yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
