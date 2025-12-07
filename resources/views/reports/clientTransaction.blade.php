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
            {{-- show validation errors --}}
            @if($errors->any())
                <div class="alert alert-danger no-print">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="reportForm" method="GET" action="{{ route('reports.clientTransaction') }}" class="row g-2 align-items-end no-print">
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

                <!-- First date box (used for Daily as "Date", and for Custom as "From") -->
                <div class="col-md-2 date-first">
                    <label class="form-label" id="dateFirstLabel">Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $from ?? $date ?? \Carbon\Carbon::today()->toDateString() }}">
                </div>

                <!-- Second date box (used only for Custom as "To") -->
                <div class="col-md-2 date-second" style="display:none;">
                    <label class="form-label">To</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $to ?? '' }}">
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary" type="submit">Generate</button>
                </div>

                <!-- new buttons -->
                <div class="col-md-2 d-grid no-print">
                    <button id="exportCsvBtn" type="button" class="btn btn-outline-success">Export CSV</button>
                </div>
                <div class="col-md-2 d-grid no-print">
                    <button id="printBtn" type="button" class="btn btn-outline-secondary">Print</button>
                </div>

                @if(isset($rangeLabel) && $rangeLabel)
                    <div class="col-12 mt-2">
                        <small class="text-muted">Showing results for: <strong>{{ $rangeLabel }}</strong></small>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Print header: visible only during printing -->
    <div class="card mt-3 print-only">
        <div class="card-body">
            <h5 class="mb-1">Client Report</h5>
            <div>
                <strong>Client:</strong>
                @php
                    $clientLabel = '';
                    if(!empty($clientId)) {
                        $cl = $clients->firstWhere('id', $clientId);
                        $clientLabel = $cl?->client_name ?? $clientId;
                    }
                @endphp
                {{ $clientLabel ?: '—' }}
                <span class="ms-3"><strong>Range:</strong> {{ $rangeLabel ?? '—' }}</span>
            </div>
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
                                <th>Source</th>
                                <th class="text-end" style="width:150px">Debit</th>
                                <th class="text-end" style="width:150px">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                <tr>
                                    <td>{{ $r['date'] }}</td>
                                    <td>{{ $r['description'] }}</td>
                                    <td>{{ $r['source'] }}</td>
                                    <td class="text-end">{{ $r['debit'] ? number_format($r['debit'],2) : '-' }}</td>
                                    <td class="text-end">{{ $r['credit'] ? number_format($r['credit'],2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No transactions found for the selected client and date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($totalDebit,2) }}</th>
                                <th class="text-end">{{ number_format($totalCredit,2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Grand Total (Credit - Debit)</th>
                                <th colspan="2" class="text-end">
                                    <span class="fw-semibold">{{ number_format($grandTotal,2) }}</span>
                                    @if($grandTotal >= 0)
                                        <span class="badge bg-success ms-2">Balance</span>
                                    @else
                                        <span class="badge bg-danger ms-2">Due</span>
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
document.addEventListener('DOMContentLoaded', function () {
    function formatToday() {
        var d = new Date(), m = d.getMonth()+1, day = d.getDate(), y = d.getFullYear();
        if (m < 10) m = '0' + m;
        if (day < 10) day = '0' + day;
        return y + '-' + m + '-' + day;
    }

    function showElement(el){
        if(!el) return;
        // use inline style for reliability
        el.style.display = 'block';
    }
    function hideElement(el){
        if(!el) return;
        el.style.display = 'none';
    }

    function toggleFields(){
        var typeEl = document.getElementById('report_type');
        if (!typeEl) return;
        var type = typeEl.value;
        var firstWrap = document.querySelector('.date-first');
        var secondWrap = document.querySelector('.date-second');
        var firstLabel = document.getElementById('dateFirstLabel');
        var fromEl = document.getElementById('from_date');
        var toEl = document.getElementById('to_date');

        if(type === 'custom'){
            showElement(firstWrap);
            showElement(secondWrap);
            if(firstLabel) firstLabel.innerText = 'From';
            if(fromEl) fromEl.required = true;
            if(toEl) toEl.required = true;
            // removed toEl.focus() to avoid stealing focus from the select
        } else {
            showElement(firstWrap);
            hideElement(secondWrap);
            if(firstLabel) firstLabel.innerText = 'Date';
            if(fromEl){
                if (!fromEl.value) fromEl.value = formatToday();
                fromEl.required = true;
            }
            if(toEl){
                toEl.required = false;
                toEl.value = '';
            }
        }
    }

    // Attach only the 'change' event to the select (do not attach 'click' which interferes)
    var typeSel = document.getElementById('report_type');
    if(typeSel){
        typeSel.addEventListener('change', toggleFields);
    }

    // initial toggle
    toggleFields();

    // export / print handlers
    var exportBtn = document.getElementById('exportCsvBtn');
    var printBtn = document.getElementById('printBtn');
    var form = document.getElementById('reportForm');

    if (exportBtn && form){
        exportBtn.addEventListener('click', function(){
            var qs = qsFromForm(form);
            var url = "{{ route('reports.clientTransaction.export') }}";
            if (qs) url += '?' + qs;
            window.open(url, '_blank');
        });
    }
    if (printBtn){
        printBtn.addEventListener('click', function(){
            window.print();
        });
    }

    // client-side submit validation: require both dates for custom
    if(form){
        form.addEventListener('submit', function(e){
            var type = document.getElementById('report_type')?.value;
            if(type === 'custom'){
                var from = document.getElementById('from_date')?.value;
                var to = document.getElementById('to_date')?.value;
                if(!from || !to){
                    e.preventDefault();
                    // show inline alert
                    var existing = document.getElementById('client-report-date-error');
                    if(!existing){
                        var alert = document.createElement('div');
                        alert.id = 'client-report-date-error';
                        alert.className = 'alert alert-warning no-print mt-2';
                        alert.innerText = 'Please provide both From and To dates for custom date range.';
                        form.parentNode.insertBefore(alert, form.nextSibling);
                    }
                    // ensure To box visible
                    toggleFields();
                    return false;
                }
            }
            return true;
        });
    }
});
</script>
@endpush
@endsection
