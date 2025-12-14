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

    
    <!-- Report table -->
    <div class="card mt-3" id="client-report-print-section">
        <div class="card-header">
            <h5 class="card-title mb-0">Client Calculas</h5>
        </div>
        <div class="card-body">
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

            @php
                $isDaily = (isset($reportType) && $reportType === 'daily');
                // determine statement label: for daily show single date, otherwise the rangeLabel
                if ($isDaily) {
                    $statementDate = $from ?? $date ?? \Carbon\Carbon::today()->toDateString();
                } else {
                    $statementDate = $rangeLabel ?? ($from && $to ? ($from . ' — ' . $to) : '—');
                }
                $clientObj = null;
                if (!empty($clientId)) {
                    $clientObj = $clients->firstWhere('id', $clientId);
                }
                // number of visible columns in table (used for colspan adjustments)
                $colCount = $isDaily ? 5 : 6; // daily: Description,Source,Debit,Credit,Balance (5) ; custom: Date + those (6)
                $colsBeforeBalance = $colCount - 1;
            @endphp

            {{-- Statement header: client details + statement date --}}
            <div class="mb-3 d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">Client:</h6>
                    <div class="small text-muted">
                        <div><strong>Name:</strong> {{ $clientObj?->client_name ?? '—' }}</div>
                        <div><strong>Mobile:</strong> {{ $clientObj?->client_phone ?? '—' }}</div>
                        <div><strong>Email:</strong> {{ $clientObj?->client_email ?? '—' }}</div>
                    </div>
                </div>
                <div class="text-end">
                    <h6 class="mb-1">Statement</h6>
                    <div class="small text-muted">
                        <div><strong>Date/Range:</strong> {{ $statementDate }}</div>
                        @if(isset($rangeLabel) && !$isDaily)
                            <div><strong>Range label:</strong> {{ $rangeLabel }}</div>
                        @endif
                    </div>
                </div>
            </div>
            

            @if(isset($openingBalance) || isset($closingBalance) || isset($grandTotal))
                @php
                    // determine grand total status (positive => Balance, negative => Due)
                    $gt = isset($grandTotal) ? (float) $grandTotal : 0.0;
                    $grandLabel = $gt < 0 ? 'Due' : 'Balance';
                    $grandClass = $gt < 0 ? 'bg-danger' : 'bg-success';
                @endphp

                <div class="mb-2 d-flex gap-3 align-items-center">
                    @if(isset($openingBalance))
                        <div><small class="text-muted">Opening Balance at start: <strong>{{ number_format($openingBalance,2) }}</strong></small></div>
                    @endif
                    @if(isset($closingBalance))
                        <div><small class="text-muted">Closing Balance at end: <strong>{{ number_format($closingBalance,2) }}</strong></small></div>
                    @endif
                    @if(isset($grandTotal))
                        <div class="ms-3">
                            <span class="badge {{ $grandClass }} text-white">
                                {{ $grandLabel }} ({{ number_format($gt,2) }})
                            </span>
                        </div>
                    @endif
                    @if(isset($txnCount))
                        <div class="ms-3">
                            <small class="text-muted">Total Transactions: <strong>{{ $txnCount }}</strong></small>
                        </div>
                    @endif
                    @if(isset($debitTxnCount) || isset($creditTxnCount))
                        <div class="ms-3">
                            <small class="text-muted">Txn Count — Debit: <strong>{{ $debitTxnCount ?? 0 }}</strong>, Credit: <strong>{{ $creditTxnCount ?? 0 }}</strong></small>
                        </div>
                    @endif
                </div>
            @endif

            @if(empty($clientId))
                <div class="alert alert-info">Please select a client and date range then click Generate.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                @unless($isDaily)
                                    <th style="width:130px">Date</th>
                                @endunless
                                <th>Description</th>
                                <th>Source</th>
                                <th class="text-end" style="width:120px">Debit</th>
                                <th class="text-end" style="width:120px">Credit</th>
                                <th class="text-end" style="width:140px">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Opening balance row (inside table body) --}}
                            @if(isset($openingBalance))
                                <tr class="table-secondary">
                                    <td colspan="{{ $colsBeforeBalance }}"><strong>Opening Balance</strong></td>
                                    <td class="text-end"><strong>{{ number_format($openingBalance,2) }}</strong></td>
                                </tr>
                            @endif

                            @forelse($rows as $r)
                                <tr>
                                    @unless($isDaily)
                                        <td>{{ $r['date'] }}</td>
                                    @endunless
                                    <td>{{ $r['description'] }}</td>
                                    <td>{{ $r['source'] }}</td>
                                    <td class="text-end">{{ $r['debit'] ? number_format($r['debit'],2) : '-' }}</td>
                                    <td class="text-end">{{ $r['credit'] ? number_format($r['credit'],2) : '-' }}</td>
                                    <td class="text-end">{{ number_format($r['balance'],2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colCount }}" class="text-center text-muted">No transactions found for the selected client and date range.</td>
                                </tr>
                            @endforelse

                            {{-- Closing balance row (inside table body) --}}
                            @php
                                // prefer controller-provided closingBalance; if not, derive from last row or opening + totals
                                $closing = $closingBalance ?? null;
                                if ($closing === null && !empty($rows)) {
                                    $last = end($rows);
                                    $closing = $last['balance'] ?? null;
                                }
                                if ($closing === null) {
                                    $closing = (isset($openingBalance) ? $openingBalance : 0) + ($totalCredit ?? 0) - ($totalDebit ?? 0);
                                }
                            @endphp
                            @if(isset($closing))
                                <tr class="table-secondary">
                                    <td colspan="{{ $colsBeforeBalance }}"><strong>Closing Balance</strong></td>
                                    <td class="text-end"><strong>{{ number_format($closing,2) }}</strong></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            @php $labelColspan = max(1, $colCount - 3); @endphp
                            <tr>
                                <th colspan="{{ $labelColspan }}" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($totalDebit,2) }}</th>
                                <th class="text-end">{{ number_format($totalCredit,2) }}</th>
                                <th class="text-end"></th>
                            </tr>
                            <tr>
                                <th colspan="{{ $labelColspan }}" class="text-end">Grand Total (Credit - Debit)</th>
                                <th colspan="3" class="text-end">
                                    <span class="fw-semibold">{{ number_format($grandTotal,2) }}</span>
                                    @php $gt = (float) ($grandTotal ?? 0); @endphp
                                    @if($gt < 0)
                                        <span class="badge bg-danger ms-2">Due</span>
                                    @else
                                        <span class="badge bg-success ms-2">Balance</span>
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

