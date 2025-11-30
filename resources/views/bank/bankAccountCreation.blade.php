@extends('include')
@section('backTitle')
Bank Account Creation
@endsection
@section('bodyTitle')
<a href="{{ route('bankAccountCreationView') }}">Bank Account</a>
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
@if(!empty($itemId))
<div class="row">
    <div class="card">
        <div class="card-header">
            <h5 class="">Update Account Detail</h5>
        </div>
        <div class="card-body">
            <form action="{{route('updateBankAccount')}}" method="POST" >
                @csrf
                <input type="hidden" name="id" value="{{ $itemId }}">
                <div class=" mb-2">
                    <label for="fullName">Full Name</label> 
                    <div class="input-group">                                                            
                        <span class="input-group-text" id="fullName"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" placeholder="Name" aria-label="fullName" name="fullName" value="{{ $bankAccount->account_name }}">
                    </div>
                </div>
                <div class=" mb-2">
                    <label for="accountNumber">Account Number</label> 
                    <div class="input-group">                                                            
                        <span class="input-group-text" id="AccountNumber"><i class="fas fa-credit-card"></i></span>
                        <input type="number" class="form-control" placeholder="**** **** **** ****" aria-label="accountNumber" name="accountNumber" value="{{ $bankAccount->account_number }}">
                    </div>
                </div>
                    @php
                        $bankMagages = App\Models\bankManage::all();
                    @endphp
                <div class=" mb-2">
                    <label for="bankManageId">Bank Manage</label>
                        <select class="form-select" id="bankManageId" name="bankManageId" required>
                            @if(!empty($bankAccount->bank_manage_id))
                                <option value="{{ $bankAccount->bank_manage_id }}">{{ $bankAccount->bank_name }} - {{ $bankAccount->branch_name }} -{{ $bankAccount->routing_number }}</option>
                                @else
                                <option value="">-- Select --</option>
                                @endif
                            @if(!empty($bankMagages) && $bankMagages->count()>0)
                            @foreach($bankMagages as $bankManage)
                            <option value="{{ $bankManage->id }}">{{ $bankManage->bank_name }} - {{ $bankManage->branch_name }} - {{ $bankManage->routing_number }}
                            </option>
                            @endforeach
                            @else
                            <option value="">No Source Found</option>
                            @endif
                        </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="entryDate">Entry Date</label> 
                            <div class="input-group">
                                <span class="input-group-text" id="entryDate"><i class="far fa-calendar"></i></span>
                                <input type="date" class="form-control" placeholder="01/35" aria-label="entryDate" name="entryDate">
                            </div>
                        </div>
                    </div><!--end col-->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="opningBalance">Opning Balance</label> 
                            <div class="input-group">
                                <span class="input-group-text" id="opningBalance"><i class="fas fa-ellipsis"></i></span>
                                <input type="number" class="form-control" placeholder="123" aria-label="opningBalance" name="opningBalance">
                            </div>
                        </div>                                                            
                    </div>
                </div>           
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Add Account</button>
                    <button type="reset" class="btn btn-light w-100">Reset</button>
                </div>
            </form>     
        </div>
    </div>
