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

  <div class="card mt-4" id="dailyRecordsSection">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Daily Debit/Credit Records</h5>
      <div class="d-flex gap-2 d-print-none">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printDailyRecords()">
          <i class="bx bx-printer me-1"></i> Print
        </button>
      </div>
    </div>
    <div class="card-body">
      <p class="text-muted mb-3">Add individual debit (money out) and credit (money in) transactions for today.</p>
      
      <!-- Add New Record Form -->
      <form method="POST" action="{{ route('mobile.cashRecords.add') }}" class="row g-3 mb-4 p-3 bg-light rounded d-print-none">
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
          <div class="input-group">
            <select name="transaction_detail_id" class="form-select" id="descriptionSelect">
              <option value="">-- Select or Create New --</option>
              @foreach($transactionDetails as $detail)
                <option value="{{ $detail->id }}" @if(old('transaction_detail_id') == $detail->id) selected @endif>{{ $detail->name }}</option>
              @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newDetailModal" title="Add new description">
              +
            </button>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageDetailsModal" title="Manage descriptions">
              ⚙
            </button>
          </div>
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
              <th class="text-end d-print-none">Actions</th>
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
                <td>{{ $record->detail_name ?? $record->description ?? '—' }}</td>
                <td>{{ $record->reference_no ?? '—' }}</td>
                <td class="text-end d-print-none">
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
                          <select name="transaction_detail_id" class="form-select">
                            <option value="">-- Select Description --</option>
                            @foreach($transactionDetails as $detail)
                              <option value="{{ $detail->id }}" @if($record->transaction_detail_id == $detail->id) selected @endif>{{ $detail->name }}</option>
                            @endforeach
                          </select>
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

  <!-- Modal for Creating New Transaction Detail -->
  <div class="modal fade" id="newDetailModal" tabindex="-1" aria-labelledby="newDetailLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="newDetailLabel">Create New Transaction Description</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="newDetailForm">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Description Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g., Cash Deposit, Check Payment" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="createNewDetail()">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal for Managing Transaction Details -->
  <div class="modal fade" id="manageDetailsModal" tabindex="-1" aria-labelledby="manageDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="manageDetailsLabel">Manage Transaction Descriptions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Description Name</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="detailsTableBody">
                @foreach($transactionDetails as $detail)
                  <tr id="detail-row-{{ $detail->id }}">
                    <td>{{ $detail->name }}</td>
                    <td class="text-end">
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="editDetail({{ $detail->id }}, '{{ addslashes($detail->name) }}')">
                        Edit
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDetail({{ $detail->id }}, '{{ addslashes($detail->name) }}')">
                        Delete
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Editing Transaction Detail -->
  <div class="modal fade" id="editDetailModal" tabindex="-1" aria-labelledby="editDetailLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editDetailLabel">Edit Transaction Description</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editDetailForm">
          @csrf
          <input type="hidden" id="editDetailId" name="id">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Description Name</label>
              <input type="text" id="editDetailName" name="name" class="form-control" placeholder="e.g., Cash Deposit, Check Payment" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="updateDetail()">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal for Delete Confirmation -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this transaction description?</p>
          <p class="fw-bold" id="deleteDetailName"></p>
          <p class="text-muted small">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDelete()">Delete</button>
        </div>
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
                        <td class="text-muted small">{{ $record->detail_name ?? $record->description ?? '—' }}</td>
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