@push('styles')
<style>
@page { size: A4 portrait; margin: 20mm 15mm; }
/* Hide print-only elements on screen */
@media screen {
    .print-only { display: none !important; }
}
/* Print only the report section */
@media print {
    /* hide everything by default */
    body * { visibility: hidden !important; }
    /* show only the selected section */
    #client-report-print-section, #client-report-print-section * { visibility: visible !important; }
    /* position section within page margins for clean print */
    #client-report-print-section {
        position: absolute; left: 0; right: 0; top: 0; width: auto;
        /* scale down to try to fit one page; adjust as needed */
        transform-origin: top left;
        transform: scale(var(--print-scale, 0.9));
        width: calc(100% / var(--print-scale, 0.9));
    }

    /* compact table for print */
    #client-report-print-section table.table { font-size: 12px; }
    #client-report-print-section th, #client-report-print-section td { padding: 4px 6px !important; }

    /* keep headers/footers with table and avoid row splits */
    #client-report-print-section thead { display: table-header-group; }
    #client-report-print-section tfoot { display: table-footer-group; }
    #client-report-print-section tr, 
    #client-report-print-section table, 
    #client-report-print-section img { page-break-inside: avoid; break-inside: avoid; }

    /* generic helpers */
    .no-print { display: none !important; }
    .print-only { display: block !important; }
}
</style>
@endpush

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

    // qsFromForm helper (was missing; used by Export button)
    function qsFromForm(form) {
        var params = new URLSearchParams();
        var elements = form.elements;
        for (var i = 0; i < elements.length; i++) {
            var el = elements[i];
            if (!el.name) continue;
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) continue;
            // include zero values and allow '0'
            if (el.value === '') continue;
            params.append(el.name, el.value);
        }
        return params.toString();
    }

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
