@extends('include')
@section('backTitle')
Bank Transactions
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('bank-select-all');
    const checkboxes = Array.from(document.querySelectorAll('.bank-txn-checkbox'));
    const bulkBtn = document.getElementById('bank-bulk-delete-btn');
    const form = document.getElementById('bank-bulk-form');

    function refreshBtn() {
        const any = checkboxes.some(cb => cb.checked);
        if (bulkBtn) bulkBtn.disabled = !any;
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
            refreshBtn();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', () => {
        if (!cb.checked && selectAll && selectAll.checked) selectAll.checked = false;
        refreshBtn();
    }));

    if (form) {
        form.addEventListener('submit', function (e) {
            if (bulkBtn && bulkBtn.disabled) { e.preventDefault(); return; }
            if (!confirm('Delete selected bank transactions? Balances will be adjusted.')) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
@section('bodyTitleFrist')
   Bank Transaction List
@endsection
@section('bodyTitleEnd')
   <a href="{{route('bankTransactionCreation')}}"> Add Bank Transaction</a>
@endsection
@section('bodyContent')
<div class="row">
    <div class="col-12">
        @if(session()->has('success'))
        <div class="alert alert-success w-100 rounded-0">{{ session()->get('success') }}</div>
        @endif
        @if(session()->has('error'))
        <div class="alert alert-danger w-100 rounded-0">{{ session()->get('error') }}</div>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Bank Transactions</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bankTransactions.bulkDelete') }}" id="bank-bulk-form" data-confirm-delete data-confirm-message="Delete the selected bank transactions?">
                    @csrf
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <a href="{{ route('bankTransactionCreation') }}" class="btn btn-primary">Add Transaction</a>
                        <button type="submit" class="btn btn-danger" id="bank-bulk-delete-btn" disabled>Delete Selected</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="bank-select-all"></th>
                                    <th>#</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($txns as $i => $txn)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{ $txn->id }}" class="bank-txn-checkbox" form="bank-bulk-form"></td>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $txn->account_name ?? $txn->account_number ?? 'Account '.$txn->bank_account_id }}</td>
                                        <td>{{ ucfirst($txn->{$typeCol} ?? '') }}</td>
                                        <td>{{ number_format($txn->{$amtCol} ?? 0,2) }}</td>
                                        <td>{{ $txn->{$dateCol} ?? '' }}</td>
                                        <td>{{ $txn->description ?? '' }}</td>
                                        <td>
                                            <a href="{{ route('bankTransactionEdit', ['id'=>$txn->id]) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="{{ route('deleteBankTransaction', ['id'=>$txn->id]) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this transaction?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No transactions found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection