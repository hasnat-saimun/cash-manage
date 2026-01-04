<!doctype html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
    <!-- Mirrored from mannatthemes.com/approx/default/auth-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 17 Nov 2025 09:06:57 GMT -->
    <head>
        <meta charset="utf-8" />
        @php
            $appName = config('app.name', 'Cash Manage');
            $routeTitle = \Illuminate\Support\Facades\Route::currentRouteName();
            if ($routeTitle) {
                $routeTitle = str_replace(['.', '-'], [' ', ' '], $routeTitle);
                $routeTitle = preg_replace('/(?<!^)([A-Z])/', ' $1', $routeTitle);
                $routeTitle = trim(preg_replace('/\s+/', ' ', $routeTitle));
                $routeTitle = ucwords($routeTitle);
            }
            $routeTitle = $routeTitle ?: 'Login';
        @endphp
        <title>{{ $appName }} | {{ $routeTitle }}</title>
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

    <!-- Top Bar Start -->
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
                                            <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">
                                                Welcome Back
                                            </h4>
                                            <p class="text-muted fw-medium mb-0">Sign in to continue.</p>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        @if(!empty($setupMode))
                                            <div class="alert alert-info">
                                                No users or businesses exist yet. Create the first admin and business to get started.
                                            </div>
                                            <form class="my-3" method="POST" action="{{ route('auth.register.post') }}">
                                                @csrf
                                                <input type="hidden" name="role" value="superAdmin" />
                                                <div class="form-group mb-2">
                                                    <label class="form-label" for="setup_name">Admin Name</label>
                                                    <input type="text" class="form-control" id="setup_name" name="name" value="{{ old('name') }}" required placeholder="Enter name" />
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="form-label" for="setup_email">Admin Email</label>
                                                    <input type="email" class="form-control" id="setup_email" name="email" value="{{ old('email') }}" required placeholder="Enter email" />
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="form-label" for="setup_password">Password</label>
                                                    <input type="password" class="form-control" id="setup_password" name="password" required placeholder="Enter password" />
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="form-label" for="setup_password_confirmation">Confirm Password</label>
                                                    <input type="password" class="form-control" id="setup_password_confirmation" name="password_confirmation" required placeholder="Confirm password" />
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="form-label" for="setup_business_name">Business Name</label>
                                                    <input type="text" class="form-control" id="setup_business_name" name="business_name" value="{{ old('business_name', 'My Business') }}" required placeholder="Enter business name" />
                                                </div>
                                                <div class="d-grid mb-4">
                                                    <button class="btn btn-success" type="submit">Create Admin & Business</button>
                                                </div>
                                            </form>
                                            <hr class="my-4" />
                                        @endif

                                        <form class="my-4" method="POST" action="{{ route('auth.login') }}">
                                            @csrf
                                            <div class="form-group mb-2">
                                                <label class="form-label" for="email">Email</label>
                                                <input
                                                    type="email"
                                                    class="form-control"
                                                    id="email"
                                                    name="email"
                                                    value="{{ old('email') }}"
                                                    required
                                                    placeholder="Enter email"
                                                />
                                            </div>
                                            <!--end form-group-->

                                            <div class="form-group">
                                                <label class="form-label" for="password">Password</label>
                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    name="password"
                                                    id="password"
                                                    placeholder="Enter password"
                                                    required
                                                />
                                            </div>
                                            <!--end form-group-->

                                            <div class="form-group row mt-3">
                                                <div class="col-sm-6">
                                                    <div class="form-check form-switch form-switch-success">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            id="remember"
                                                            name="remember"
                                                        />
                                                        <label class="form-check-label" for="remember"
                                                            >Remember me</label
                                                        >
                                                    </div>
                                                </div>
                                                <!--end col-->
                                                <div class="col-sm-6 text-end">
                                                    @if (Route::has('password.request'))
                                                        <a href="{{ route('password.request') }}" class="text-muted font-13">
                                                            <i class="dripicons-lock"></i> Forgot password?
                                                        </a>
                                                    @endif
                                                </div>
                                                <!--end col-->
                                            </div>
                                            <!--end form-group-->

                                            <div class="form-group mb-0 row">
                                                <div class="col-12">
                                                    <div class="d-grid mt-3">
                                                        <button class="btn btn-primary" type="submit">
                                                            Log In <i class="fas fa-sign-in-alt ms-1"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <!--end col-->
                                            </div>
                                            <!--end form-group-->
                                        </form>
                                        <!--end form-->

                                        <div class="text-center mb-2">
                                            <p class="text-muted">Need an account? Contact your administrator.</p>
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
