 @extends('include')
@section('backTitle')
Bank Transaction List
@endsection
@section('bodyTitleFrist')
   Transaction List
@endsection
@section('bodyTitleEnd')
   <a href="{{route('bankTransactionCreation')}}"> Transaction Creation</a>
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
                                <option value="1" data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/m-card.png">xx25</option>
                                <option value="2" data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/ame-bank.png">xx56</option>
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
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Daily</p>
                                                <h4 class="mt-1 mb-0 fw-medium">$76.50</h4>
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
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Weekly</p>
                                                <h4 class="mt-1 mb-0 fw-medium">$852.50</h4>
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
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Monthly</p>
                                                <h4 class="mt-1 mb-0 fw-medium">$8542.50</h4>
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
                        <p class="mb-0 text-success bg-soft-success rounded p-2">
                            The last transaction <span class="fw-bold text-dark">$2560.00</span> is Successful!
                        </p>
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

<div class="row justify-content-center">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">All Transactions</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <div class="dropdown">
                            <a
                                href="#"
                                class="btn bt btn-light dropdown-toggle"
                                data-bs-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <i class="icofont-calendar fs-5 me-1"></i> This Month<i
                                    class="las la-angle-down ms-1"
                                ></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Today</a>
                                <a class="dropdown-item" href="#">Last Week</a>
                                <a class="dropdown-item" href="#">Last Month</a>
                                <a class="dropdown-item" href="#">This Year</a>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end card-header-->
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-top-0">Account Name</th>
                                <th class="border-top-0">Account Number</th>
                                <th class="border-top-0">Transaction Type</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Date</th>
                                <th class="border-top-0">Description</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            <!--end tr-->
                        </thead>
                        <tbody>
                            @if(!empty($transactions) && $transactions->count())
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->account_name}}</td>
                                <td>{{ $transaction->account_number}}</td>
                                @if($transaction->transaction_type == 'Debit')
                                <td>
                                    <span class="badge bg-success-subtle text-danger fs-11 fw-medium px-2"
                                        >Debit</span
                                    >
                                </td>
                                @elseif($transaction->transaction_type == 'Credit')
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
                                <td>{{ $transaction->transaction_date }}</td>
                                <td>{{ $transaction->description }}</td>
                                <td>
                                    <a href="#"><i class="las la-print text-secondary fs-18"></i></a>
                                    <a href="{{ route('bankTransactionEdit',['id'=>$transaction->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                    <a href="{{ route('deleteTransaction',['id'=>$transaction->id]) }}"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <!--end tr-->
                            <tr>
                                <td>15 July 2024 <span>012:35pm</span></td>
                                <td>Card Payment</td>
                                <td>UI/UX Project</td>
                                <td>$700</td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger fs-11 fw-medium px-2">Debit</span>
                                </td>
                                <td>
                                    <a href="#"><i class="las la-print text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-download text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                </td>
                            </tr>
                            @endif
                            <!--end tr-->
                        </tbody>
                    </table>
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