</div>
@else
    <div class="row mb-3">                            
        <div class="col-md-12 col-lg-3">
            <div class="card  h-100 bg-blue bg-globe-img">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <p class="text-white text-uppercase mb-0 fw-semibold fs-14">Master Card</p>
                        </div>
                        <!--end col-->
                        <div class="col-3 align-self-center text-end">
                            <img src="assets/images/logos/m-card.png" alt="" class="" height="20">
                        </div><!--end col-->
                    </div><!--end row--> 
                    <div class="row mt-3 mb-1">
                        <div class="col-9">                                        
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Balance</p>
                            <h5 class="mt-1 mb-0 fw-semibold fs-20 text-white">$98659.50</h5>
                        </div>
                        <!--end col-->
                        <div class="col-3 align-self-center text-end">
                            <i class="iconoir-wifi fs-24 trans-90 text-white-50"></i>
                        </div><!--end col-->
                    </div><!--end row-->                               
                </div><!--end card-body--> 
                <div class="card-body p-2 bg-black rounded-bottom">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Expiry: 01/32</p>
                            <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">Daniel Leonard</h5>
                        </div>
                        <!--end col-->
                        <div class="col-6 align-self-center text-end">
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">CVV: 301</p>
                            <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">**** **** **** 1234</h5>
                        </div><!--end col-->
                    </div><!--end row-->                           
                </div><!--end card-body-->                            
            </div><!--end card-->
        </div><!--end col-->
        <div class="col-md-12 col-lg-3">
            <div class="card  h-100 bg-warning bg-globe-img">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <p class="text-white text-uppercase mb-0 fw-semibold fs-14">Visa Card</p>
                        </div>
                        <!--end col-->
                        <div class="col-3 align-self-center text-end">
                            <img src="assets/images/logos/visa.png" alt="" class="" height="20">
                        </div><!--end col-->
                    </div><!--end row--> 
                    <div class="row mt-3 mb-1">
                        <div class="col-9">                                        
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Balance</p>
                            <h5 class="mt-1 mb-0 fw-semibold fs-20 text-white">$44125.50</h5>
                        </div>
                        <!--end col-->
                        <div class="col-3 align-self-center text-end">
                            <i class="iconoir-wifi fs-24 trans-90 text-white-50"></i>
                        </div><!--end col-->
                    </div><!--end row-->                               
                </div><!--end card-body--> 
                <div class="card-body p-2 bg-black rounded-bottom">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Expiry: 01/35</p>
                            <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">Mary Mallory</h5>
                        </div>
                        <!--end col-->
                        <div class="col-6 align-self-center text-end">
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">CVV: 650</p>
                            <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">**** **** **** 1234</h5>
                        </div><!--end col-->
                    </div><!--end row-->                           
                </div><!--end card-body-->                            
            </div><!--end card-->
        </div><!--end col-->
        <div class="col-md-12 col-lg-3">
            <div class="card  h-100 bg-black bg-globe-img">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <p class="text-white text-uppercase mb-0 fw-semibold fs-14">Master Card</p>
                        </div>
                        <!--end col-->
                        <div class="col-3 align-self-center text-end">
                            <img src="assets/images/logos/m-card.png" alt="" class="" height="20">
                        </div><!--end col-->
                    </div><!--end row--> 
                    <div class="row mt-3 mb-1">
                        <div class="col-9">                                        
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Balance</p>
                            <h5 class="mt-1 mb-0 fw-semibold fs-20 text-white">$36251.50</h5>
                        </div>
                        <!--end col-->
                        <div class="col-3 align-self-center text-end">
                            <i class="iconoir-wifi fs-24 trans-90 text-white-50"></i>
                        </div><!--end col-->
                    </div><!--end row-->                               
                </div><!--end card-body--> 
                <div class="card-body p-2 bg-soft-secondary rounded-bottom">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Expiry: 01/30</p>
                            <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">John Carter</h5>
                        </div>
                        <!--end col-->
                        <div class="col-6 align-self-center text-end">
                            <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">CVV: 511</p>
                            <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">**** **** **** 1234</h5>
                        </div><!--end col-->
                    </div><!--end row-->                           
                </div><!--end card-body-->                            
            </div><!--end card-->
        </div><!--end col-->
        <div class="col-md-12 col-lg-3">
            <div class="card  h-100 bg-dark-subtle bg-globe-img">
                <div class="card-body text-center">
                    <a href="#" class="h-100 d-block" data-bs-toggle="modal" data-bs-target="#addCard">
                    <div class="position-relative h-100 d-block">
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <i class="fas fa-plus fs-30"></i>
                            <h5 class="fw-medium fs-18 text-muted">Account</h5> 
                        </div> 
                    </div>
                    </a>                    
                </div><!--end card-body-->                            
            </div><!--end card-->
        </div><!--end col-->
    </div><!--end row-->

