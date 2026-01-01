@extends('include')
@section('backTitle') Mobile Banking @endsection
@section('bodyTitleFrist') Mobile Banking Daily Balance @endsection
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

      <form method="POST" action="{{ route('mobile.store') }}" class="row g-3">
        @csrf
        <div class="col-md-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Account</label>
          <select name="account_id" class="form-select" required>
            @foreach(($accounts ?? []) as $a)
              <option value="{{ $a->id }}" @if(old('account_id') ? old('account_id') == $a->id : $loop->first) selected @endif>{{ $a->provider ? $a->provider.' - ' : '' }}{{ $a->number }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Daily Balance</label>
          <input type="number" step="0.01" name="balance" class="form-control" value="{{ old('balance', '') }}" required>
        </div>
        <div class="col-md-3 d-grid">
          <label class="form-label">&nbsp;</label>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header"><h5 class="card-title mb-0">Recent Entries</h5></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Account</th>
              <th class="text-end">Balance</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recent as $r)
              <tr>
                <td>{{ $r->date }}</td>
                <td>{{ $r->provider ? $r->provider.' - ' : '' }}{{ $r->number }}</td>
                <td class="text-end">{{ number_format($r->balance,2) }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEntryModal{{ $r->id }}">Edit</button>
                  <form method="POST" action="{{ route('mobile.entries.delete', $r->id) }}" class="d-inline ms-1" data-confirm-delete data-confirm-message="Delete this entry?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>

                  <!-- Edit Entry Modal -->
                  <div class="modal fade" id="editEntryModal{{ $r->id }}" tabindex="-1" aria-labelledby="editEntryLabel{{ $r->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editEntryLabel{{ $r->id }}">Edit Entry</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('mobile.entries.update') }}">
                          @csrf
                          <div class="modal-body">
                            <input type="hidden" name="id" value="{{ $r->id }}">
                            <div class="mb-3">
                              <label class="form-label">Balance</label>
                              <input type="number" step="0.01" name="balance" value="{{ $r->balance }}" class="form-control" required>
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
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">No entries yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header"><h5 class="card-title mb-0">Manage Mobile Numbers</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('mobile.accounts.add') }}" class="row g-3">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Provider</label>
          <select name="provider_id" class="form-select">
            @foreach(($providers ?? []) as $p)
              <option value="{{ $p->id }}" @if(old('provider_id') ? old('provider_id') == $p->id : $loop->first) selected @endif>{{ $p->name }}</option>
            @endforeach
          </select>
          <div class="form-text">Manage providers from the <a href="{{ route('mobile.providers.index') }}">Providers</a> page.</div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Number</label>
          <input type="text" name="number" class="form-control" placeholder="e.g., 01XXXXXXXXX" required>
        </div>
        <div class="col-md-4 d-grid">
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-outline-primary">Add Number</button>
        </div>
      </form>

      <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Provider</th>
              <th>Number</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($accounts ?? []) as $a)
              <tr>
                <td>{{ $a->provider }}</td>
                <td>{{ $a->number }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAccountModal{{ $a->id }}">Edit</button>
                  <form method="POST" action="{{ route('mobile.accounts.delete', $a->id) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this number?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>

                  <!-- Edit Account Modal -->
                  <div class="modal fade" id="editAccountModal{{ $a->id }}" tabindex="-1" aria-labelledby="editAccountLabel{{ $a->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editAccountLabel{{ $a->id }}">Edit Mobile Number</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('mobile.accounts.update') }}">
                          @csrf
                          <div class="modal-body">
                            <input type="hidden" name="id" value="{{ $a->id }}">
                            <div class="mb-3">
                              <label class="form-label">Provider</label>
                              <select name="provider_id" class="form-select">
                                @foreach(($providers ?? []) as $p)
                                  <option value="{{ $p->id }}" @if($a->provider === $p->name) selected @endif>{{ $p->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Number</label>
                              <input type="text" name="number" value="{{ $a->number }}" class="form-control" placeholder="e.g., 01XXXXXXXXX" required>
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
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">No numbers added yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
