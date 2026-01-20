@extends('include')
@section('backTitle') Mobile Banking @endsection
@section('bodyTitleFrist') Mobile Banking Daily Balance @endsection
@section('bodyTitleEnd') @endsection
@section('bodyContent')
<div class="col-12">
  <!-- Quick Navigation -->
  <div class="mb-3">
    <a href="{{ route('mobile.cashCalculator') }}" class="btn btn-outline-primary">
      <i class="bx bx-calculator me-1"></i> Cash Calculator
    </a>
    <a href="{{ route('mobile.providers.index') }}" class="btn btn-outline-secondary">
      <i class="bx bx-cog me-1"></i> Manage Providers
    </a>
  </div>

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
      <form id="entries-bulk-form" method="POST" action="{{ route('mobile.entries.bulkDelete') }}" data-confirm-delete data-confirm-message="Delete the selected entries? This cannot be undone.">
        @csrf
      </form>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;"><input type="checkbox" id="entries-select-all" class="form-check-input"></th>
              <th>SL</th>
              <th>Date</th>
              <th>Account</th>
              <th class="text-end">Balance</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recent as $r)
              <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $r->id }}" class="form-check-input entries-checkbox" form="entries-bulk-form"></td>
                <td>{{ $loop->iteration }}</td>
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
      <div class="mt-3">
        <button type="submit" form="entries-bulk-form" id="entries-bulk-delete-btn" class="btn btn-danger" disabled>
          <i class="fas fa-trash me-1"></i> Delete Selected
        </button>
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
        <form id="accounts-bulk-form" method="POST" action="{{ route('mobile.accounts.bulkDelete') }}" data-confirm-delete data-confirm-message="Delete the selected mobile numbers? This cannot be undone.">
          @csrf
        </form>
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;"><input type="checkbox" id="accounts-select-all" class="form-check-input"></th>
              <th>SL</th>
              <th>Provider</th>
              <th>Number</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($accounts ?? []) as $a)
              <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $a->id }}" class="form-check-input accounts-checkbox" form="accounts-bulk-form"></td>
                <td>{{ $loop->iteration }}</td>
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
            <tr><td colspan="5" class="text-center text-muted">No numbers added yet.</td></tr>
          @endforelse
          </tbody>
        </table>
        <div class="mt-3">
          <button type="submit" form="accounts-bulk-form" id="accounts-bulk-delete-btn" class="btn btn-danger" disabled>
            <i class="fas fa-trash me-1"></i> Delete Selected
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Entries bulk delete
    const entriesSelectAll = document.getElementById('entries-select-all');
    const entriesCheckboxes = document.querySelectorAll('.entries-checkbox');
    const entriesDeleteBtn = document.getElementById('entries-bulk-delete-btn');
    const entriesForm = document.getElementById('entries-bulk-form');

    if (entriesSelectAll && entriesCheckboxes.length > 0) {
        entriesSelectAll.addEventListener('change', function() {
            entriesCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateEntriesButtonState();
        });

        entriesCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateEntriesButtonState();
                if (!this.checked) {
                    entriesSelectAll.checked = false;
                }
            });
        });

        function updateEntriesButtonState() {
            const anyChecked = Array.from(entriesCheckboxes).some(cb => cb.checked);
            entriesDeleteBtn.disabled = !anyChecked;
        }

        entriesForm.addEventListener('submit', function(e) {
          const anyChecked = Array.from(entriesCheckboxes).some(cb => cb.checked);
          if (!anyChecked) {
            e.preventDefault();
          }
        });
    }

    // Accounts bulk delete
    const accountsSelectAll = document.getElementById('accounts-select-all');
    const accountsCheckboxes = document.querySelectorAll('.accounts-checkbox');
    const accountsDeleteBtn = document.getElementById('accounts-bulk-delete-btn');
    const accountsForm = document.getElementById('accounts-bulk-form');

    if (accountsSelectAll && accountsCheckboxes.length > 0) {
        accountsSelectAll.addEventListener('change', function() {
            accountsCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateAccountsButtonState();
        });

        accountsCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateAccountsButtonState();
                if (!this.checked) {
                    accountsSelectAll.checked = false;
                }
            });
        });

        function updateAccountsButtonState() {
            const anyChecked = Array.from(accountsCheckboxes).some(cb => cb.checked);
            accountsDeleteBtn.disabled = !anyChecked;
        }

        accountsForm.addEventListener('submit', function(e) {
          const anyChecked = Array.from(accountsCheckboxes).some(cb => cb.checked);
          if (!anyChecked) {
            e.preventDefault();
          }
        });
    }
});
</script>
@endpush

@endsection
