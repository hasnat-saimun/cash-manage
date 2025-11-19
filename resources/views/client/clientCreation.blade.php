 @extends('include')
@section('backTitle')
Clint
@endsection
@section('bodyTitle')
Client
@endsection
@section('bodyContent')
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
                <div class="table-responsive">
                    <table class="table mb-0" id="datatable_1">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile No</th>
                                <th>Registered On</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="d-flex align-items-center">
                                    <div class="flex-grow-1 text-truncate">
                                        <h6 class="m-0">Unity Pugh</h6>
                                    </div>
                                    <!--end media body-->
                                </td>
                                <td><a href="#" class="text-body text-decoration-underline">dummy@gmail.com</a></td>
                                <td>+1 234 567 890</td>
                                <td>22 August 2024</td>
                                <td><span class="badge rounded text-success bg-success-subtle">Active</span></td>
                                <td class="text-end">
                                    <a href="#"><i class="las la-pen text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end col -->
</div>
<!-- end row -->

<!-- end page-wrapper -->
<div class="modal fade" id="addClient" tabindex="-1" aria-labelledby="addClientLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientLabel">Add Client Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label for="fullName">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text" id="fullName"><i class="far fa-user"></i></span>
                        <input type="text" class="form-control" placeholder="Name" aria-label="FullName" />
                    </div>
                </div>
                <div class="mb-2">
                    <label for="account">Account Number</label>
                    <div class="input-group">
                        <span class="input-group-text" id="account">A/c</span>
                        <input type="number" class="form-control" placeholder="Account number" aria-label="email" />
                    </div>
                </div>
                <div class="mb-2">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <span class="input-group-text" id="email"><i class="far fa-envelope"></i></span>
                        <input type="email" class="form-control" placeholder="Email address" aria-label="email" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="ragisterDate">Register Date</label>
                            <div class="input-group">
                                <span class="input-group-text" id="ragisterDate"><i class="far fa-calendar"></i></span>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="00/2024"
                                    aria-label="ragisterDate"
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
                                />
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100">Add Client</button>
            </div>
        </div>
    </div>
</div>

@endsection