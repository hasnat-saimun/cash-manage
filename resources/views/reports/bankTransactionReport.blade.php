@extends('include')
@section('backTitle') Bank Report @endsection
@section('bodyTitleFrist') Account Wise Transaction Report @endsection
@section('bodyTitleEnd')
    <a href="{{ route('transactionList') }}"> Transaction List</a>
@endsection
@section('bodyContent')
<div class="col-12">
    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger no-print">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form id="reportForm" method="GET" action="{{ route('reports.bankTransaction') }}" class="row g-2 align-items-end no-print">
                <div class="col-md-4">
                    <label class="form-label">Account</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">-- Select account --</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}" @if(isset($accountId) && $accountId == $a->id) selected @endif>{{ $a->account_name ?? $a->account_number ?? 'Account '.$a->id }}</option>
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

                <div class="col-md-2 date-first">
                    <label class="form-label" id="dateFirstLabel">Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $from ?? $date ?? \Carbon\Carbon::today()->toDateString() }}">
                </div>
                <div class="col-md-2 date-second" style="display:none;">
                    <label class="form-label">To</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $to ?? '' }}">
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary" type="submit">Generate</button>
                </div>
                <div class="col-md-2 d-grid no-print">
                    <button id="exportCsvBtn" type="button" class="btn btn-outline-success">Export CSV</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $isDaily = (isset($reportType) && $reportType === 'daily');
        $colCount = $isDaily ? 4 : 5; // daily: Description,Debit,Credit,Balance ; custom: Date + those
        $colsBeforeBalance = $colCount - 1;
    @endphp

    <div class="card mt-3">
        <div class="card-header"><h5 class="card-title mb-0">Account Statement</h5></div>
        <div class="card-body">
            @if(empty($accountId))
                <div class="alert alert-info">Please select an account and date range then click Generate.</div>
            @else
                <div class="mb-3 d-flex justify-content-between">
                    <div>
                        <div><strong>Account:</strong> {{ optional($accounts->firstWhere('id',$accountId))->account_name ?? '—' }}</div>
                        <div><strong>Account No:</strong> {{ optional($accounts->firstWhere('id',$accountId))->account_number ?? '—' }}</div>
                    </div>
                    <div class="text-end">
                        <div><strong>Range:</strong> {{ $rangeLabel ?? ($from ?? $date ?? '-') }}</div>
                        @if(isset($openingBalance)) <div><strong>Opening:</strong> {{ number_format($openingBalance,2) }}</div> @endif
                        @if(isset($closingBalance)) <div><strong>Closing:</strong> {{ number_format($closingBalance,2) }}</div> @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                @unless($isDaily)<th style="width:130px">Date</th>@endunless
                                <th>Description</th>
                                <th class="text-end" style="width:120px">Debit</th>
                                <th class="text-end" style="width:120px">Credit</th>
                                <th class="text-end" style="width:140px">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($openingBalance))
                                <tr class="table-secondary">
                                    <td colspan="{{ $colsBeforeBalance }}"><strong>Opening Balance</strong></td>
                                    <td class="text-end"><strong>{{ number_format($openingBalance,2) }}</strong></td>
                                </tr>
                            @endif

                            @forelse($rows as $r)
                                <tr>
                                    @unless($isDaily)<td>{{ $r['date'] }}</td>@endunless
                                    <td>{{ $r['description'] }}</td>
                                    <td class="text-end">{{ $r['debit'] ? number_format($r['debit'],2) : '-' }}</td>
                                    <td class="text-end">{{ $r['credit'] ? number_format($r['credit'],2) : '-' }}</td>
                                    <td class="text-end">{{ number_format($r['balance'],2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $colCount }}" class="text-center text-muted">No transactions found.</td></tr>
                            @endforelse

                            @if(isset($closingBalance))
                                <tr class="table-secondary">
                                    <td colspan="{{ $colsBeforeBalance }}"><strong>Closing Balance</strong></td>
                                    <td class="text-end"><strong>{{ number_format($closingBalance,2) }}</strong></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            @php $labelColspan = max(1, $colCount - 3); @endphp
                            <tr>
                                <th colspan="{{ $labelColspan }}" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($totalDebit ?? 0,2) }}</th>
                                <th class="text-end">{{ number_format($totalCredit ?? 0,2) }}</th>
                                <th class="text-end"></th>
                            </tr>
                            <tr>
                                <th colspan="{{ $labelColspan }}" class="text-end">Grand Total (Credit - Debit)</th>
                                <th colspan="3" class="text-end">
                                    <span class="fw-semibold">{{ number_format($grandTotal ?? 0,2) }}</span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatToday(){ var d=new Date(),m=d.getMonth()+1,day=d.getDate(),y=d.getFullYear(); if(m<10)m='0'+m; if(day<10)day='0'+day; return y+'-'+m+'-'+day;}
    function showElement(el){ if(el) el.style.display='block'; }
    function hideElement(el){ if(el) el.style.display='none'; }

    function toggleFields(){
        var typeEl=document.getElementById('report_type');
        if(!typeEl) return;
        var type=typeEl.value;
        var firstWrap=document.querySelector('.date-first');
        var secondWrap=document.querySelector('.date-second');
        var firstLabel=document.getElementById('dateFirstLabel');
        var fromEl=document.getElementById('from_date');
        var toEl=document.getElementById('to_date');
        if(type==='custom'){ showElement(firstWrap); showElement(secondWrap); if(firstLabel) firstLabel.innerText='From'; if(fromEl) fromEl.required=true; if(toEl) toEl.required=true; }
        else { showElement(firstWrap); hideElement(secondWrap); if(firstLabel) firstLabel.innerText='Date'; if(fromEl){ if(!fromEl.value) fromEl.value=formatToday(); fromEl.required=true;} if(toEl){ toEl.required=false; toEl.value=''; } }
    }

    var typeSel=document.getElementById('report_type');
    if(typeSel) typeSel.addEventListener('change', toggleFields);
    toggleFields();

    function qsFromForm(form){ var params=new URLSearchParams(); Array.from(form.elements).forEach(function(el){ if(!el.name) return; if((el.type==='checkbox'||el.type==='radio')&&!el.checked) return; if(el.value==='') return; params.append(el.name,el.value); }); return params.toString(); }

    var exportBtn=document.getElementById('exportCsvBtn'), form=document.getElementById('reportForm');
    if(exportBtn && form){ exportBtn.addEventListener('click', function(){ var qs=qsFromForm(form); var url="{{ route('reports.bankTransaction.export') }}"; if(qs) url += '?' + qs; window.open(url,'_blank'); }); }
});
</script>
@endpush
@endsection
