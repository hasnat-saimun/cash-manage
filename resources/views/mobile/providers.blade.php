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
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProviderModal{{ $p->id }}">Edit</button>
                  <form method="POST" action="{{ route('mobile.providers.delete', $p->id) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this provider?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>

              <!-- Edit Provider Modal -->
              <div class="modal fade" id="editProviderModal{{ $p->id }}" tabindex="-1" aria-labelledby="editProviderLabel{{ $p->id }}" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editProviderLabel{{ $p->id }}">Edit Provider</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('mobile.providers.update') }}">
                      @csrf
                      <input type="hidden" name="id" value="{{ $p->id }}">
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Provider Name</label>
                          <input type="text" name="name" class="form-control" value="{{ $p->name }}" placeholder="e.g., bKash, Nagad" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
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
