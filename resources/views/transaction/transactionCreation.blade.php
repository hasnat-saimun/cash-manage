 @extends('include')
@section('backTitle')
New Transaction
@endsection
@section('bodyTitle')
New Transaction
@endsection
@section('bodyContent')

<div class="row">
    <div class="col-12">
        @if(session()->has('success'))
            <div class="alert alert-success w-100 rounded-0">
                {{ session()->get('success') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger w-100 rounded-0">
                {{ session()->get('error') }}
            </div>
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
                <form class="" method="POST" action="{{ route('saveTransaction') }}">
                    @csrf
                    <div class="row">
                        @if(!$clients->isEmpty())
                            <div class="mb-3">
                                <label class="form-label" for="accNum">Account Number</label>   
                                <select class="form-select" id="accNum" name="" required>
                                    <option value="">-- Select --</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->client_acNum }} - {{ $client->client_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label" for="Card">Account Number</label>
                            <select class="form-select" id="Card">
                                <option>-- Select --</option>
                                <option  >Credit</option>
                                <option>Debit</option>
                            </select>
                        </div>
                        @endif
                
                    </div>
                    <!--end row-->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="Card">Type</label>
                                <select class="form-select" id="Card">
                                    <option>-- Select --</option>
                                    <option>Credit</option>
                                    <option>Debit</option>
                                </select>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="aApprox">Amount</label>
                                <input type="number" class="form-control" id="aApprox" required="" placeholder="00.00" />
                            </div>
                        </div>
                        <!--end col-->
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="aApprox">Date</label>
                                <input type="date" class="form-control" id="aApprox" required="" placeholder="00.00" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            
                            <div class="mb-3">
                                <label class="form-label" for="description">Description</label>
                                <textarea
                                    class="form-control"
                                    rows="2"
                                    id="description"
                                    placeholder="Enter Description"
                                ></textarea>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <!--end row-->
                    <div class="row">
                        <div class="col-sm-12 text-start">
                            <button type="submit" class="btn btn-primary px-4">Entry Now</button>
                            <button type="submit" class="btn btn-danger px-4">Cancle</button>
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