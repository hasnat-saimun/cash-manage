@extends('include')
@section('backTitle')
Client Transaction
@endsection
@section('bodyTitleFrist')
   Transaction Creation
@endsection
@section('bodyTitleEnd')
   <a href="{{route('transactionList')}}"> Transaction List</a>
@endsection
@section('bodyContent')
<div class="row">
    <div class="col-12">
        @if(session()->has('success'))
        <div class="alert alert-success w-100 rounded-0">{{ session()->get('success') }}</div>
        @endif @if(session()->has('error'))
        <div class="alert alert-danger w-100 rounded-0">{{ session()->get('error') }}</div>
        @endif
    </div>
</div>
<div class="row">
@php
// normalize and initialize variables safely
$itemId = $itemId ?? null;
$clientName = $transactionSource = $type = $amount = $date = $description = '';

if (!empty($itemId)) {
    $items = \App\Models\transaction::find($itemId);
    if ($items) {
        $clientName = $items->transaction_client_name ?? '';
        $transactionSource = $items->transaction_source ?? '';
        $type = $items->type ?? '';
        $amount = $items->amount ?? '';
        $date = $items->date ?? '';
        $description = $items->description ?? '';
    } else {
        // itemId not found — reset to null to render empty form
        $itemId = null;
    }
}
@endphp
    <div class="col-md-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <form class="" method="POST" action="{{ route('saveTransaction') }}">
                    @csrf
                    <input type="hidden" name="itemId" value="{{ $itemId }}">
                    <div class="row">
                        @php
                            $clients = App\Models\clientCreation::all();
                        @endphp
                    <div class="col-6 mb-2">
                        <label for="clientId">Client Name</label>
                            <select class="form-select" id="clientId" name="clientId" required>
                                @if(!empty($clients) && $clients->count() > 0)
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" @if(!empty($clientName) ? (string)$client->id === (string)$clientName : $loop->first) selected @endif>
                                            {{ $client->client_name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" selected disabled>No Client Found</option>
                                @endif
                            </select>
                    </div>
                        @php
                            $sources = App\Models\source::all();
                        @endphp
                    <div class="col-6 mb-2">
                        <label for="sourceId">Source Type</label>
                            <select class="form-select" id="sourceId" name="sourceId" required>
                                @if(!empty($sources) && $sources->count() > 0)
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" @if(!empty($transactionSource) ? (string)$source->id === (string)$transactionSource : $loop->first) selected @endif>
                                            {{ $source->source_name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" selected disabled>No Source Found</option>
                                @endif
                            </select>
                    </div>
                    </div>
                    <!--end row-->
                    <div class="row">
                        <!--end col-->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="amount">Amount</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="amount"
                                    required=""
                                    placeholder="00.00"
                                    name="amount"
                                     value="{{ $amount }}"
                                />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="type">Transaction Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="Debit" {{ (empty($type) || $type === 'Debit') ? 'selected' : '' }}>Debit</option>
                                    <option value="Credit" {{ $type === 'Credit' ? 'selected' : '' }}>Credit</option>
                                </select>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="date">Date</label>
                                <input type="date" class="form-control" id="date" required="" placeholder="00.00" name="date"  value="{{ $date ?: now()->toDateString() }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label" id="description" for="description">Description</label>
                                <textarea
                                    class="form-control"
                                    rows="2"
                                    id="description"
                                    placeholder="Enter Description"
                                    name="description"
                                >{{ $description }}</textarea>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <!--end row-->
                    <div class="row">
                        <div class="col-sm-12 text-start">
                            <button type="submit" class="btn btn-primary px-4">@if(!empty($itemId))Update Data @else Save Data @endif</button>
                            <button type="submit" class="btn btn-danger px-4">Cancle</button>
                        </div>
                    </div>
                </form>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>

<!--end row-->

@endsection