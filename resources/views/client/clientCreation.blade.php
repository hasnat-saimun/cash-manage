@extends('include')
@section('backTitle')
Clint
@endsection
@section('bodyTitleFrist')
    Client
@endsection
@section('bodyTitleEnd')
    client
@endsection
@section('bodyContent')


@php
// Replace the balance computation: prefer client_balances row, otherwise compute from transactions
$fullName = $email = $mobileNo = $clientSource = $clientOpBalance = $registerDate = '';
if (!empty($itemId)) {
    $items = \App\Models\clientCreation::find($itemId);
    if ($items) {
        $fullName     = $items->client_name ?? '';
        $email        = $items->client_email ?? '';
        $mobileNo     = $items->client_phone ?? '';
        $clientSource = $items->client_source ?? '';

        // Prefer client_balances value first
        $balanceRow = \Illuminate\Support\Facades\DB::table('client_balances')->where('client_id', $items->id)->first();
        if ($balanceRow && isset($balanceRow->balance)) {
            $clientOpBalance = (float) $balanceRow->balance;
        } else {
            // fallback: compute current balance from transactions: credits - debits
            $tot = \Illuminate\Support\Facades\DB::table('transactions')
                ->where('transaction_client_name', $items->id)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN LOWER(type) = 'credit' THEN amount ELSE 0 END),0) as total_credit,
                    COALESCE(SUM(CASE WHEN LOWER(type) = 'debit' THEN amount ELSE 0 END),0) as total_debit
                ")->first();

            $clientOpBalance = (float)($tot->total_credit ?? 0) - (float)($tot->total_debit ?? 0);
        }

        $registerDate = $items->client_regDate ?? '';
    } else {
        $itemId = null;
    }
} else {
    // keep defaults (already initialized)
}
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
                        <h4 class="card-title">Client Details</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <button class="btn bg-primary text-white" data-bs-toggle="modal" data-bs-target="#addClient">
                            <i class="fas fa-plus me-1"></i> Add Client
                        </button>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end card-header-->
            <div class="card-body pt-0">
                <form id="client-bulk-form" method="POST" action="{{ route('clients.bulkDelete') }}" data-confirm-delete data-confirm-message="Delete the selected clients? This cannot be undone.">
                    @csrf
                    <div class="table-responsive">
                        <table class="table mb-0" id="datatable_1">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="client-select-all" class="form-check-input"></th>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Balance</th>
                                    <th>Registered</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $x = 1; @endphp

                                @if(!empty($allClient) && $allClient->count()>0)
                                    @foreach($allClient as $client)
                                        @php
                                            // prefer client_balances
                                            $bal = \Illuminate\Support\Facades\DB::table('client_balances')
                                                    ->where('client_id', $client->id)
                                                    ->value('balance');

                                            if ($bal === null) {
                                                // fallback: compute from transactions
                                                $tot = \Illuminate\Support\Facades\DB::table('transactions')
                                                        ->where('transaction_client_name', $client->id)
                                                        ->selectRaw("
                                                            COALESCE(SUM(CASE WHEN LOWER(type) = 'credit' THEN amount ELSE 0 END),0) as total_credit,
                                                            COALESCE(SUM(CASE WHEN LOWER(type) = 'debit' THEN amount ELSE 0 END),0) as total_debit
                                                        ")->first();
                                                $bal = (float)($tot->total_credit ?? 0) - (float)($tot->total_debit ?? 0);
                                            } else {
                                                $bal = (float) $bal;
                                            }
                                        @endphp
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="{{ $client->id }}" class="form-check-input client-checkbox" form="client-bulk-form"></td>
                                            <td>{{ $x }}</td>
                                            <td>
                                                <div class="flex-grow-1 text-truncate">
                                                    <h6 class="m-0">{{ $client->client_name }}</h6>
                                                </div>
                                            </td>
                                            <td>{{ number_format($bal,2) }}</td>
                                            <td>{{ $client->client_regDate }}</td>
                                            <td>{{ $client->client_email }}</td>
                                            <td>{{ $client->client_phone }}</td>
                                            <td><span class="badge rounded text-success bg-success-subtle">Active</span></td>
                                            <td class="text-end">
                                                <a href="{{ route('clientEdit',['id'=>$client->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                                <form method="POST" action="{{ route('deleteClient',['id'=>$client->id]) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this client?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0"><i class="las la-trash-alt text-secondary fs-18"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        @php $x++; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td></td>
                                        <td>{{ $x }}</td>
                                        <td class="d-flex align-items-center">
                                            <div class="flex-grow-1 text-truncate">
                                                <h6 class="m-0">Virtual It Professional</h6>
                                            </div>
                                        </td>
                                        <td>+1 234 567 890</td>
                                        <td>9000</td>
                                        <td>22 August 2024</td>
                                        <td><a href="#" class="text-body text-decoration-underline">dummy@gmail.com</a></td>
                                        <td><span class="badge rounded text-success bg-success-subtle">Active</span></td>
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
                        <button type="submit" form="client-bulk-form" id="client-bulk-delete-btn" class="btn btn-danger" disabled>
                            <i class="fas fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- end col -->
</div>
@else
<!-- end row -->
 
<div class="row">
    <div class="col-12">
        <div class="card">
             <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Update Client Detail</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <a href="{{ route('clientCreation') }}" class="btn btn-secondary ">Back</a>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <div class="card-body">
                <form action="{{ route('updateClient') }}" method="POST" >
                @csrf
                    <input type="hidden" name="itemId" value="{{ $itemId }}">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label for="fullName">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text" id="fullName"><i class="far fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Name" aria-label="FullName" name="fullName" value="{{ $fullName }}" required />
                            </div>
                        </div>

                        <!-- Updated label: show Current Balance (value comes from client_balances via $clientOpBalance) -->
                        <div class="col-6 mb-2">
                            <label for="clientOpBalance">Current Balance</label>
                            <div class="input-group">
                                <span class="input-group-text" id="clientOpBalance">$</span>
                                <input type="number" step="0.01" class="form-control" placeholder="Current Balance" aria-label="clientOpBalance" name="clientOpBalance" value="{{ $clientOpBalance }}"  required />
                            </div>
                        </div>
                    </div> 
                    <div class="mb-2">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text" id="email"><i class="far fa-envelope"></i></span>
                            <input type="email" class="form-control" placeholder="Email address" aria-label="email" name="email" value="{{ $email }}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="ragisterDate">Register Date</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="ragisterDate"><i class="far fa-calendar"></i></span>
                                    <input
                                        type="date"
                                        class="form-control"
                                        placeholder="00/2024"
                                        aria-label="ragisterDate"
                                        name="registerDate"
                                        value="{{ $registerDate ?: now()->toDateString() }}" required
                                    />
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="mobilleNo">Mobille No</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="mobilleNo"><i class="fas fa-phone"></i></span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="+1 234 567 890"
                                        aria-label="mobilleNo"
                                        name="mobileNo"
                                        value="{{ $mobileNo }}" required
                                    />
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <div class="text-center mb-3">
                    <button type="submit" class="btn btn-primary ">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


<!-- end page-wrapper -->
<div class="modal fade" id="addClient" tabindex="-1" aria-labelledby="addClientLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientLabel">Add Client Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('saveClient') }}" method="POST" >
                @csrf
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label for="fullName">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text" id="fullName"><i class="far fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Name" aria-label="FullName" name="fullName"  required />
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <label for="clientOpBalance">Opning Balance</label>
                            <div class="input-group">
                                <span class="input-group-text" id="clientOpBalance">$</span>
                                <input type="number" class="form-control" placeholder="Opning Balance" aria-label="clientOpBalance" name="clientOpBalance"  required />
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text" id="email"><i class="far fa-envelope"></i></span>
                            <input type="email" class="form-control" placeholder="Email address" aria-label="email" name="email"  />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="ragisterDate">Register Date</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="ragisterDate"><i class="far fa-calendar"></i></span>
                                    <input
                                        type="date"
                                        class="form-control"
                                        placeholder="00/2024"
                                        aria-label="ragisterDate"
                                        name="registerDate" value="{{ old('registerDate', now()->toDateString()) }}" required
                                    />
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="mobilleNo">Mobille No</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="mobilleNo"><i class="fas fa-phone"></i></span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="+1 234 567 890"
                                        aria-label="mobilleNo"
                                        name="mobileNo" required
                                    />
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Add Client</button>
                    
                    <button type="reset" class="btn btn-light w-100">reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('client-select-all');
    const clientCheckboxes = document.querySelectorAll('.client-checkbox');
    const deleteBtn = document.getElementById('client-bulk-delete-btn');
    const bulkForm = document.getElementById('client-bulk-form');

    // Toggle all checkboxes
    selectAllCheckbox.addEventListener('change', function() {
        clientCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteButtonState();
    });

    // Update delete button state when any checkbox changes
    clientCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateDeleteButtonState();
            // Uncheck select-all if any individual checkbox is unchecked
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            }
        });
    });

    function updateDeleteButtonState() {
        const anyChecked = Array.from(clientCheckboxes).some(cb => cb.checked);
        deleteBtn.disabled = !anyChecked;
    }

    // Handle form submission with confirmation
    bulkForm.addEventListener('submit', function(e) {
        const anyChecked = Array.from(clientCheckboxes).some(cb => cb.checked);
        if (!anyChecked) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

@endsection