@extends('include')

@section('bodyTitleFrist')
Business Management
@endsection

@section('bodyTitleEnd')
Manage businesses
@endsection

@section('bodyContent')
<div class="container py-3">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-4">
    <div class="card-header">Current Business</div>
    <div class="card-body">
      <p class="mb-0">Business ID in session: <strong>{{ $currentId ?? 'none' }}</strong></p>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">Your Businesses</div>
    <div class="card-body">
      <form class="row g-2" method="POST" action="{{ route('business.switch') }}">
        @csrf
        <div class="col-12 col-md-6">
          <select name="business_id" class="form-select">
            @foreach($businesses as $biz)
              <option value="{{ $biz->id }}" {{ empty($currentId) ? ($loop->first ? 'selected' : '') : (($currentId==$biz->id)?'selected':'') }}>{{ $biz->name }} (ID: {{ $biz->id }})</option>
            @endforeach
          </select>
        </div>
        <div class="col">
          <button type="submit" class="btn btn-primary">Switch</button>
        </div>
      </form>

      <form class="row g-2 mt-3" method="POST" action="{{ route('business.update') }}">
        @csrf
        @method('PATCH')
        <div class="col-12 col-md-4">
          <select name="business_id" class="form-select">
            @foreach($businesses as $biz)
              <option value="{{ $biz->id }}">{{ $biz->name }} (ID: {{ $biz->id }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-5">
          <input type="text" name="name" class="form-control" placeholder="New business name" required />
        </div>
        <div class="col">
          <button type="submit" class="btn btn-warning">Rename</button>
        </div>
      </form>

      <div class="row g-2 mt-3">
        <div class="col-12 col-md-4">
          <select id="delete_business_id" class="form-select">
            @foreach($businesses as $biz)
              <option value="{{ $biz->id }}">{{ $biz->name }} (ID: {{ $biz->id }})</option>
            @endforeach
          </select>
        </div>
        <div class="col">
          <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Business</button>
        </div>
      </div>

      <form id="deleteForm" method="POST" action="{{ route('business.destroy') }}" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="business_id" id="delete_form_business_id" />
        <input type="hidden" name="delete_type" id="delete_type" />
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Create New Business</div>
    <div class="card-body">
      <form class="row g-2" method="POST" action="{{ route('business.store') }}">
        @csrf
        <div class="col-12 col-md-6">
          <input type="text" name="name" class="form-control" placeholder="Business name" required />
        </div>
        <div class="col">
          <button type="submit" class="btn btn-success">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDelete() {
    const businessId = document.getElementById('delete_business_id').value;
    const businessName = document.getElementById('delete_business_id').options[document.getElementById('delete_business_id').selectedIndex].text;
    
    if (!businessId) {
        Swal.fire({
            icon: 'warning',
            title: 'No Business Selected',
            text: 'Please select a business to delete',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    Swal.fire({
        title: 'Delete Business',
        html: `You are about to delete: <strong>"${businessName}"</strong><br><br>
               Please choose deletion type:`,
        icon: 'question',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Delete All Data',
        denyButtonText: '<i class="fas fa-building"></i> Delete Business Only',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        denyButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        customClass: {
            confirmButton: 'btn btn-danger mx-1',
            denyButton: 'btn btn-warning mx-1',
            cancelButton: 'btn btn-secondary mx-1'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Option 1: Full delete
            Swal.fire({
                title: 'Are you absolutely sure?',
                html: `This will <strong>permanently delete</strong> the business and <strong>ALL related data</strong> including:<br>
                       <ul style="text-align: left; display: inline-block;">
                         <li>All clients</li>
                         <li>All transactions</li>
                         <li>All bank accounts</li>
                         <li>All mobile accounts</li>
                         <li>All balances and records</li>
                       </ul><br>
                       <strong style="color: #d33;">This action cannot be undone!</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete Everything',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    document.getElementById('delete_form_business_id').value = businessId;
                    document.getElementById('delete_type').value = 'full';
                    document.getElementById('deleteForm').submit();
                }
            });
        } else if (result.isDenied) {
            // Option 2: Business only delete
            Swal.fire({
                title: 'Delete Business Only?',
                html: `This will delete the business record but <strong>keep all data</strong>:<br>
                       <ul style="text-align: left; display: inline-block;">
                         <li>Clients will be preserved</li>
                         <li>Transactions will be preserved</li>
                         <li>Bank accounts will be preserved</li>
                         <li>All data remains accessible</li>
                       </ul>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete Business Only',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#6c757d'
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    document.getElementById('delete_form_business_id').value = businessId;
                    document.getElementById('delete_type').value = 'business_only';
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    });
}
</script>
@endsection
