@extends('include')
@section('backTitle') Mobile Banking @endsection
@section('bodyTitleFrist') Cash Calculator @endsection
@section('bodyTitleEnd') Daily Cash Calculation @endsection
@section('bodyContent')
<div class="col-12">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Daily Mobile Banking Capital Summary</h5>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="row mb-4">
        @php
          $netCashFlow = ($totalCredit ?? 0) - ($totalDebit ?? 0);
          $currentAsset = ($todayTotalBalance ?? 0) + $netCashFlow;
        @endphp
        <div class="col-md-3">
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="text-muted mb-2">Yesterday's Total Balance</h6>
              <h3 class="text-dark">{{ number_format($yesterdayBalance, 2) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="text-muted mb-2">Today's Total Balance</h6>
              <h3 class="text-dark">{{ number_format($todayTotalBalance, 2) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="text-muted mb-2">Capital Change</h6>
              <h3 class="@if($cashDifference >= 0) text-success @else text-danger @endif">
                {{ ($cashDifference >= 0 ? '+' : '') }}{{ number_format($cashDifference, 2) }}
              </h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="text-muted mb-2">Total Accounts</h6>
              <h3 class="text-dark">{{ $todayEntries->count() }}/{{ $accounts->count() }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-success">
            <div class="card-body">
              <h6 class="text-success mb-2">Current Mobile Asset</h6>
              <h3 class="text-success mb-1">{{ number_format($currentAsset, 2) }}</h3>
              <small class="text-muted d-block">Formula: Today's Mobile Total + (Credit - Debit)</small>
            </div>
          </div>
        </div>
      </div>

      <hr>

      <h6 class="text-muted mb-3">Today's Mobile Banking Entries ({{ $today }})</h6>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Provider</th>
              <th>Number</th>
              <th class="text-end">Balance</th>
            </tr>
          </thead>
          <tbody>
            @forelse($todayEntries as $entry)
              <tr>
                <td>{{ $entry->provider ?? 'N/A' }}</td>
                <td>{{ $entry->number }}</td>
                <td class="text-end">{{ number_format($entry->balance, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">No entries for today yet.</td></tr>
            @endforelse
          </tbody>
          @if($todayEntries->count() > 0)
            <tfoot class="table-light">
              <tr>
                <th colspan="2">Total</th>
                <th class="text-end">{{ number_format($todayTotalBalance, 2) }}</th>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Daily Debit/Credit Records</h5>
    </div>
    <div class="card-body">
      <p class="text-muted mb-3">Add individual debit (money out) and credit (money in) transactions for today.</p>
      
      <!-- Add New Record Form -->
      <form method="POST" action="{{ route('mobile.cashRecords.add') }}" class="row g-3 mb-4 p-3 bg-light rounded">
        @csrf
        <div class="col-md-2">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="{{ old('date', $today) }}" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Type</label>
          <select name="type" class="form-select" required>
            <option value="">Select Type</option>
            <option value="debit" @if(old('type') === 'debit') selected @endif>Debit (Out)</option>
            <option value="credit" @if(old('type') === 'credit') selected @endif>Credit (In)</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Amount</label>
          <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" name="amount" step="0.01" class="form-control" placeholder="0.00" value="{{ old('amount') }}" required>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Description</label>
          <input type="text" name="description" class="form-control" placeholder="e.g., Cash deposit, Withdrawal" value="{{ old('description') }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">Reference No</label>
          <input type="text" name="reference_no" class="form-control" placeholder="e.g., CHQ123" value="{{ old('reference_no') }}">
        </div>
        <div class="col-md-1 d-grid">
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </form>

      <hr>

      <!-- Summary Cards -->
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="card border-danger">
            <div class="card-body">
              <h6 class="text-danger mb-2">Total Debit (Money Out)</h6>
              <h3 class="text-danger">৳ {{ number_format($totalDebit, 2) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border-success">
            <div class="card-body">
              <h6 class="text-success mb-2">Total Credit (Money In)</h6>
              <h3 class="text-success">৳ {{ number_format($totalCredit, 2) }}</h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Records Table -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th class="text-end">Amount</th>
              <th>Description</th>
              <th>Reference</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cashRecords as $record)
              <tr>
                <td>{{ $record->date }}</td>
                <td>
                  <span class="badge @if($record->type === 'debit') bg-danger @else bg-success @endif">
                    {{ ucfirst($record->type) }}
                  </span>
                </td>
                <td class="text-end fw-bold">
                  <span class="@if($record->type === 'debit') text-danger @else text-success @endif">
                    {{ ($record->type === 'debit' ? '-' : '+') }}{{ number_format($record->amount, 2) }}
                  </span>
                </td>
                <td>{{ $record->description ?? '—' }}</td>
                <td>{{ $record->reference_no ?? '—' }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRecordModal{{ $record->id }}">Edit</button>
                  <form method="POST" action="{{ route('mobile.cashRecords.delete', $record->id) }}" class="d-inline" onsubmit="return confirm('Delete this record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>

              <!-- Edit Modal -->
              <div class="modal fade" id="editRecordModal{{ $record->id }}" tabindex="-1" aria-labelledby="editRecordLabel{{ $record->id }}" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editRecordLabel{{ $record->id }}">Edit Record</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('mobile.cashRecords.update') }}">
                      @csrf
                      <input type="hidden" name="id" value="{{ $record->id }}">
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Type</label>
                          <select name="type" class="form-select" required>
                            <option value="debit" @if($record->type === 'debit') selected @endif>Debit (Money Out)</option>
                            <option value="credit" @if($record->type === 'credit') selected @endif>Credit (Money In)</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Amount</label>
                          <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="amount" step="0.01" value="{{ $record->amount }}" class="form-control" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Description</label>
                          <input type="text" name="description" class="form-control" value="{{ $record->description ?? '' }}">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Reference No</label>
                          <input type="text" name="reference_no" class="form-control" value="{{ $record->reference_no ?? '' }}">
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
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">No debit/credit records yet. Add one to get started.</td></tr>
            @endforelse
          </tbody>
          @if($cashRecords->count() > 0)
            <tfoot class="table-light">
              <tr>
                <th colspan="2">Total</th>
                <th class="text-end fw-bold">
                  <span class="text-success">+{{ number_format($totalCredit, 2) }}</span> / <span class="text-danger">-{{ number_format($totalDebit, 2) }}</span>
                </th>
                <th colspan="3" class="text-end">
                  <strong class="@if(($totalCredit - $totalDebit) >= 0) text-success @else text-danger @endif">
                    Net: {{ ($totalCredit - $totalDebit) >= 0 ? '+' : '' }}{{ number_format($totalCredit - $totalDebit, 2) }}
                  </strong>
                </th>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title mb-0">Manual Capital Calculator</h5>
    </div>
    <div class="card-body">
      <p class="text-muted">Use this calculator to compute your daily closing capital based on opening capital and daily transactions.</p>
      
      <form id="cashCalculatorForm" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Opening Capital</label>
          <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" id="openingCapital" step="0.01" class="form-control" placeholder="Enter opening capital" required>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Today's Mobile Banking Total</label>
          <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" id="mobileBankingTotal" step="0.01" class="form-control" value="{{ $todayTotalBalance }}" placeholder="Automatically populated" readonly>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Debit (Amount Out)</label>
          <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" id="debit" step="0.01" class="form-control" placeholder="Auto-calculated from records" value="{{ $totalDebit }}" readonly>
          </div>
          <small class="form-text text-muted">Money withdrawn or paid out</small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Credit (Amount In)</label>
          <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" id="credit" step="0.01" class="form-control" placeholder="Auto-calculated from records" value="{{ $totalCredit }}" readonly>
          </div>
          <small class="form-text text-muted">Money deposited or received</small>
        </div>

        <div class="col-12">
          <button type="button" class="btn btn-primary" id="calculateBtn">Calculate Closing Capital</button>
          <button type="reset" class="btn btn-outline-secondary ms-2">Reset</button>
        </div>
      </form>

      <div id="resultContainer" class="mt-4" style="display: none;">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daily Capital Calculation Report</h5>
            <div>
              <button type="button" class="btn btn-sm btn-light" onclick="printReport()">
                <i class="bx bx-printer me-1"></i> Print
              </button>
              <button type="button" class="btn btn-sm btn-light" onclick="downloadReport()">
                <i class="bx bx-download me-1"></i> Download
              </button>
            </div>
          </div>
          
          <div id="reportContent" class="card-body p-4">
            <!-- Report Header -->
            <div class="text-center mb-4 pb-3 border-bottom">
              <h4 class="fw-bold text-dark mb-2">{{ config('app.name') }}</h4>
              <p class="text-muted mb-1">Daily Capital Closing Statement</p>
              <p class="text-muted small">Date: <span id="reportDate"></span></p>
            </div>

            <!-- Summary Section -->
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="p-3 bg-light rounded mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Opening Capital:</span>
                    <h5 class="mb-0 text-dark">৳ <span id="resultOpening">0.00</span></h5>
                  </div>
                </div>
                <div class="p-3 bg-light rounded mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Credit (Money In):</span>
                    <h5 class="mb-0 text-success">+ ৳ <span id="resultCredit">0.00</span></h5>
                  </div>
                </div>
                <div class="p-3 bg-light rounded mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Debit (Money Out):</span>
                    <h5 class="mb-0 text-danger">- ৳ <span id="resultDebit">0.00</span></h5>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="p-4 bg-gradient rounded-lg border-2 border-success" style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-color: #4caf50 !important;">
                  <h6 class="text-muted mb-3">Closing Capital</h6>
                  <h2 class="text-success fw-bold mb-3">৳ <span id="resultClosing">0.00</span></h2>
                  
                  <div class="alert mb-0" id="netChangeAlert" role="alert">
                    <strong>Net Change: </strong><span id="netChangeValue">0.00</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="table-responsive mb-4">
              <table class="table table-borderless align-middle">
                <tbody>
                  <tr class="border-bottom">
                    <td class="text-muted">Opening Balance</td>
                    <td class="text-end fw-bold">৳ <span id="detailOpening">0.00</span></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-info">Mobile Banking Total (Today)</td>
                    <td class="text-end text-info fw-bold">৳ <span id="detailMobileTotal">0.00</span></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-success">Add: Total Credits</td>
                    <td class="text-end text-success fw-bold">+ ৳ <span id="detailCredit">0.00</span></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-danger">Less: Total Debits</td>
                    <td class="text-end text-danger fw-bold">- ৳ <span id="detailDebit">0.00</span></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-primary">Net Cash Flow (Credit - Debit)</td>
                    <td class="text-end text-primary fw-bold">৳ <span id="detailNetCash">0.00</span></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="fw-bold text-success">Current Mobile Asset</td>
                    <td class="text-end fw-bold text-success fs-5">৳ <span id="detailCurrentAsset">0.00</span></td>
                  </tr>
                  <tr class="bg-light">
                    <td class="fw-bold">Closing Balance</td>
                    <td class="text-end fw-bold text-success fs-5">৳ <span id="detailClosing">0.00</span></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Daily Capital Records -->
            @if($cashRecords->count() > 0)
            <div class="mb-4 pb-4 border-bottom">
              <h6 class="fw-bold text-dark mb-3">Daily Debit/Credit Records</h6>
              <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-muted">Date</th>
                      <th class="text-muted">Type</th>
                      <th class="text-muted">Description</th>
                      <th class="text-muted">Reference</th>
                      <th class="text-end text-muted">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($cashRecords as $record)
                      <tr>
                        <td class="text-muted small">{{ $record->date }}</td>
                        <td>
                          <span class="badge @if($record->type === 'debit') bg-danger @else bg-success @endif">
                            {{ ucfirst($record->type) }}
                          </span>
                        </td>
                        <td class="text-muted small">{{ $record->description ?? '—' }}</td>
                        <td class="text-muted small">{{ $record->reference_no ?? '—' }}</td>
                        <td class="text-end small fw-bold">
                          <span class="@if($record->type === 'debit') text-danger @else text-success @endif">
                            {{ ($record->type === 'debit' ? '-' : '+') }}{{ number_format($record->amount, 2) }}
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            @endif

            <!-- Footer -->
            <div class="text-center text-muted small pt-3 border-top">
              <p class="mb-1">This is a computer-generated report and does not require a signature.</p>
              <p class="mb-0">Generated on: <span id="reportDateTime"></span></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function downloadReport() {
  const reportContent = document.getElementById('reportContent');
  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Daily Capital Calculation Report</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <style>
        body { margin: 40px; font-family: Arial, sans-serif; }
        .bg-gradient { background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%) !important; }
        .border-2 { border-width: 2px !important; }
      </style>
    </head>
    <body>
      ${reportContent.innerHTML}
      <script>
        setTimeout(() => { window.print(); }, 500);
      <\/script>
    </body>
    </html>
  `;
  
  const blob = new Blob([html], { type: 'text/html' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'capital-calculation-report-' + new Date().toISOString().split('T')[0] + '.html';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
}

function formatDate(date) {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

function formatDateTime(date) {
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return date.toLocaleDateString('en-US', options) + ' ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

(function() {
  function initCalculator() {
    const calculateBtn = document.getElementById('calculateBtn');
    
    if (!calculateBtn) {
      console.log('Calculate button not found');
      return;
    }

    calculateBtn.onclick = function(e) {
      e.preventDefault();
      console.log('Calculate button clicked');
      
      const openingCapitalInput = document.getElementById('openingCapital');
      const debitInput = document.getElementById('debit');
      const creditInput = document.getElementById('credit');

      if (!openingCapitalInput || !debitInput || !creditInput) {
        alert('Error: Input fields not found');
        return;
      }

      const openingCapital = parseFloat(openingCapitalInput.value) || 0;
      const debit = parseFloat(debitInput.value) || 0;
      const credit = parseFloat(creditInput.value) || 0;

      console.log('Values:', {openingCapital, debit, credit});

      const mobileBankingTotal = parseFloat(document.getElementById('mobileBankingTotal').value) || 0;
      const netChange = credit - debit;
      const currentAsset = mobileBankingTotal + netChange;
      const closingCapital = openingCapital + currentAsset;

      console.log('Results:', {closingCapital, netChange});

      // Get all result elements
      const resultElements = {
        resultOpening: document.getElementById('resultOpening'),
        resultCredit: document.getElementById('resultCredit'),
        resultDebit: document.getElementById('resultDebit'),
        resultClosing: document.getElementById('resultClosing'),
        detailOpening: document.getElementById('detailOpening'),
        detailMobileTotal: document.getElementById('detailMobileTotal'),
        detailCredit: document.getElementById('detailCredit'),
        detailDebit: document.getElementById('detailDebit'),
        detailNetCash: document.getElementById('detailNetCash'),
        detailCurrentAsset: document.getElementById('detailCurrentAsset'),
        detailClosing: document.getElementById('detailClosing'),
        netChangeValue: document.getElementById('netChangeValue'),
        alertEl: document.getElementById('netChangeAlert'),
        resultContainer: document.getElementById('resultContainer'),
        reportDate: document.getElementById('reportDate'),
        reportDateTime: document.getElementById('reportDateTime')
      };

      // Check for missing elements
      for (let key in resultElements) {
        if (!resultElements[key]) {
          console.error('Missing element:', key);
        }
      }

      // Populate results
      if (resultElements.resultOpening) resultElements.resultOpening.textContent = openingCapital.toFixed(2);
      if (resultElements.resultCredit) resultElements.resultCredit.textContent = credit.toFixed(2);
      if (resultElements.resultDebit) resultElements.resultDebit.textContent = debit.toFixed(2);
      if (resultElements.resultClosing) resultElements.resultClosing.textContent = closingCapital.toFixed(2);
      
      // Get mobile banking total
      const netCashFlow = netChange;
      
      if (resultElements.detailOpening) resultElements.detailOpening.textContent = openingCapital.toFixed(2);
      if (resultElements.detailMobileTotal) resultElements.detailMobileTotal.textContent = mobileBankingTotal.toFixed(2);
      if (resultElements.detailCredit) resultElements.detailCredit.textContent = credit.toFixed(2);
      if (resultElements.detailDebit) resultElements.detailDebit.textContent = debit.toFixed(2);
      if (resultElements.detailNetCash) resultElements.detailNetCash.textContent = netCashFlow.toFixed(2);
      if (resultElements.detailCurrentAsset) resultElements.detailCurrentAsset.textContent = currentAsset.toFixed(2);
      if (resultElements.detailClosing) resultElements.detailClosing.textContent = closingCapital.toFixed(2);
      
      const netChangeText = netChange >= 0 ? '+৳ ' + netChange.toFixed(2) : '-৳ ' + Math.abs(netChange).toFixed(2);
      if (resultElements.netChangeValue) resultElements.netChangeValue.textContent = netChangeText;

      // Update alert color based on net change
      if (resultElements.alertEl) {
        resultElements.alertEl.className = 'alert ' + (netChange >= 0 ? 'alert-success' : 'alert-danger');
      }

      // Set dates
      const now = new Date();
      if (resultElements.reportDate) resultElements.reportDate.textContent = formatDate(now);
      if (resultElements.reportDateTime) resultElements.reportDateTime.textContent = formatDateTime(now);

      // Show results
      if (resultElements.resultContainer) {
        resultElements.resultContainer.style.display = 'block';

        // Scroll to results
        setTimeout(() => {
          resultElements.resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
      }
    };
  }

  // Try to init immediately if DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalculator);
  } else {
    initCalculator();
  }
})();
</script>
@endsection
