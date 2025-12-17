@extends('include')
@section('backTitle')
Bank Transaction
@endsection
@section('bodyTitleFrist')
   Bank Transaction Creation
@endsection
@section('bodyTitleEnd')
   <a href="{{route('bankTransactionList')}}"> Bank Transaction List</a>
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
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('saveBankTransaction') }}">
                    @csrf
                    <input type="hidden" name="itemId" value="{{ $itemId ?? '' }}">
                    <div class="mb-3">
                        <label for="bank_account_id" class="form-label">Bank Account</label>
                        <select class="form-select" id="bank_account_id" name="bank_account_id" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @if(isset($editData) ? ($editData->bank_account_id == $acc->id) : $loop->first) selected @endif>
                                    {{ $acc->account_name ?? $acc->account_number ?? 'Account '.$acc->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="Debit" {{ (isset($editData) ? (($editData->type ?? $editData->transaction_type) === 'Debit') : true) ? 'selected' : '' }}>Debit</option>
                            <option value="Credit" {{ (isset($editData) && (($editData->type ?? $editData->transaction_type) === 'Credit')) ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required value="{{ $editData->amount ?? $editData->txn_amount ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required value="{{ $editData->date ?? $editData->transaction_date ?? $editData->txn_date ?? now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2">{{ $editData->description ?? '' }}</textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">@if(isset($itemId)) Update @else Save @endif</button>
                        <a href="{{ route('bankTransactionList') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection