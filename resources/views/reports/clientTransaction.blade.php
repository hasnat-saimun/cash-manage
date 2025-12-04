@extends('include')
@section('backTitle') Client Report @endsection
@section('bodyTitleFrist') Client Wise Transaction Report @endsection
@section('bodyTitleEnd')
    <a href="{{ route('transactionList') }}"> Transaction List</a>
@endsection
@section('bodyContent')
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.clientTransaction') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select" required>
                        <option value="">-- Select client --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @if(isset($clientId) && $clientId == $c->id) selected @endif>
                                {{ $c->client_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" id="report_type" class="form-select">
                        <option value="daily" @if(($reportType ?? '') === 'daily') selected @endif>Daily</option>
                        <option value="custom" @if(($reportType ?? '') === 'custom') selected @endif>Custom Date Range</option>
                    </select>
                </div>

                <div class="col-md-2 daily-field">
                    <label class="form-label">Date</label>
                    <input type="date" id="daily_date" name="date" class="form-control" value="{{ $date ?? \Carbon\Carbon::today()->toDateString() }}">
                </div>

                <!-- custom fields: To then From (per request) -->
                <div class="col-md-2 custom-field" style="display: none;">
                    <label class="form-label">To</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $to ?? '' }}">
                </div>
                <div class="col-md-2 custom-field" style="display: none;">
                    <label class="form-label">From</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $from ?? '' }}">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Generate</button>
                </div>

                @if(isset($rangeLabel) && $rangeLabel)
                    <div class="col-12 mt-2">
                        <small class="text-muted">Showing results for: <strong>{{ $rangeLabel }}</strong></small>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Report table -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Client Transactions (Debit / Credit)</h5>
        </div>
        <div class="card-body">
            @if(empty($clientId))
                <div class="alert alert-info">Please select a client and date range then click Generate.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:130px">Date</th>
                                <th>Description</th>
                                <th class="text-end" style="width:150px">Debit</th>
                                <th class="text-end" style="width:150px">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                <tr>
                                    <td>{{ $r['date'] }}</td>
                                    <td>{{ $r['description'] }}</td>
                                    <td class="text-end">{{ $r['debit'] ? number_format($r['debit'],2) : '-' }}</td>
                                    <td class="text-end">{{ $r['credit'] ? number_format($r['credit'],2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No transactions found for the selected client and date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($totalDebit,2) }}</th>
                                <th class="text-end">{{ number_format($totalCredit,2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-end">Grand Total (Credit - Debit)</th>
                                <th colspan="2" class="text-end">
                                    <span class="fw-semibold">{{ number_format($grandTotal,2) }}</span>
                                    @if($grandTotal >= 0)
                                        <span class="badge bg-success ms-2">Positive</span>
                                    @else
                                        <span class="badge bg-danger ms-2">Negative</span>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    function formatToday() {
        var d = new Date();
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();
        var year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    }

    function toggleFields(){
        var type = document.getElementById('report_type').value;
        var daily = document.querySelectorAll('.daily-field');
        var custom = document.querySelectorAll('.custom-field');

        if(type === 'custom'){
            daily.forEach(e=>e.style.display='none');
            custom.forEach(e=>e.style.display='block');
            // preserve existing values; focus "to" by default
            var to = document.getElementById('to_date');
            if(to) to.focus();
        } else {
            daily.forEach(e=>e.style.display='block');
            custom.forEach(e=>e.style.display='none');
            // set today's date and focus to "open" the picker
            var dailyInput = document.getElementById('daily_date');
            if(dailyInput){
                dailyInput.value = formatToday();
                dailyInput.focus();
            }
        }
    }

    var sel = document.getElementById('report_type');
    if(sel){
        sel.addEventListener('change', toggleFields);
    }
    // initial toggle based on server state
    toggleFields();
})();
</script>
@endpush
@endsection