<style>
@media print {
  /* Page setup */
  @page {
    size: A4;
    margin: 1cm;
  }
  
  /* Reset and hide everything */
  * {
    overflow: visible !important;
  }
  
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
  }
  
  body * {
    visibility: hidden;
    max-height: 0;
    overflow: hidden;
  }
  
  /* Show and position only report content */
  #reportContent {
    visibility: visible !important;
    max-height: none !important;
    overflow: visible !important;
    position: fixed !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: auto !important;
    padding: 10px !important;
    margin: 0 !important;
    z-index: 9999 !important;
  }
  
  #reportContent * {
    visibility: visible !important;
    max-height: none !important;
    overflow: visible !important;
  }
  
  /* Hide buttons inside report */
  #reportContent .btn,
  #reportContent button {
    display: none !important;
  }
  
  /* Hide DataTables pagination and info for simple-datatables */
  .dataTable-info,
  .dataTable-pagination,
  .dataTable-top,
  .dataTable-bottom,
  .dataTable-dropdown,
  .dataTable-search,
  .dataTables_info,
  .dataTables_paginate,
  .dataTables_length,
  .dataTables_filter,
  .dataTables_wrapper .row:first-child,
  .dataTables_wrapper .row:last-child,
  #reportContent .dataTable-info,
  #reportContent .dataTable-pagination,
  #reportContent .dataTable-top,
  #reportContent .dataTable-bottom {
    display: none !important;
    visibility: hidden !important;
  }
  
  /* Compact spacing */
  #reportContent h4 {
    font-size: 16px;
    margin-bottom: 4px;
  }
  
  #reportContent h5 {
    font-size: 13px;
    margin-bottom: 4px;
  }
  
  #reportContent h6 {
    font-size: 12px;
    margin-bottom: 4px;
  }
  
  #reportContent p {
    font-size: 11px;
    margin-bottom: 3px;
  }
  
  #reportContent .small {
    font-size: 10px;
  }
  
  #reportContent .text-center {
    margin-bottom: 8px;
    padding-bottom: 4px;
  }
  
  #reportContent .border-bottom {
    padding-bottom: 6px;
    margin-bottom: 8px;
  }
  
  #reportContent .border-top {
    padding-top: 6px;
    margin-top: 8px;
  }
  
  #reportContent .row {
    margin-bottom: 8px;
  }
  
  #reportContent .mb-1 { margin-bottom: 4px !important; }
  #reportContent .mb-2 { margin-bottom: 6px !important; }
  #reportContent .mb-3 { margin-bottom: 8px !important; }
  #reportContent .mb-4 { margin-bottom: 10px !important; }
  
  #reportContent .p-3 {
    padding: 6px !important;
  }
  
  #reportContent .p-4 {
    padding: 8px !important;
  }
  
  #reportContent .pb-3 {
    padding-bottom: 6px !important;
  }
  
  #reportContent .pt-3 {
    padding-top: 6px !important;
  }
  
  /* Tables */
  #reportContent table {
    font-size: 10px;
    margin-bottom: 8px;
  }
  
  #reportContent th,
  #reportContent td {
    padding: 3px 5px !important;
  }
  
  /* Preserve colors */
  * {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
  
  .bg-gradient {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%) !important;
  }
  
  .bg-light {
    background-color: #f8f9fa !important;
  }
  
  .bg-success {
    background-color: #198754 !important;
    color: white !important;
  }
  
  .bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
  }
  
  .alert-success {
    background-color: #d1e7dd !important;
    color: #0f5132 !important;
    border-color: #badbcc !important;
  }
  
  .alert-danger {
    background-color: #f8d7da !important;
    color: #842029 !important;
    border-color: #f5c2c7 !important;
  }
  
  .badge {
    padding: 2px 5px;
    font-size: 9px;
  }
  
  /* Card styling */
  .card {
    border: 1px solid #dee2e6;
    margin-bottom: 8px;
  }
  
  .card-body {
    padding: 8px;
  }
}
</style>

