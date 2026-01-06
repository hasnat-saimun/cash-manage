@extends('include')
@section('backTitle')
Bank Manage
@endsection

@section('bodyTitleFrist')
   Bank Manage
@endsection
@section('bodyTitleEnd')
   <a href="{{ route('bankManageView') }}">Bank Entry</a>
@endsection
@section('bodyContent')
@php
    if(!empty($itemId)):
            $items          = \App\Models\bankManage::find($itemId);
        if(!empty($items)): 

            $bankName       = $items->bank_name;
            $branchName     = $items->branch_name;
            $routingNumber  = $items->routing_number;

        endif;
    else:
            $itemId                 = null;
            $bankName               = " ";
            $branchName             = " ";
            $routingNumber          = " ";
    endif;
@endphp

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


@if(empty($itemId))
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Bank Manage Details</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <button class="btn bg-primary text-white" data-bs-toggle="modal" data-bs-target="#addRate">
                            <i class="fas fa-plus me-1"></i> Add Bank
                        </button>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end card-header-->
            <div class="card-body pt-0">
                <form id="bankmanage-bulk-form" method="POST" action="{{ route('bankManages.bulkDelete') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table mb-0" id="datatable_1">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="bankmanage-select-all" class="form-check-input"></th>
                                    <th>SL</th>
                                    <th>Bank Name</th>
                                    <th>Branch</th>
                                    <th>routing Nmuber</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @php
                                    $x = 1;
                                @endphp
                                @if(isset($bankManages) && count($bankManages) > 0)
                                    @foreach($bankManages as $bankManage)
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="{{ $bankManage->id }}" class="form-check-input bankmanage-checkbox"></td>
                                            <td>{{ $x }}</td>
                                            <td>{{$bankManage->bank_name}}</td>
                                            <td>{{$bankManage->branch_name}}</td>
                                            <td>{{$bankManage->routing_number}}</td>
                                            <td class="text-end">
                                                <a href="{{ route('bankManageEdit',['id'=>$bankManage->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                                <a href="{{ route('deleteBankManage',['id'=>$bankManage->id]) }}"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                            </td>
                                        </tr>
                                        @php
                                            $x++;
                                        @endphp
                                    @endforeach
                                @else
                                <tr>
                                    <td></td>
                                    <td>01</td>
                                    <td>Empty</td>
                                    <td>Empty</td>
                                    <td>Empty</td>
                                    <td class="text-end">
                                        <a href="#"><i class="las la-pen text-secondary fs-18"></i></a>
                                        <a href="#"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" id="bankmanage-bulk-delete-btn" class="btn btn-danger" disabled>
                            <i class="fas fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- end col -->
</div>
<!-- end row -->
@else
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Update Bank Detail</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <a href="{{ route('bankManageView') }}" class="btn btn-secondary ">Back</a>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <div class="card-body">
                <form action="{{route('updateBankManage')}}" method="POST">
                    @csrf        
                        <input type="hidden" name="itemId" value="{{ $itemId }}">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label for="bankName">Bank Name</label> 
                            <div class="input-group">                      
                                <input type="text" class="form-control" placeholder="Enter The Bank Name" aria-label="bankName" name="bankName" id="bankName" value="{{ $bankName }}">
                            </div>
                        </div>   
                        <div class="col-6 mb-2">
                            <label for="branchName">Branch Name</label> 
                            <div class="input-group">                      
                                <input type="text" class="form-control" placeholder="Enter The Branch Name" aria-label="branchName" name="branchName" id="branchName" value="{{ $branchName }}">
                            </div>
                        </div>   
                        <div class="col-6 mb-2">
                            <label for="routingNumber">Routing Number</label> 
                            <div class="input-group">                      
                                <input type="text" class="form-control" placeholder="Enter The Routing Number" aria-label="routingNumber" name="routingNumber" id="routingNumber" value="{{ $routingNumber }}">
                            </div>
                        </div>    
                        <div class="col-6 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Update Data</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- end page-wrapper -->
<div class="modal fade" id="addRate" tabindex="-1" aria-labelledby="addRateLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addRateLabel">Add Bank Detail</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('saveBankManage')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class=" mb-2">
                        <label for="bankName">Bank Name</label> 
                        <div class="input-group">                      
                            <input type="text" class="form-control" placeholder="Enter The Bank Name" aria-label="bankName" name="bankName" id="bankName">
                        </div>
                    </div>   
                    <div class=" mb-2">
                        <label for="branchName">Branch Name</label> 
                        <div class="input-group">                      
                            <input type="text" class="form-control" placeholder="Enter The Branch Name" aria-label="branchName" name="branchName" id="branchName">
                        </div>
                    </div>   
                    <div class=" mb-2">
                        <label for="routingNumber">Routing Number</label> 
                        <div class="input-group">                      
                            <input type="text" class="form-control" placeholder="Enter The Routing Number" aria-label="routingNumber" name="routingNumber" id="routingNumber">
                        </div>
                    </div>         
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Add New Bank</button>
                <button type="reset" class="btn btn-light w-100">Reset</button>
                </div>
            </form>
          </div>
        </div>
      </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('bankmanage-select-all');
    const bankmanageCheckboxes = document.querySelectorAll('.bankmanage-checkbox');
    const deleteBtn = document.getElementById('bankmanage-bulk-delete-btn');
    const bulkForm = document.getElementById('bankmanage-bulk-form');

    if (selectAllCheckbox && bankmanageCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            bankmanageCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDeleteButtonState();
        });

        bankmanageCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateDeleteButtonState();
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                }
            });
        });

        function updateDeleteButtonState() {
            const anyChecked = Array.from(bankmanageCheckboxes).some(cb => cb.checked);
            deleteBtn.disabled = !anyChecked;
        }

        bulkForm.addEventListener('submit', function(e) {
            const checkedCount = Array.from(bankmanageCheckboxes).filter(cb => cb.checked).length;
            if (!confirm(`Are you sure you want to delete ${checkedCount} bank manager(s)? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush

@endsection