@extends('include')
@section('backTitle')
Bank Transaction
@endsection
@section('bodyTitleFrist')
   Transaction Creation
@endsection
@section('bodyTitleEnd')
   <a href="{{route('bankTransactionList')}}"> Transaction List</a>
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
    <div class="col-md-4">
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
                                <option
                                    value="1"
                                    data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/m-card.png"
                                >
                                    xx25
                                </option>
                                <option
                                    value="2"
                                    data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/ame-bank.png"
                                >
                                    xx56
                                </option>
                            </select>
                        </form>
                    </div>

                    <h4 class="my-2 fs-24 fw-semibold">122.5692.00 <small class="font-14">BTC</small></h4>
                    <p class="mb-3 text-muted fw-semibold">
                        <span class="text-success"><i class="fas fa-arrow-up me-1"></i>11.1%</span> Outstanding balance
                        boost
                    </p>
                    <button type="submit" class="btn btn-soft-primary">Transfer</button>
                    <button type="button" class="btn btn-soft-danger">Request</button>
                </div>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Recent Transactions</h4>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end card-header-->
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <tr class="">
                                <td class="px-0">
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="thumb-md justify-content-center d-flex align-items-center bg-success-subtle text-success rounded-circle me-2"
                                            >SF</span
                                        >
                                        <div class="flex-grow-1 text-truncate">
                                            <h6 class="m-0 text-truncate">Service Fee</h6>
                                            <p class="font-12 mb-0 text-muted">20 August 2024</p>
                                        </div>
                                        <!--end media body-->
                                    </div>
                                    <!--end media-->
                                </td>
                                <td class="px-0 text-end">
                                    <span class="text-primary ps-2 align-self-center text-end">$3325.00</span>
                                </td>
                            </tr>
                            <!--end tr-->
                            <tr class="">
                                <td class="px-0">
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="thumb-md justify-content-center d-flex align-items-center bg-warning-subtle text-warning rounded-circle me-2"
                                            >UI</span
                                        >
                                        <div class="flex-grow-1 text-truncate">
                                            <h6 class="m-0 text-truncate">UI/UX Project</h6>
                                            <p class="font-12 mb-0 text-muted">15 August 2024</p>
                                        </div>
                                        <!--end media body-->
                                    </div>
                                    <!--end media-->
                                </td>
                                <td class="px-0 text-end">
                                    <span class="text-primary ps-2 align-self-center text-end">$2548.00</span>
                                </td>
                            </tr>
                            <!--end tr-->
                            <tr class="">
                                <td class="px-0">
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="thumb-md justify-content-center d-flex align-items-center bg-purple-subtle text-purple rounded-circle me-2"
                                            >FF</span
                                        >
                                        <div class="flex-grow-1 text-truncate">
                                            <h6 class="m-0 text-truncate">Freelancer Fee</h6>
                                            <p class="font-12 mb-0 text-muted">12 August 2024</p>
                                        </div>
                                        <!--end media body-->
                                    </div>
                                    <!--end media-->
                                </td>
                                <td class="px-0 text-end">
                                    <span class="text-primary ps-2 align-self-center text-end">$2985.00</span>
                                </td>
                            </tr>
                            <!--end tr-->
                            <tr class="">
                                <td class="px-0">
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="thumb-md justify-content-center d-flex align-items-center bg-danger-subtle text-danger rounded-circle me-2"
                                            >MS</span
                                        >
                                        <div class="flex-grow-1 text-truncate">
                                            <h6 class="m-0 text-truncate">Monthly SIP Plan</h6>
                                            <p class="font-12 mb-0 text-muted">08 August 2024</p>
                                        </div>
                                        <!--end media body-->
                                    </div>
                                    <!--end media-->
                                </td>
                                <td class="px-0 text-end">
                                    <span class="text-primary ps-2 align-self-center text-end">$1845.00</span>
                                </td>
                            </tr>
                            <!--end tr-->
                        </tbody>
                    </table>
                    <!--end table-->
                </div>
                <!--end /div-->
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
    <div class="col-md-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <form class="" method="POST" action="{{ route('saveBankTransaction') }}">
                    @csrf
                    <input type="hidden" name="itemId" value="{{ !empty($transaction) ? $transaction->id : '' }}">
                    <div class="row">
                        @php
                            $bankAccount = App\Models\bankAccount::all();
                        @endphp
                        <div class="col-6 mb-2">
                            <label for="accountId">Account Name</label>
                            <select class="form-select" id="accountId" name="accountId" required>
                                @if(!empty($transaction))
                                <option value="{{ $transaction->bank_account_id }}">{{ $transaction->account_name }} - {{ $transaction->account_number }}</option>
                                @else
                                <option value="">-- Select --</option>
                                @endif
                                @if(!empty($bankAccount) && $bankAccount->count() > 0)
                                @foreach($bankAccount as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} - {{ $account->account_number }}</option>
                                @endforeach
                                @else
                                <option value="">No Account Found</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="date">Date</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    id="date"
                                    required
                                    name="date"
                                    value="{{ !empty($transaction) ? $transaction->transaction_date : '' }}"
                                />
                            </div>
                        </div>
                    </div>
                    <!--end row-->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="type">Transaction Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    @if(!empty($transaction))
                                    <option value="{{ $transaction->transaction_type }}">{{ $transaction->transaction_type }}</option>
                                    @else
                                    <option>-- Select --</option>
                                    @endif
                                    <option value="Debit">Debit</option>
                                    <option value="Credit">Credit</option>
                                </select>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="amount">Amount</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="aApprox"
                                    required
                                    placeholder="00.00"
                                    name="amount"
                                    value="{{ !empty($transaction) ? $transaction->amount : '' }}"
                                />
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label" for="description">Description</label>
                                <textarea
                                    class="form-control"
                                    rows="2"
                                    id="description"
                                    name="description"
                                    placeholder="Enter Description"
                                >{{ !empty($transaction) ? $transaction->description : '' }}</textarea>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <div class="row">
                        <div class="col-sm-12 text-start">
                            <button type="submit" class="btn btn-primary px-4">
                                @if(!empty($transaction)) Update Data @else Save Data @endif
                            </button>
                            <a href="{{ route('transactionList') }}" class="btn btn-danger px-4">Cancel</a>
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