<script>
function printDailyRecords() {
  const section = document.getElementById('dailyRecordsSection');
  if (!section) {
    alert('Daily Debit/Credit section not found.');
    return;
  }

  // Clone the section so we can strip controls without touching the live DOM
  const clone = section.cloneNode(true);
  clone.querySelectorAll('.d-print-none, .btn').forEach(el => el.remove());

  // Inline minimal styling to avoid blank pages if external CSS fails to load
  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Daily Debit/Credit Records</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <style>
        body { margin: 16px; font-family: Arial, sans-serif; }
        @page { size: auto; margin: 10mm; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        .card { page-break-inside: avoid; }
      </style>
    </head>
    <body>
      ${clone.outerHTML}
    </body>
    </html>
  `;

  const printWindow = window.open('', '_blank', 'width=900,height=700');
  if (!printWindow) {
    alert('Please allow pop-ups to print.');
    return;
  }

  printWindow.document.open();
  printWindow.document.write(html);
  printWindow.document.close();
  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
}

function printReport() {
  // Update the report date and time before printing
  const now = new Date();
  const reportDate = document.getElementById('reportDate');
  const reportDateTime = document.getElementById('reportDateTime');
  
  if (reportDate) reportDate.textContent = formatDate(now);
  if (reportDateTime) reportDateTime.textContent = formatDateTime(now);
  
  // Use native window.print()
  window.print();
}

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
        body { 
          margin: 40px; 
          font-family: Arial, sans-serif;
          background-color: white;
        }
        .bg-gradient { 
          background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%) !important;
          print-color-adjust: exact;
          -webkit-print-color-adjust: exact;
        }
        .border-2 { border-width: 2px !important; }
        .card-header {
          background-color: #0d6efd !important;
          color: white !important;
          padding: 15px;
        }
        .text-success { color: #198754 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-primary { color: #0d6efd !important; }
        .text-info { color: #0dcaf0 !important; }
        .badge { 
          padding: 0.25rem 0.5rem;
          border-radius: 0.25rem;
        }
        .bg-success { background-color: #198754 !important; color: white; }
        .bg-danger { background-color: #dc3545 !important; color: white; }
        .bg-light { background-color: #f8f9fa !important; }
        .alert-success { 
          background-color: #d1e7dd !important; 
          color: #0f5132 !important;
          border: 1px solid #badbcc;
          padding: 10px;
          border-radius: 5px;
        }
        .alert-danger { 
          background-color: #f8d7da !important; 
          color: #842029 !important;
          border: 1px solid #f5c2c7;
          padding: 10px;
          border-radius: 5px;
        }
        .btn { display: none; }
        @media print {
          body { margin: 20px; }
        }
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

function createNewDetail() {
  const form = document.getElementById('newDetailForm');
  const formData = new FormData(form);
  
  fetch("{{ route('mobile.createTransactionDetail') }}", {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Add new option to select dropdown
      const select = document.getElementById('descriptionSelect');
      const option = document.createElement('option');
      option.value = data.id;
      option.textContent = data.name;
      option.selected = true;
      select.appendChild(option);
      
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('newDetailModal'));
      modal.hide();
      
      // Reset form
      form.reset();
      
      // Show success message
      showAlert('success', 'Transaction description created successfully!');
    } else {
      showAlert('danger', data.message || 'Failed to create transaction description.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('danger', 'An error occurred while creating the transaction description.');
  });
}

function editDetail(id, name) {
  document.getElementById('editDetailId').value = id;
  document.getElementById('editDetailName').value = name;
  
  const editModal = new bootstrap.Modal(document.getElementById('editDetailModal'));
  editModal.show();
}

function updateDetail() {
  const form = document.getElementById('editDetailForm');
  const formData = new FormData(form);
  
  fetch("{{ route('mobile.updateTransactionDetail') }}", {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Update the dropdown
      const select = document.getElementById('descriptionSelect');
      const option = select.querySelector(`option[value="${data.id}"]`);
      if (option) {
        option.textContent = data.name;
      }
      
      // Update the manage table row
      const row = document.getElementById(`detail-row-${data.id}`);
      if (row) {
        row.querySelector('td:first-child').textContent = data.name;
      }
      
      // Close edit modal
      const editModal = bootstrap.Modal.getInstance(document.getElementById('editDetailModal'));
      editModal.hide();
      
      // Show success message
      showAlert('success', 'Transaction description updated successfully!');
      
      // Reload page to refresh all dropdowns
      setTimeout(() => {
        location.reload();
      }, 1000);
    } else {
      showAlert('danger', data.message || 'Failed to update transaction description.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('danger', 'An error occurred while updating the transaction description.');
  });
}

let deleteDetailId = null;
let deleteDetailName = null;

function deleteDetail(id, name) {
  deleteDetailId = id;
  deleteDetailName = name;
  
  document.getElementById('deleteDetailName').textContent = name;
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  deleteModal.show();
}

function confirmDelete() {
  if (!deleteDetailId) return;
  
  const csrfToken = document.querySelector('input[name="_token"]').value;
  
  fetch("{{ url('mobile-banking/transaction-details') }}/" + deleteDetailId, {
    method: 'DELETE',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrfToken,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Remove from dropdown
      const select = document.getElementById('descriptionSelect');
      const option = select.querySelector(`option[value="${deleteDetailId}"]`);
      if (option) {
        option.remove();
      }
      
      // Remove from manage table
      const row = document.getElementById(`detail-row-${deleteDetailId}`);
      if (row) {
        row.remove();
      }
      
      // Close delete modal
      const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
      deleteModal.hide();
      
      // Show success message
      showAlert('success', 'Transaction description deleted successfully!');
      
      deleteDetailId = null;
      deleteDetailName = null;
    } else {
      // Close delete modal
      const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
      deleteModal.hide();
      
      showAlert('danger', data.message || 'Failed to delete transaction description.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    
    // Close delete modal
    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
    if (deleteModal) deleteModal.hide();
    
    showAlert('danger', 'An error occurred while deleting the transaction description.');
  });
}

function showAlert(type, message) {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.setAttribute('role', 'alert');
  alertDiv.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;
  
  const container = document.querySelector('.card-body');
  if (container) {
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 4 seconds
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alertDiv);
      bsAlert.close();
    }, 4000);
  }
}
</script>
@endsection