<div class="row justify-content-center">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">                      
                        <h4 class="card-title">Acccount List</h4>                      
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
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-top-0">Entry Date</th>
                                <th class="border-top-0">Account Name</th>
                                <th class="border-top-0">Account Number</th>                                                
                                <th class="border-top-0">Branch</th>
                                <th class="border-top-0">Opning Balance</th>
                                <th class="border-top-0">Action</th>
                            </tr><!--end tr-->
                        </thead>
                        <tbody>
                            @if(isset($bankAccounts) && count($bankAccounts) > 0)
                                @foreach($bankAccounts as $bankAccount)
                            <tr>   
                                <td>{{ $bankAccount->entry_date }}</td>
                                <td>{{ $bankAccount->account_name }}</td>
                                <td>{{ $bankAccount->account_number }}</td>
                                <td>{{ $bankAccount->bank_name }} - {{ $bankAccount->branch_name }} - {{ $bankAccount->routing_number }}</td>
                                <td>{{ $bankAccount->opning_balance }}</td>
                                <td>
                                    <a href="#"><i class="las la-print text-secondary fs-18"></i></a>
                                    
                                    <a href="{{ route('bankAccountEdit',['id'=>$bankAccount->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                </td>
                            </tr><!--end tr-->    
                                @endforeach
                            @else 
                            <tr>             
                                <td>15 July 2024</td> 
                                <td>Card Payment</td>
                                <td>UI/UX Project</td>                                                                                 
                                <td>$700</td>
                                <td><span class="badge bg-danger-subtle text-danger fs-11 fw-medium px-2">Debit</span></td>
                                <td>                                                       
                                    <a href="#"><i class="las la-print text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-download text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                </td>
                            </tr><!--end tr--> 
                            @endif          
                        </tbody>
                    </table> <!--end table-->                                               
                </div><!--end /div-->
                <div class="d-lg-flex justify-content-end mt-2">
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
                        </ul><!--end pagination-->
                    </div>
                </div>
            </div><!--end card-body--> 
        </div><!--end card--> 
    </div> <!--end col-->
    
</div><!--end row-->

 <!-- end page-wrapper -->
    <div class="modal fade" id="addCard" tabindex="-1" aria-labelledby="addCardLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addCardLabel">Add Account Detail</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('saveBankAccount')}}" method="POST" >
                    @csrf
                    <div class=" mb-2">
                        <label for="fullName">Full Name</label> 
                        <div class="input-group">                                                            
                            <span class="input-group-text" id="fullName"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" placeholder="Name" aria-label="fullName" name="fullName">
                        </div>
                    </div>
                    <div class=" mb-2">
                        <label for="accountNumber">Account Number</label> 
                        <div class="input-group">                                                            
                            <span class="input-group-text" id="AccountNumber"><i class="fas fa-credit-card"></i></span>
                            <input type="number" class="form-control" placeholder="**** **** **** ****" aria-label="accountNumber" name="accountNumber">
                        </div>
                    </div>
                        @php
                            $bankMagages = App\Models\bankManage::all();
                        @endphp
                    <div class=" mb-2">
                        <label for="bankManageId">Bank Manage</label>
                            <select class="form-select" id="bankManageId" name="bankManageId" required>
                                <option value="">-- Select --</option>
                                @if(!empty($bankMagages) && $bankMagages->count()>0)
                                @foreach($bankMagages as $bankManage)
                                <option value="{{ $bankManage->id }}">{{ $bankManage->bank_name }} - {{ $bankManage->branch_name }} - {{ $bankManage->routing_number }}
                                </option>
                                @endforeach
                                @else
                                <option value="">No Source Found</option>
                                @endif
                            </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="entryDate">Entry Date</label> 
                                <div class="input-group">
                                    <span class="input-group-text" id="entryDate"><i class="far fa-calendar"></i></span>
                                    <input type="date" class="form-control" placeholder="01/35" aria-label="entryDate" name="entryDate">
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="opningBalance">Opning Balance</label> 
                                <div class="input-group">
                                    <span class="input-group-text" id="opningBalance"><i class="fas fa-ellipsis"></i></span>
                                    <input type="number" class="form-control" placeholder="123" aria-label="opningBalance" name="opningBalance">
                                </div>
                            </div>                                                            
                        </div>
                    </div>           
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Add Account</button>
                <button type="reset" class="btn btn-light w-100">Reset</button>
                </div>
            </form>     
          </div>
        </div>
      </div>
@endif
      @endsection
