@extends('include')
@section('backTitle') Capital Account @endsection
@section('bodyTitleFrist') Capital Account (Total Business) @endsection
@section('bodyTitleEnd') @endsection
@section('bodyContent')
<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="text-muted mb-1">Total Bank Balance</h6>
              <div class="h4 mb-0">{{ number_format($totalBank, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="text-muted mb-1">Total Client Balance</h6>
              <div class="h4 mb-0">{{ number_format($totalClients, 2) }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="text-muted mb-1">Capital (Bank + Clients + Mobile)</h6>
              <div class="h4 mb-0">{{ number_format($capitalTotal, 2) }}</div>
              <div class="small text-muted mt-2">
                Mobile Balance: <strong>{{ number_format($totalMobileBalance ?? 0, 2) }}</strong>
                <span class="ms-3">Today's Mobile Profit: <strong>{{ number_format($totalMobileProfit ?? 0, 2) }}</strong></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-end mt-3 no-print">
        <button id="printBtn" type="button" class="btn btn-outline-secondary">Print</button>
      </div>
    </div>
  </div>

  <div class="card mt-3" id="capital-report-print-section">
    <div class="card-header">
      <h5 class="card-title mb-0">Breakdown</h5>
    </div>
    <div class="card-body">
      <!-- Print-only summary header and totals -->
      <div class="print-only mb-3">
        <h4 class="mb-2">Capital Account (Total Business)</h4>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <tbody>
              <tr>
                <th style="width:260px">Total Bank Balance</th>
                <td class="text-end">{{ number_format($totalBank, 2) }}</td>
              </tr>
              <tr>
                <th>Total Client Balance</th>
                <td class="text-end">{{ number_format($totalClients, 2) }}</td>
              </tr>
              <tr>
                <th>Mobile Balance (Today)</th>
                <td class="text-end">{{ number_format($totalMobileBalance ?? 0, 2) }}</td>
              </tr>
              <tr>
                <th>Mobile Profit (Today)</th>
                <td class="text-end">{{ number_format($totalMobileProfit ?? 0, 2) }}</td>
              </tr>
              <tr>
                <th>Capital Total</th>
                <td class="text-end"><strong>{{ number_format($capitalTotal, 2) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="row">
        <div class="col-12 mb-3">
          <div class="alert alert-info">
            Mobile banking balances and today's profit are included in Capital. Profit shown for reference and not added twice.
          </div>
        </div>
        <div class="col-md-6">
          <h6 class="mb-2">Bank Accounts</h6>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>Account</th>
                  <th>Number</th>
                  <th class="text-end" style="width:140px">Balance</th>
                </tr>
              </thead>
              <tbody>
              @forelse($bankAccounts as $b)
                <tr>
                  <td>{{ $b->account_name }}</td>
                  <td>{{ $b->account_number }}</td>
                  <td class="text-end">{{ number_format($b->balance ?? 0,2) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted">No bank accounts found.</td></tr>
              @endforelse
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="2" class="text-end">Total</th>
                  <th class="text-end">{{ number_format($totalBank,2) }}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="col-md-6">
          <h6 class="mb-2">Clients</h6>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th class="text-end" style="width:140px">Balance</th>
                </tr>
              </thead>
              <tbody>
              @forelse($clients as $c)
                <tr>
                  <td>{{ $c->client_name }}</td>
                  <td>{{ $c->client_phone }}</td>
                  <td class="text-end">{{ number_format($c->balance ?? 0,2) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted">No clients found.</td></tr>
              @endforelse
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="2" class="text-end">Total</th>
                  <th class="text-end">{{ number_format($totalClients,2) }}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
@page { size: A4 portrait; margin: 15mm 12mm; }
@media screen { .print-only { display: none !important; } }
@media print {
  body * { visibility: hidden !important; }
  #capital-report-print-section, #capital-report-print-section * { visibility: visible !important; }
  #capital-report-print-section { position: absolute; left: 0; right: 0; top: 0; width: auto; }
  .no-print { display: none !important; }
  .print-only { display: block !important; }
  #capital-report-print-section table.table { font-size: 12px; }
  #capital-report-print-section th, #capital-report-print-section td { padding: 4px 6px !important; }
  #capital-report-print-section thead { display: table-header-group; }
  #capital-report-print-section tfoot { display: table-footer-group; }
  #capital-report-print-section tr, #capital-report-print-section table { page-break-inside: avoid; break-inside: avoid; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  var printBtn = document.getElementById('printBtn');
  if (printBtn) { printBtn.addEventListener('click', function(){ window.print(); }); }
});
</script>
@endpush
@endsection
