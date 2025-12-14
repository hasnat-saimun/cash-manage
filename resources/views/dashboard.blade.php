@extends('include')
@section('backTitle') Dashboard @endsection
@section('bodyTitleFrist') Welcome @endsection
@section('bodyContent')
 <div class="container-fluid">
                @php
                    $businessId = session('business_id');
                    $business = auth()->user() ? auth()->user()->businesses()->when($businessId, fn($q) => $q->where('business_id', $businessId))->first() : null;
                    $businessName = $business?->name ?? config('app.name');

                    // Scoped via global scope on clientCreation
                    $clientCount = \App\Models\clientCreation::query()->count();

                    // Transactions scoped via global scope
                    $txnCount = \App\Models\transaction::query()->count();

                    // client_balances doesn't have business_id, so join to client_creations
                    $activeClients = \App\Models\clientBalance::query()
                        ->join('client_creations', 'client_creations.id', '=', 'client_balances.client_id')
                        ->when($businessId, fn($q) => $q->where('client_creations.business_id', $businessId))
                        ->where('client_balances.balance', '>', 0)
                        ->count();

                    $totalBalance = \App\Models\clientBalance::query()
                        ->join('client_creations', 'client_creations.id', '=', 'client_balances.client_id')
                        ->when($businessId, fn($q) => $q->where('client_creations.business_id', $businessId))
                        ->sum('client_balances.balance');

                    // Monthly window and metrics (safe defaults)
                    $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->toDateString();
                    $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->toDateString();
                    $monthlyTxns = \App\Models\transaction::query()
                        ->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->get();
                    $incomeSum = 0; $expenseSum = 0; $otherSum = 0; $incomePct = 0; $expensePct = 0; $otherPct = 0; $txnsThisMonth = 0; $avgOrderValue = 0; $newClientsThisMonth = 0;
                    if ($monthlyTxns && $monthlyTxns->count() > 0) {
                        $incomeSum = (clone $monthlyTxns)->where('type', 'credit')->sum('amount') ?? 0;
                        $expenseSum = (clone $monthlyTxns)->where('type', 'debit')->sum('amount') ?? 0;
                        $otherSum = max(0, (($monthlyTxns->sum('amount') ?? 0) - ($incomeSum + $expenseSum)));
                        $totalSum = max(1, $incomeSum + $expenseSum + $otherSum);
                        $incomePct = round(($incomeSum / $totalSum) * 100);
                        $expensePct = round(($expenseSum / $totalSum) * 100);
                        $otherPct = 100 - $incomePct - $expensePct;
                        $txnsThisMonth = $monthlyTxns->count();
                        $avgOrderValue = round($monthlyTxns->avg('amount') ?? 0, 2);
                    }
                    $newClientsThisMonth = \App\Models\clientCreation::query()
                        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->count();
                    // Recent transactions and total count for table activation
                    $recentCount = \App\Models\transaction::query()->count();
                    $recentTxns = \App\Models\transaction::query()->orderByDesc('id')->limit(50)->get();

                    // Build lookup maps from domain tables (scoped by business via global scopes)
                    // Lookup maps keyed by IDs (id => name)
                    $clientNamesById = \App\Models\clientCreation::query()
                        ->whereNotNull('client_name')
                        ->pluck('client_name', 'id')
                        ->toArray();
                    $sourceNamesById = \App\Models\source::query()
                        ->whereNotNull('source_name')
                        ->pluck('source_name', 'id')
                        ->toArray();
                @endphp
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="fw-semibold mb-1">{{ $businessName }}</h3>
                                    <p class="text-muted mb-0">Business overview and key metrics</p>
                                </div>
                                <div class="d-flex gap-3">
                                    <div class="text-end">
                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Clients</p>
                                        <h5 class="mt-1 mb-0 fw-medium">{{ number_format($clientCount) }}</h5>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Active</p>
                                        <h5 class="mt-1 mb-0 fw-medium">{{ number_format($activeClients) }}</h5>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Transactions</p>
                                        <h5 class="mt-1 mb-0 fw-medium">{{ number_format($txnCount) }}</h5>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Total Balance</p>
                                        <h5 class="mt-1 mb-0 fw-medium">@currency($totalBalance)</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card  bg-welcome-img overflow-hidden">
                                    <div class="card-body">
                                        <div class="">                                            
                                            <h3 class="text-white fw-semibold fs-20 lh-base">Upgrade you plan for
                                            <br>Great experience</h3>
                                            <a href="#" class="btn btn-sm btn-danger">Upgarde Now</a>
                                            <img src="{{asset('/public/projectFile/home')}}/assets/images/extra/fund.png" alt="" class=" mb-n4 float-end" height="107"> 
                                        </div>
                                    </div><!--end card-body-->
                                </div><!--end card-->
                            </div><!--end col-->
                            <div class="col-md-6">
                                <div class="card bg-globe-img">
                                    <div class="card-body">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fs-16 fw-semibold">Balance</span>
                                                <form class="">
                                                    <select id="dynamic-select" name="example-select" data-placeholder="Select an option" data-dynamic-select>
                                                        <option value="1" data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/m-card.png">xx25</option>
                                                        <option value="2" data-img="{{asset('/public/projectFile/home')}}/assets/images/logos/ame-bank.png">xx56</option>
                                                    </select>
                                                </form>
                                            </div>
                                            
                                            <h4 class="my-2 fs-24 fw-semibold">@currency($totalBalance)</h4>                                            
                                            <p class="mb-3 text-muted fw-semibold">
                                                <span class="text-success"><i class="fas fa-arrow-up me-1"></i>11.1%</span> Outstanding balance boost
                                            </p> 
                                            <button type="submit" class="btn btn-soft-primary">Transfer</button>
                                            <button type="button" class="btn btn-soft-danger">Request</button> 
                                        </div>
                                    </div><!--end card-body-->
                                </div><!--end card-->
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end col-->
                    <div class="col-lg-5">
                        <div class="row justify-content-center">
                            <div class="col-md-6 col-lg-6">
                                <div class="card bg-corner-img">
                                    <div class="card-body">
                                        <div class="row d-flex justify-content-center">
                                            <div class="col-9">
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Total Revenue (This Month)</p>
                                                <h4 class="mt-1 mb-0 fw-medium">@currency($incomeSum)</h4>
                                            </div>
                                            <!--end col-->
                                            <div class="col-3 align-self-center">
                                                <div class="d-flex justify-content-center align-items-center thumb-md border-dashed border-primary rounded mx-auto">
                                                    <i class="iconoir-dollar-circle fs-22 align-self-center mb-0 text-primary"></i>
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
                            <div class="col-md-6 col-lg-6">
                                <div class="card bg-corner-img">
                                    <div class="card-body">
                                        <div class="row d-flex justify-content-center">
                                            <div class="col-9">
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">New Clients (This Month)</p>
                                                <h4 class="mt-1 mb-0 fw-medium">{{ number_format($newClientsThisMonth) }}</h4>
                                            </div>
                                            <!--end col-->
                                            <div class="col-3 align-self-center">
                                                <div class="d-flex justify-content-center align-items-center thumb-md border-dashed border-info rounded mx-auto">
                                                    <i class="iconoir-cart fs-22 align-self-center mb-0 text-info"></i>
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
                            <div class="col-md-6 col-lg-6">
                                <div class="card bg-corner-img">
                                    <div class="card-body">
                                        <div class="row d-flex justify-content-center">
                                            <div class="col-9">
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Transactions (This Month)</p>
                                                <h4 class="mt-1 mb-0 fw-medium">{{ number_format($txnsThisMonth) }}</h4>
                                            </div>
                                            <!--end col-->
                                            <div class="col-3 align-self-center">
                                                <div class="d-flex justify-content-center align-items-center thumb-md border-dashed border-warning rounded mx-auto">
                                                    <i class="iconoir-percentage-circle fs-22 align-self-center mb-0 text-warning"></i>
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
        
                            <div class="col-md-6 col-lg-6">
                                <div class="card bg-corner-img">
                                    <div class="card-body">
                                        <div class="row d-flex justify-content-center">
                                            <div class="col-9">
                                                <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Avg. Transaction Value</p>
                                                <h4 class="mt-1 mb-0 fw-medium">@currency($avgOrderValue)</h4>
                                            </div>
                                            <!--end col-->
                                            <div class="col-3 align-self-center">
                                                <div class="d-flex justify-content-center align-items-center thumb-md border-dashed border-danger rounded mx-auto">
                                                    <i class="iconoir-hexagon-dice fs-22 align-self-center mb-0 text-danger"></i>
                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </div>
                                    <!--end card-body-->
                                </div>
                                <!--end card-->
                            </div><!--end col-->        
                        </div>
                        <!--end row-->
                    </div><!--end col-->
                    
                </div><!--end row-->
                
                <div class="row justify-content-center">
                    
                    <div class="col-md-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">                      
                                        <h4 class="card-title">Report</h4>                      
                                    </div><!--end col-->
                                    <div class="col-auto"> 
                                        <div class="dropdown">
                                            <a href="#" class="btn bt btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="icofont-calendar fs-5 me-1"></i> This Month<i class="las la-angle-down ms-1"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#">Today</a>
                                                <a class="dropdown-item" href="#">Last Week</a>
                                                <a class="dropdown-item" href="#">Last Month</a>
                                                <a class="dropdown-item" href="#">This Year</a>
                                            </div>
                                        </div>               
                                    </div><!--end col-->
                                </div>  <!--end row-->                                  
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div id="reports" class="apex-charts pill-bar"></div>                                
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end col-->
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="card-title">Cash Flow</h4>
                                    </div>
                                    <!--end col-->
                                    <div class="col-auto">
                                        <div class="dropdown">
                                            <a href="#" class="btn bt btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="icofont-calendar fs-5 me-1"></i>
                                                Weekly<i class="las la-angle-down ms-1"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#">Today</a>
                                                <a class="dropdown-item" href="#">Weekly</a>
                                                <a class="dropdown-item" href="#">Monthly</a>
                                                <a class="dropdown-item" href="#">Yearly</a>
                                            </div>
                                        </div>
                                    </div> <!--end col-->
                                </div><!--end row-->
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div id="cashflow" class="apex-charts"></div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted text-uppercase mb-0 fw-medium fs-13">Income</p>
                                            <h5 class="mt-1 mb-0 fw-medium">{{ $incomePct }}%</h5>
                                        </div>
                                    </div><!--end col-->
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted text-uppercase mb-0 fw-medium fs-13">Expense</p>
                                            <h5 class="mt-1 mb-0 fw-medium">{{ $expensePct }}%</h5>
                                        </div>
                                    </div><!--end col-->
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted text-uppercase mb-0 fw-medium fs-13">Other</p>
                                            <h5 class="mt-1 mb-0 fw-medium">{{ $otherPct }}%</h5>
                                        </div>
                                    </div><!--end col-->
                                </div><!--end row-->
                                <div class=" text-center mx-auto">
                                    <img src="{{asset('/public/projectFile/home')}}/assets/images/extra/rabit.png" alt="" class="d-inline-block" height="105">
                                </div>
                                <div class="card-bg position-relative z-0">
                                    <div class="p-3 bg-primary-subtle rounded position-relative">                                    
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-lg rounded-circle">
                                                <i class="iconoir-bright-star fs-3"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="my-0 fw-normal text-dark fs-13 mb-0">You have $1.53 remaining found over ...<a href="#" class="text-primary fw-medium mb-0 text-decoration-underline">View Details</a></h6>
                                                
                                            </div><!--end media-body-->
                                        </div>                                    
                                    </div>
                                </div>
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div><!--end col-->
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">                      
                                        <h4 class="card-title">Exchange Rate</h4>                      
                                    </div><!--end col-->
                                </div>  <!--end row-->                                  
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr class="">                                                        
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/us_flag.jpg" class="me-2 align-self-center thumb-sm rounded-circle" alt="...">
                                                        <h6 class="m-0 text-truncate">USA</h6>
                                                    </div><!--end media-->
                                                </td>
                                                <td  class="px-0 text-end"><span class="text-body ps-2 align-self-center text-end fw-medium">0.835230 <span class="badge rounded text-success bg-success-subtle">1.10%</span></span></td> 
                                            </tr><!--end tr-->
                                            <tr class="">                                                        
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/spain_flag.jpg" class="me-2 align-self-center thumb-sm rounded-circle" alt="...">
                                                        <h6 class="m-0 text-truncate">Spain</h6>
                                                    </div><!--end media-->
                                                </td>
                                                <td  class="px-0 text-end"><span class="text-body ps-2 align-self-center text-end fw-medium">0.896532 <span class="badge rounded text-success bg-success-subtle">0.91%</span></span></td> 
                                            </tr><!--end tr-->
                                            <tr class="">                                                        
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/french_flag.jpg" class="me-2 align-self-center thumb-sm rounded-circle" alt="...">
                                                        <h6 class="m-0 text-truncate">French</h6>
                                                    </div><!--end media-->
                                                </td>
                                                <td  class="px-0 text-end"><span class="text-body ps-2 align-self-center text-end fw-medium">0.875433 <span class="badge rounded text-danger bg-danger-subtle">0.11%</span></span></td> 
                                            </tr><!--end tr-->
                                            <tr class="">                                                        
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/germany_flag.jpg" class="me-2 align-self-center thumb-sm rounded-circle" alt="...">
                                                        <h6 class="m-0 text-truncate">Germany</h6>
                                                    </div><!--end media-->
                                                </td>
                                                <td  class="px-0 text-end"><span class="text-body ps-2 align-self-center text-end fw-medium">0.795621 <span class="badge rounded text-success bg-success-subtle">0.85%</span></span></td> 
                                            </tr><!--end tr-->
                                            <tr class="">                                                        
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/french_flag.jpg" class="me-2 align-self-center thumb-sm rounded-circle" alt="...">
                                                        <h6 class="m-0 text-truncate">French</h6>
                                                    </div><!--end media-->
                                                </td>
                                                <td  class="px-0 text-end"><span class="text-body ps-2 align-self-center text-end fw-medium">0.875433 <span class="badge rounded text-danger bg-danger-subtle">0.11%</span></span></td> 
                                            </tr><!--end tr-->
                                            <tr class="">                                                        
                                                <td class="px-0 pb-0">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/baha_flag.jpg" class="me-2 align-self-center thumb-sm rounded-circle" alt="...">
                                                        <h6 class="m-0 text-truncate">Bahamas</h6>
                                                    </div><!--end media-->
                                                </td>
                                                <td  class="px-0 pb-0 text-end"><span class="text-body ps-2 align-self-center text-end fw-medium">0.845236 <span class="badge rounded text-danger bg-danger-subtle">0.22%</span></span></td> 
                                            </tr><!--end tr-->
                                        </tbody>
                                    </table> <!--end table-->                   
                                </div><!--end /div--> 
                                <hr class="hr-dashed">     
                                <div class="row">
                                    <div class="col-lg-6 text-center">
                                        <div class="p-2 border-dashed border-theme-color rounded">
                                            <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Higher Rate</p>
                                            <h5 class="mt-1 mb-0 fw-medium text-success">0.833658</h5>
                                            <small>05 Sep 2024</small>
                                        </div>
                                    </div><!--end col-->
                                    <div class="col-lg-6 text-center">
                                        <div class="p-2 border-dashed border-theme-color rounded">
                                            <p class="text-muted text-uppercase mb-0 fw-normal fs-13">Lower Rate</p>
                                            <h5 class="mt-1 mb-0 fw-medium text-danger">0.812547</h5>
                                            <small>05 Sep 2024</small>
                                        </div>
                                    </div><!--end col-->
                                 </div><!--end row-->                      
                            </div><!--end card-body--> 
                        </div><!--end card--> 
                    </div> <!--end col--> 
                </div><!--end row-->

                <div class="row justify-content-center">
                    <div class="col-md-12 col-lg-8 order-1 order-lg-1">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">                      
                                        <h4 class="card-title">Transaction History</h4>
                                        <small class="text-muted">Updated: {{ \Carbon\Carbon::now()->format('d M Y h:i A') }}</small>
                                    </div><!--end col-->
                                    <div class="col-auto"> 
                                        <div class="dropdown">
                                            <a href="#" class="btn bt btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="icofont-calendar fs-5 me-1"></i> This Month<i class="las la-angle-down ms-1"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#">Today</a>
                                                <a class="dropdown-item" href="#">Last Week</a>
                                                <a class="dropdown-item" href="#">Last Month</a>
                                                <a class="dropdown-item" href="#">This Year</a>
                                            </div>
                                        </div>               
                                    </div><!--end col-->
                                </div>  <!--end row-->                                  
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table mb-0 {{ $recentCount > 10 ? 'table-striped table-hover' : '' }}" id="txn-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-top-0">Client</th>
                                                <th class="border-top-0">Source</th>
                                                <th class="border-top-0">Type</th>
                                                <th class="border-top-0">Amount</th>
                                            </tr><!--end tr-->
                                        </thead>
                                        <tbody>
                                            @forelse($recentTxns as $t)
                                            <tr>
                                                <td>
                                                    @php
                                                        // transaction_client_name may contain an ID or a name
                                                        $clientVal = trim($t->transaction_client_name ?? '');
                                                        $clientNameResolved = '';
                                                        if(is_numeric($clientVal) && isset($clientNamesById[(int)$clientVal])){
                                                            $clientNameResolved = $clientNamesById[(int)$clientVal];
                                                        } elseif(!empty($clientVal)) {
                                                            $clientNameResolved = $clientVal;
                                                        } else {
                                                            $clientNameResolved = 'Client';
                                                        }
                                                    @endphp
                                                    <div class="text-truncate">
                                                        <h6 class="m-0 text-truncate" title="{{ $clientNameResolved }}">{{ $clientNameResolved }}</h6>
                                                    </div>
                                                </td>
                                                <td class="text-truncate">
                                                    @php $sourceVal = trim($t->transaction_source ?? ''); @endphp
                                                    @if(is_numeric($sourceVal) && isset($sourceNamesById[(int)$sourceVal]))
                                                        {{ $sourceNamesById[(int)$sourceVal] }}
                                                    @elseif(!empty($sourceVal))
                                                        {{ $sourceVal }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @php $isCredit = strtolower($t->type) === 'credit'; @endphp
                                                    <span class="badge {{ $isCredit ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-11 fw-medium px-2">{{ $isCredit ? 'Credit' : 'Debit' }}</span>
                                                </td>
                                                <td>@currency($t->amount)</td>
                                                
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No recent transactions</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table> <!--end table-->                                               
                                </div><!--end /div-->
                            </div><!--end card-body--> 
                        </div><!--end card--> 
                    </div> <!--end col-->
                    <div class="col-md-6 col-lg-4 order-2 order-lg-2">
                        @php
                            // Build simple client summaries: total transactions and total amount per client (scoped by month)
                            $clientSummaries = $monthlyTxns
                                ->groupBy(function($t){ return trim($t->transaction_client_name ?? ''); })
                                ->map(function($items) use ($clientNamesById){
                                    $raw = trim($items->first()->transaction_client_name ?? '');
                                    $name = 'Client';
                                    if (is_numeric($raw) && isset($clientNamesById[(int)$raw])) {
                                        $name = $clientNamesById[(int)$raw];
                                    } elseif (!empty($raw)) {
                                        $name = $raw;
                                    }
                                    return [
                                        'name' => $name,
                                        'count' => $items->count(),
                                        'total' => $items->sum('amount') ?? 0,
                                    ];
                                })
                                ->sortByDesc('total')
                                ->take(6);
                        @endphp
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">                      
                                        <h4 class="card-title">Client Details</h4>                      
                                    </div><!--end col-->
                                </div>  <!--end row-->                                  
                            </div><!--end card-header-->
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-top-0">Client</th>
                                                <th class="border-top-0">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($clientSummaries as $c)
                                            <tr>
                                                <td class="text-truncate">{{ $c['name'] ?? 'Client' }}</td>
                                                <td>@currency($c['total'])</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No client activity this month</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div><!--end card-body--> 
                        </div><!--end card--> 
                    </div> <!--end col-->                                                    
                </div><!--end row-->
            </div><!-- container -->
@endsection

@push('pageScripts')
<script src="{{asset('/public/projectFile/home')}}/assets/js/pages/index.init.js"></script>
@if($recentCount > 10)
<!-- DataTables assets (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    (function(){
        const activateDataTable = function(){
            if (window.jQuery && jQuery.fn && typeof jQuery.fn.DataTable === 'function') {
                jQuery('#txn-table').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    lengthChange: false,
                    searching: true,
                });
            }
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', activateDataTable);
        } else {
            activateDataTable();
        }
    })();
    </script>
@endif
@endpush