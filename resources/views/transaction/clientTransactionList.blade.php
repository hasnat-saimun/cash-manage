@extends('include')
@section('backTitle')
Client Transaction List
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = Array.from(document.querySelectorAll('.txn-checkbox'));
    const bulkBtn = document.getElementById('bulk-delete-btn');
    const form = document.getElementById('bulk-delete-form');

    function refreshButton() {
        const anyChecked = checkboxes.some(cb => cb.checked);
        bulkBtn.disabled = !anyChecked;
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
            refreshButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked && selectAll && selectAll.checked) {
                selectAll.checked = false;
            }
            refreshButton();
        @extends('include')
        @section('backTitle')
        Client Transaction List
        @endsection
        @section('bodyTitleFrist')
           Transaction List
        @endsection
        @section('bodyTitleEnd')
           <a href="{{route('transactionCreation')}}"> Transaction Creation</a>
        @endsection

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all');
            const checkboxes = Array.from(document.querySelectorAll('.txn-checkbox'));
            const bulkBtn = document.getElementById('bulk-delete-btn');
            const form = document.getElementById('bulk-delete-form');

            function refreshButton() {
                const anyChecked = checkboxes.some(cb => cb.checked);
                if (bulkBtn) bulkBtn.disabled = !anyChecked;
            }

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
                    refreshButton();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    if (!cb.checked && selectAll && selectAll.checked) {
                        selectAll.checked = false;
                    }
                    refreshButton();
                });
            });

            if (form) {
                form.addEventListener('submit', function (e) {
                    if (bulkBtn && bulkBtn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    const confirmed = confirm('Delete selected transactions? Balances will be adjusted.');
                    if (!confirmed) e.preventDefault();
                });
            }
        });
        </script>
        @endpush

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
        <div class="d-flex justify-content-end mb-2 no-print">
            <button id="printTransactions" type="button" class="btn btn-outline-secondary">Print</button>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-4">
                <div class="card bg-globe-img">
                    <div class="card-body">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-16 fw-semibold">Balance</span>
                                <form class="">
                                    <select
                                        id="dynamic-select"
                                        name="example-select"
                                        data-placeholder="Select an option"
                                        data-dynamic-select
                                    >
                                        <option value="1" data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/m-card.png" selected>xx25</option>
                                        <option value="2" data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/ame-bank.png">xx56</option>
                                    </select>
                                </form>
                            </div>

                            <h4 class="my-2 fs-24 fw-semibold">{{ number_format($outstandingBalance ?? 0, 2) }} <small class="font-14">BDT</small></h4>
                            <p class="mb-3 text-muted fw-semibold">
                                Outstanding total client balance
                            </p>
                            <button type="submit" class="btn btn-soft-primary">Transfer</button>
                            <button type="button" class="btn btn-soft-danger">Request</button>
                        </div>
                    </div>
                    <!--end card-body-->
                </div>
                <!--end card-->
            </div>
            <!--end col-->
            <div class="col-md-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-12 col-lg-4">
                                <div id="customers" class="apex-charts"></div>
                            </div>
                            <!--end col-->
                            <div class="col-12 col-lg-8">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col-9">
                                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Daily Net</p>
                                                        <h4 class="mt-1 mb-0 fw-medium">{{ number_format($dailyNet ?? 0, 2) }}</h4>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-3 align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-md rounded mx-auto"
                                                        >
                                                            <i
                                                                class="iconoir-dollar-circle fs-22 align-self-center mb-0 text-muted opacity-50"
                                                            ></i>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </div>
                                            <!--end card-body-->
                                        </div>
                                        <!--end card-->
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col-9">
                                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Weekly Net</p>
                                                        <h4 class="mt-1 mb-0 fw-medium">{{ number_format($weeklyNet ?? 0, 2) }}</h4>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-3 align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-md rounded mx-auto"
                                                        >
                                                            <i
                                                                class="iconoir-calendar fs-22 align-self-center mb-0 text-muted opacity-50"
                                                            ></i>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </div>
                                            <!--end card-body-->
                                        </div>
                                        <!--end card-->
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row d-flex justify-content-center">
                                                    <div class="col-9">
                                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Monthly Net</p>
                                                        <h4 class="mt-1 mb-0 fw-medium">{{ number_format($monthlyNet ?? 0, 2) }}</h4>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-3 align-self-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center thumb-md rounded mx-auto"
                                                        >
                                                            <i
                                                                class="iconoir-stats-report fs-22 align-self-center mb-0 text-muted opacity-50"
                                                            ></i>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </div>
                                            <!--end card-body-->
                                        </div>
                                        <!--end card-->
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                                @if(!empty($lastTxn))
                                <p class="mb-0 bg-soft-success rounded p-2">
                                    Last transaction: <span class="fw-bold text-dark">{{ number_format($lastTxn->amount, 2) }}</span>
                                    <span class="ms-1">({{ ucfirst(strtolower($lastTxn->type)) }})</span>
                                    on <span>{{ $lastTxn->date }}</span>
                                </p>
                                @endif
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-body-->
                </div>
                <!--end card-->
            </div>
            <!--end col-->
        </div>
        <!--end row-->

        <div class="row justify-content-center" id="transactions-print-section">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Transactions</h4>
                            </div>
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-header-->
                    <div class="card-body pt-0">
                        <!-- Print-only summary header -->
                        <div class="print-only mb-3">
                            <h5 class="mb-2">Client Transactions Summary</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <tbody>
                                        <tr>
                                            <th style="width:220px">Outstanding Balance</th>
                                            <td class="text-end">{{ number_format($outstandingBalance ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Daily Net</th>
                                            <td class="text-end">{{ number_format($dailyNet ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Weekly Net</th>
                                            <td class="text-end">{{ number_format($weeklyNet ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Monthly Net</th>
                                            <td class="text-end">{{ number_format($monthlyNet ?? 0, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <form id="bulk-delete-form" method="POST" action="{{ route('transactions.bulkDelete') }}">
                                @csrf
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <button type="submit" class="btn btn-danger btn-sm" id="bulk-delete-btn" disabled>Delete Selected</button>
                                    <small class="text-muted">Select rows to delete; balances auto-adjust.</small>
                                </div>
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-top-0"><input type="checkbox" id="select-all"></th>
                                            <th class="border-top-0">SL</th>
                                            <th class="border-top-0">Client</th>
                                            <th class="border-top-0">Source</th>
                                            <th class="border-top-0">Transaction Type</th>
                                            <th class="border-top-0">Amount</th>
                                            <th class="border-top-0">Date</th>
                                            <th class="border-top-0">Description</th>
                                            <th class="border-top-0">Action</th>
                                        </tr>
                                        <!--end tr-->
                                    </thead>
                                    <tbody>
                                        @php $__sourceCache = []; @endphp
                                        @if(!$transactions->isEmpty())
                                        @foreach ($transactions as $transaction)
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="{{ $transaction->id }}" class="txn-checkbox"></td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $transaction->client_name }}</td>               
                                            @php
                                                $sourceName = '';
                                                $srcId = $transaction->transaction_source ?? $transaction->source ?? null;
                                                if ($srcId) {
                                                    if (isset($__sourceCache[$srcId])) {
                                                        $sourceName = $__sourceCache[$srcId];
                                                    } else {
                                                        $s = \App\Models\source::find($srcId);
                                                        $sourceName = $s?->source_name ?? '';
                                                        $__sourceCache[$srcId] = $sourceName;
                                                    }
                                                }
                                            @endphp
                                            <td>{{ $sourceName ?: '-' }}</td>
                                            @if($transaction->type == 'Debit')
                                            <td>
                                                <span class="badge bg-success-subtle text-danger fs-11 fw-medium px-2"
                                                    >Debit</span
                                                >
                                            </td>
                                            @elseif($transaction->type == 'Credit')
                                            <td>
                                                <span class="badge bg-danger-subtle text-success fs-11 fw-medium px-2"
                                                    >Credit</span
                                                >
                                            </td>
                                            @else
                                            <td>
                                               -
                                            </td>
                                            @endif
                                            <td>{{ $transaction->amount }}</td>
                                            <td>{{ $transaction->date }}</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>
                                                <a href="{{ route('transactionEdit',['id'=>$transaction->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                                <a href="{{ route('deleteTransaction',['id'=>$transaction->id]) }}" data-confirm-delete data-confirm-message="Delete this transaction?">
                                                    <i class="las la-trash-alt text-secondary fs-18"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <!--end tr-->
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No transactions found.</td>
                                        </tr>
                                        @endif
                                        <!--end tr-->
                                    </tbody>
                                </table>
                            </form>
                            <!--end table-->
                        </div>
                        <!--end /div-->
                        <div class="d-lg-flex justify-content-lg-between mt-2">
                            <div class="mb-2 mb-lg-0">
                                <a href="{{route('transactionCreation')}}" type="button" class="btn btn-primary px-4">Add Transaction</a>
                            </div>
                            <div>
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                                <!--end pagination-->
                            </div>
                        </div>
                    </div>
                    <!--end card-body-->
                </div>
                <!--end card-->
            </div>
            <!--end col-->
        </div>
        <!--end row-->

        @endsection