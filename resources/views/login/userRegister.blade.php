<!doctype html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
    <head>
        <meta charset="utf-8" />
        <title>Register | Approx - Admin & Dashboard Template</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('/public/projectFile/home')}}/assets/images/favicon.ico" />

        <!-- App css -->
        <link href="{{asset('/public/projectFile/home')}}/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="{{asset('/public/projectFile/home')}}/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="{{asset('/public/projectFile/home')}}/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <div class="container-xxl">
            <div class="row vh-100 d-flex justify-content-center">
                <div class="col-12 align-self-center">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 mx-auto">
                                <div class="card">
                                    <div class="card-body p-0 bg-black auth-header-box rounded-top">
                                        <div class="text-center p-3">
                                            <a href="index.html" class="logo logo-admin">
                                                <img
                                                    src="{{asset('/public/projectFile/home')}}/assets/images/logo-sm.png"
                                                    height="50"
                                                    alt="logo"
                                                    class="auth-logo"
                                                />
                                            </a>
                                            <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Create an account</h4>
                                            <p class="text-muted fw-medium mb-0">
                                                Enter your detail to Create your account today.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <!-- show validation errors -->
                                        @if($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <form class="my-4" method="POST" action="{{ route('auth.register.post') }}">
                                            @csrf

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="name">Full name</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="name"
                                                    name="name"
                                                    value="{{ old('name') }}"
                                                    placeholder="Enter full name"
                                                    required
                                                />
                                            </div>
                                            <!--end form-group-->

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="email">Email</label>
                                                <input
                                                    type="email"
                                                    class="form-control"
                                                    id="email"
                                                    name="email"
                                                    value="{{ old('email') }}"
                                                    placeholder="Enter email"
                                                    required
                                                />
                                            </div>
                                            <!--end form-group-->

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="password">Password</label>
                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    id="password"
                                                    name="password"
                                                    placeholder="Enter password"
                                                    required
                                                />
                                            </div>
                                            <!--end form-group-->

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="password_confirmation">Confirm Password</label>
                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    id="password_confirmation"
                                                    name="password_confirmation"
                                                    placeholder="Confirm password"
                                                    required
                                                />
                                            </div>
                                            <!--end form-group-->

                                            <!-- first registered user becomes superAdmin -->
                                            <input type="hidden" name="role" value="superAdmin" />

                                            <div class="form-group mb-0 row">
                                                <div class="col-12">
                                                    <div class="d-grid mt-3">
                                                        <button class="btn btn-primary" type="submit">Register</button>
                                                    </div>
                                                </div>
                                                <!--end col-->
                                            </div>
                                            <!--end form-group-->
                                        </form>
                                        <!--end form-->
                                        <div class="text-center">
                                            <p class="text-muted">
                                                Already have an account ?
                                                <a href="{{ route('auth.loginForm') }}" class="text-primary ms-2">Log in</a>
                                            </p>
                                        </div>
                                    </div>
                                    <!--end card-body-->
                                </div>
                                <!--end card-->
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-body-->
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!-- container -->
    </body>
    <!--end body-->
</html>