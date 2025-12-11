@extends('include')
@section('backTitle')
Bank Transactions
@endsection
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
                <a href="{{ route('bankTransactionCreation') }}" class="btn btn-primary mb-2">Add Transaction</a>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
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
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $txn->account_name ?? $txn->account_number ?? 'Account '.$txn->bank_account_id }}</td>
                                    <td>{{ ucfirst($txn->{$typeCol} ?? '') }}</td>
                                    <td>{{ number_format($txn->{$amtCol} ?? 0,2) }}</td>
                                    <td>{{ $txn->{$dateCol} ?? '' }}</td>
                                    <td>{{ $txn->description ?? '' }}</td>
                                    <td>
                                        <a href="{{ route('bankTransactionEdit', ['id'=>$txn->id]) }}" class="btn btn-sm btn-info">Edit</a>
                                        <a href="{{ route('deleteBankTransaction', ['id'=>$txn->id]) }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this transaction?')">Delete</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection