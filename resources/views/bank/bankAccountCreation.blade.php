@extends('include')
@section('backTitle')
Bank Account Creation
@endsection
@section('bodyTitleFrist')
   Bank Account
@endsection
@section('bodyTitleEnd')
Bank
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
    @php
        // Ensure $currentBalance is defined for edit page.
        // If controller didn't pass it, read from bank_balances table (safe fallback).
        if (!isset($currentBalance)) {
            $acctId = $bankAccount->id ?? $itemId;
            $currentBalance = \DB::table('bank_balances')
                ->where('bank_account_id', $acctId)
                ->value('balance') ?? 0;
        }
    @endphp
    <div class="card">
        <div class="card-header">
            <h5 class="">Update Account Detail</h5>
        </div>
        <div class="card-body">
            <form action="{{route('updateBankAccount')}}" method="POST" >
                @csrf
                <input type="hidden" name="id" value="{{ $itemId }}">
                <div class=" mb-2">
                    <label for="account_name">Account Name</label> 
                    <div class="input-group">                                                            
                        <span class="input-group-text" id="account_name_addon"><i class="fas fa-user"></i></span>
                        <input id="account_name" type="text" class="form-control" placeholder="Name" aria-label="account_name" name="account_name" value="{{ $bankAccount->account_name }}">
                    </div>
                    @error('account_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                                <option value="{{ $bankAccount->bank_manage_id }}" selected>{{ $bankAccount->bank_name }} - {{ $bankAccount->branch_name }} -{{ $bankAccount->routing_number }}</option>
                            @endif
                            @if(!empty($bankMagages) && $bankMagages->count()>0)
                                @foreach($bankMagages as $bankManage)
                                    <option value="{{ $bankManage->id }}" @if(empty($bankAccount->bank_manage_id) ? $loop->first : false) selected @endif>
                                        {{ $bankManage->bank_name }} - {{ $bankManage->branch_name }} - {{ $bankManage->routing_number }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" selected disabled>No Source Found</option>
                            @endif
                        </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="entryDate">Entry Date</label> 
                            <div class="input-group">
                                <span class="input-group-text" id="entryDate"><i class="far fa-calendar"></i></span>
                                <input type="date" class="form-control" placeholder="01/35" aria-label="entryDate" name="entryDate" value="{{ old('entryDate', $bankAccount->entry_date ?? now()->toDateString()) }}">
                            </div>
                        </div>
                    </div><!--end col-->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="currentBalance">Current Balance</label> 
                            <div class="input-group">
                                <span class="input-group-text" id="currentBalance"><i class="fas fa-ellipsis"></i></span>
                                <input type="number" class="form-control" placeholder="123" aria-label="currentBalance" name="currentBalance" value="{{ $currentBalance }}">
                            </div>
                        </div>                                                
                    </div>
                </div> 
                <div class="text-center mb-4 mt-3">
                    <button type="submit" class="btn btn-primary">Update Account</button>
                    <button type="reset" class="btn btn-light ">Reset</button>
                </div>
            </form>     
        </div>
    </div>
</div>
@else
    <div class="row mb-3">
        @php $hasAccounts = isset($bankAccounts) && count($bankAccounts) > 0; @endphp
        @if($hasAccounts)
            @foreach($bankAccounts as $acct)
                @php
                    $balanceRow = \DB::table('bank_balances')->where('bank_account_id', $acct->id)->first();
                    $currentBalance = $balanceRow ? $balanceRow->balance : 0;
                    $masked = $acct->account_number ? str_repeat('*', max(0, strlen($acct->account_number) - 4)).substr($acct->account_number,-4) : 'N/A';
                    $entry = $acct->entry_date ?: 'N/A';
                    $themes = [
                        ['card' => 'bg-blue', 'footer' => 'bg-black'],
                        ['card' => 'bg-warning', 'footer' => 'bg-black'],
                        ['card' => 'bg-black', 'footer' => 'bg-soft-secondary'],
                    ];
                    $theme = $themes[$loop->index % count($themes)];
                @endphp
                <div class="col-md-12 col-lg-3">
                    <div class="card h-100 {{ $theme['card'] }} bg-globe-img">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-9">
                                    <p class="text-white text-uppercase mb-0 fw-semibold fs-14">{{ $acct->bank_name }}</p>
                                </div>
                                <div class="col-3 align-self-center text-end">
                                    <i class="iconoir-credit-card fs-20 text-white-50"></i>
                                </div>
                            </div>
                            <div class="row mt-3 mb-1">
                                <div class="col-9">
                                    <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Balance</p>
                                    <h5 class="mt-1 mb-0 fw-semibold fs-20 text-white">${{ number_format($currentBalance,2) }}</h5>
                                </div>
                                <div class="col-3 align-self-center text-end">
                                    <i class="iconoir-wifi fs-24 trans-90 text-white-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2 rounded-bottom {{ $theme['footer'] }}">
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Entry: {{ $entry }}</p>
                                    <h5 class="mt-1 mb-0 fw-medium fs-14 text-white">{{ $acct->account_name }}</h5>
                                </div>
                                <div class="col-6 align-self-center text-end">
                                    <p class="text-white-50 text-uppercase mb-0 fw-normal fs-12">Routing</p>
                                    <h6 class="mt-1 mb-0 fw-medium fs-12 text-white">{{ $acct->routing_number }}</h6>
                                    <h6 class="mt-1 mb-0 fw-medium fs-12 text-white">{{ $masked }}</h6>
                                </div>
                            </div>
                            <div class="mt-2 d-flex justify-content-between">
                                <a href="{{ route('bankAccountEdit',['id'=>$acct->id]) }}" class="btn btn-sm btn-outline-light">Edit</a>
                                <form method="POST" action="{{ route('deleteBankAccount',['id'=>$acct->id]) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this bank account?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <!-- No accounts yet: show just the add card tile -->
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
                    </div>
                </div>
            </div>
        @endif
    </div>

<div class="row justify-content-center">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Acccount List</h4>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCard">
                            <i class="las la-plus me-1"></i> Create Account
                        </button>
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
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <form id="bankaccount-bulk-form" method="POST" action="{{ route('bankAccounts.bulkDelete') }}" data-confirm-delete data-confirm-message="Delete the selected bank accounts?">
                    @csrf
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="bankaccount-select-all" class="form-check-input"></th>
                                    <th class="border-top-0">SL</th>
                                    <th class="border-top-0">Entry Date</th>
                                    <th class="border-top-0">Account Name</th>
                                    <th class="border-top-0">Account Number</th>                                                
                                    <th class="border-top-0">Branch</th>
                                    <th class="border-top-0">Current Balance</th>
                                    <th class="border-top-0">Action</th>
                                </tr><!--end tr-->
                            </thead>
                            <tbody>
                                @if(isset($bankAccounts) && count($bankAccounts) > 0)
                                    @php $x = 1; @endphp
                                    @foreach($bankAccounts as $bankAccount)
                                        @php
                                            $balanceRow = \DB::table('bank_balances')->where('bank_account_id', $bankAccount->id)->first();
                                            $currentBalance = $balanceRow ? $balanceRow->balance : 0;
                                        @endphp
                                <tr>   
                                    <td><input type="checkbox" name="ids[]" value="{{ $bankAccount->id }}" class="form-check-input bankaccount-checkbox" form="bankaccount-bulk-form"></td>
                                    <td>{{ $x }}</td>
                                    <td>{{ $bankAccount->entry_date }}</td>
                                    <td>{{ $bankAccount->account_name }}</td>
                                    <td>{{ $bankAccount->account_number }}</td>
                                    <td>{{ $bankAccount->bank_name }} - {{ $bankAccount->branch_name }} - {{ $bankAccount->routing_number }}</td>
                                    <td>{{ number_format($currentBalance,2) }}</td>
                                    <td>
                                        <a href="#"><i class="las la-print text-secondary fs-18"></i></a>
                                        <a href="{{ route('bankAccountEdit',['id'=>$bankAccount->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                        <form method="POST" action="{{ route('deleteBankAccount',['id'=>$bankAccount->id]) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this bank account?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0"><i class="las la-trash-alt text-secondary fs-18"></i></button>
                                        </form>
                                    </td>
                                </tr><!--end tr-->    
                                    @php $x++; @endphp
                                    @endforeach
                                @else 
                                <tr>             
                                    <td></td>
                                    <td></td>
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
                    <div class="mt-3">
                        <button type="submit" form="bankaccount-bulk-form" id="bankaccount-bulk-delete-btn" class="btn btn-danger" disabled>
                            <i class="fas fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                </form>
                <div class="d-lg-flex justify-content-end mt-2">
                    {{ $bankAccounts->links() }}
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
                        <label for="modal_account_name">Account Name</label> 
                        <div class="input-group">                                                            
                            <span class="input-group-text" id="modal_account_name_addon"><i class="fas fa-user"></i></span>
                            <input id="modal_account_name" type="text" class="form-control" placeholder="Name" aria-label="account_name" name="account_name" value="{{ old('account_name') }}">
                        </div>
                        @error('account_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class=" mb-2">
                        <label for="accountNumber">Account Number</label> 
                        <div class="input-group">                                                            
                            <span class="input-group-text" id="AccountNumber"><i class="fas fa-credit-card"></i></span>
                            <input type="number" class="form-control" placeholder="**** **** **** ****" aria-label="accountNumber" name="accountNumber" value="{{ old('accountNumber') }}">
                            @error('accountNumber')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                        @php
                            $bankMagages = App\Models\bankManage::all();
                        @endphp
                    <div class=" mb-2">
                        <label for="bankManageId">Bank Manage</label>
                            <select class="form-select" id="bankManageId" name="bankManageId" required>
                                @if(!empty($bankMagages) && $bankMagages->count()>0)
                                    @foreach($bankMagages as $bankManage)
                                    <option value="{{ $bankManage->id }}" @if(old('bankManageId') ? old('bankManageId')==$bankManage->id : $loop->first) selected @endif>{{ $bankManage->bank_name }} - {{ $bankManage->branch_name }} - {{ $bankManage->routing_number }}
                                    </option>
                                    @endforeach
                                @else
                                    <option value="" selected disabled>No Source Found</option>
                                @endif
                            </select>
                            @error('bankManageId')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="entryDate">Entry Date</label> 
                                <div class="input-group">
                                    <span class="input-group-text" id="entryDate"><i class="far fa-calendar"></i></span>
                                    <input type="date" class="form-control" placeholder="01/35" aria-label="entryDate" name="entryDate" value="{{ old('entryDate', now()->toDateString()) }}">
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="currentBalance">Current Balance</label> 
                                <div class="input-group">
                                    <span class="input-group-text" id="currentBalance"><i class="fas fa-ellipsis"></i></span>
                                    <input type="number" class="form-control" placeholder="123" aria-label="currentBalance" name="currentBalance" value="{{ old('currentBalance') }}">
                                    @error('currentBalance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var openCreateModal = {{ $errors->any() && empty($itemId) ? 'true' : 'false' }};
    if (openCreateModal && window.bootstrap) {
        var el = document.getElementById('addCard');
        if (el) {
            var modal = new bootstrap.Modal(el);
            modal.show();
        }
    }

    // Bank Account Bulk Delete
    const selectAllCheckbox = document.getElementById('bankaccount-select-all');
    const bankaccountCheckboxes = document.querySelectorAll('.bankaccount-checkbox');
    const deleteBtn = document.getElementById('bankaccount-bulk-delete-btn');
    const bulkForm = document.getElementById('bankaccount-bulk-form');

    if (selectAllCheckbox && bankaccountCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            bankaccountCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDeleteButtonState();
        });

        bankaccountCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateDeleteButtonState();
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                }
            });
        });

        function updateDeleteButtonState() {
            const anyChecked = Array.from(bankaccountCheckboxes).some(cb => cb.checked);
            deleteBtn.disabled = !anyChecked;
        }

        bulkForm.addEventListener('submit', function(e) {
            const checkedCount = Array.from(bankaccountCheckboxes).filter(cb => cb.checked).length;
            if (!confirm(`Are you sure you want to delete ${checkedCount} bank account(s)? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush

@endsection
