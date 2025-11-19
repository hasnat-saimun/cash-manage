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
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="datatable_1">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Name</th>
                                <th>Account Number</th>
                                <th>Registered</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                           
                            $x = 1;
                        @endphp
                        @if(!empty($allClient) && $allClient->count()>0)
                        @foreach($allClient as $client)
                            <tr>
                                <td>{{ $x }}</td>
                                <td class="">
                                    <div class="flex-grow-1 text-truncate">
                                        <h6 class="m-0">{{$client->client_name}}</h6>
                                    </div>
                                    <!--end media body-->
                                </td>
                                <td>{{$client->client_acNum}}</td>
                                <td>{{$client->client_regDate}}</td>
                                <td>{{$client->client_email}}</td>
                                <td>{{$client->client_phone}}</td>
                                <td><span class="badge rounded text-success bg-success-subtle">Active</span></td>
                                <td class="text-end">
                                    <a href="{{ route('clientEdit',['id'=>$client->id]) }}"><i class="las la-pen text-secondary fs-18"></i></a>
                                    <a href="#"><i class="las la-trash-alt text-secondary fs-18"></i></a>
                                </td>
                            </tr>
                        @php
                            $x++;
                        @endphp
                        @endforeach
                        @else
                         <tr>
                                <td>{{ $x }}</td>
                                <td class="d-flex align-items-center">
                                    <div class="flex-grow-1 text-truncate">
                                        <h6 class="m-0">Virtual It Professional</h6>
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
                        @endif
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
                <form action="{{ route('saveClient') }}" method="POST" >
                @csrf
                    <div class="mb-2">
                        <label for="fullName">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text" id="fullName"><i class="far fa-user"></i></span>
                            <input type="text" class="form-control" placeholder="Name" aria-label="FullName" name="fullName"  required />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="account">Account Number</label>
                        <div class="input-group">
                            <span class="input-group-text" id="account">A/c</span>
                            <input type="number" class="form-control" placeholder="Account number" aria-label="email"  name="acNumber" required/>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text" id="email"><i class="far fa-envelope"></i></span>
                            <input type="email" class="form-control" placeholder="Email address" aria-label="email" name="email"  requiredd/>
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
                                        name="registerDate" required
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
                </div>
            </form>
        </div>
    </div>
</div>


@php
if(!empty($itemId)):
        $items       = \App\Models\clientCreation::find($itemId);
        if(!empty($items)): 
            $fullName              = $items->client_name;
            $email              = $items->client_email   ;
            $mobileNo              = $items->client_phone;
            $acNumber              = $items->client_acNum;
            $registerDate              = $items->client_regDate;
        endif;
    else:
        $itemId                 = null;
        $fullName               = "";
        $email                  = "";
        $mobileNo               = "";
        $acNumber               = "";
        $registerDate           = "";
    endif;
@endphp
<div class="modal fade" id="editClient" tabindex="-1" aria-labelledby="editClientLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientLabel">Update Client Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('updateClient') }}" method="POST" >
                @csrf
                    <input type="hidden" name="itemId" value="{{ $itemId }}">
                    <div class="mb-2">
                        <label for="fullName">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text" id="fullName"><i class="far fa-user"></i></span>
                            <input type="text" class="form-control" placeholder="Name" aria-label="FullName" name="fullName" value="{{ $fullName }}" required />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="account">Account Number</label>
                        <div class="input-group">
                            <span class="input-group-text" id="account">A/c</span>
                            <input type="number" class="form-control" placeholder="Account number" aria-label="email"  name="acNumber" value="{{ $acNumber }}" required/>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text" id="email"><i class="far fa-envelope"></i></span>
                            <input type="email" class="form-control" placeholder="Email address" aria-label="email" name="email" value="{{ $email }}" requiredd/>
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
                                        value="{{ $registerDate }}" required
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
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection