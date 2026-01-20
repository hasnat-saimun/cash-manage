@extends('include')
@section('backTitle')
New Source
@endsection

@section('bodyTitleFrist')
   New Source
@endsection
@section('bodyTitleEnd')
   <a href="{{ route('sourceView') }}">New Source</a>
@endsection
@section('bodyContent')
@php
    if(!empty($itemId)):
            $items       = \App\Models\source::find($itemId);
        if($items):
                $sourceName  = $items->source_name;
        endif;
    else:
            $itemId                 = null;
            $sourceName = '';
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
                        <h4 class="card-title">Source Details</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <button class="btn bg-primary text-white" data-bs-toggle="modal" data-bs-target="#addRate">
                            <i class="fas fa-plus me-1"></i> Add Source
                        </button>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end card-header-->
            <div class="card-body pt-0">
                <form id="source-bulk-form" method="POST" action="{{ route('sources.bulkDelete') }}" data-confirm-delete data-confirm-message="Delete the selected sources? This cannot be undone.">
                    @csrf
                    <div class="table-responsive">
                        <table class="table mb-0" id="datatable_1">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="source-select-all" class="form-check-input"></th>
                                    <th>SL</th>
                                    <th>Source Name</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @php
                                    $x = 1;
                                @endphp
                                @if(isset($sources))
                                    @foreach($sources as $key => $source)
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="{{ $source->id }}" class="form-check-input source-checkbox" form="source-bulk-form"></td>
                                            <td>{{ $x }}</td>
                                            <td>{{ $source->source_name }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('sourceEdit',['id'=>$source->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                                <form method="POST" action="{{ route('deleteSource',['id'=>$source->id]) }}" class="d-inline" data-confirm-delete data-confirm-message="Delete this source?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0"><i class="las la-trash-alt text-secondary fs-18"></i></button>
                                                </form>
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
                                    <td>Salary</td>
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
                        <button type="submit" form="source-bulk-form" id="source-bulk-delete-btn" class="btn btn-danger" disabled>
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
                        <h4 class="card-title">Update Source Detail</h4>
                    </div>
                    <!--end col-->
                    <div class="col-auto">
                        <a href="{{ route('sourceView') }}" class="btn btn-secondary ">Back</a>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <div class="card-body">
                <form action="{{route('updateSource')}}" method="POST">
                    @csrf        
                        <input type="hidden" name="itemId" value="{{ $itemId }}">
                    <div class="row">
                    <div class="col-6 mb-2">
                        <label for="sourceName">Source Name</label> 
                        <div class="input-group">     

                            <input type="text" class="form-control" placeholder="Enter The Source Name" aria-label="sourceName" name="sourceName" id="sourceName" value="{{ $sourceName }}">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('source-select-all');
    const sourceCheckboxes = document.querySelectorAll('.source-checkbox');
    const deleteBtn = document.getElementById('source-bulk-delete-btn');
    const bulkForm = document.getElementById('source-bulk-form');

    // Toggle all checkboxes
    selectAllCheckbox.addEventListener('change', function() {
        sourceCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteButtonState();
    });

    // Update delete button state when any checkbox changes
    sourceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateDeleteButtonState();
            // Uncheck select-all if any individual checkbox is unchecked
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            }
        });
    });

    function updateDeleteButtonState() {
        const anyChecked = Array.from(sourceCheckboxes).some(cb => cb.checked);
        deleteBtn.disabled = !anyChecked;
    }

    // Handle form submission with confirmation
    bulkForm.addEventListener('submit', function(e) {
        const anyChecked = Array.from(sourceCheckboxes).some(cb => cb.checked);
        if (!anyChecked) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

<!-- end page-wrapper -->
<div class="modal fade" id="addRate" tabindex="-1" aria-labelledby="addRateLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addRateLabel">Add Source Detail</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('saveSource')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class=" mb-2">
                        <label for="sourceName">Source Name</label> 
                        <div class="input-group">                      
                            <input type="text" class="form-control" placeholder="Enter The Source Name" aria-label="sourceName" name="sourceName" id="sourceName">
                        </div>
                    </div>         
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Add New Source</button>
                <button type="reset" class="btn btn-light w-100">Reset</button>
                </div>
            </form>
          </div>
        </div>
      </div>

@endsection