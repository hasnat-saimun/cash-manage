<!doctype html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
    <head>
        <meta charset="utf-8" />
        @php
            $user = auth()->user();
            $businesses = $user ? ($user->businesses ?? collect()) : collect();
            $currentBizId = session('business_id');
            $businessName = optional($businesses->firstWhere('id', $currentBizId))->name
                ?? optional($businesses->first())->name
                ?? config('app.name');

            $sectionTitle = trim((string) $__env->yieldContent('backTitle'));
            $routeTitle = \Illuminate\Support\Facades\Route::currentRouteName();
            if ($routeTitle) {
                $routeTitle = str_replace(['.', '-'], [' ', ' '], $routeTitle);
                $routeTitle = preg_replace('/(?<!^)([A-Z])/', ' $1', $routeTitle);
                $routeTitle = trim(preg_replace('/\s+/', ' ', $routeTitle));
                $routeTitle = ucwords($routeTitle);
            }
            $pageTitle = trim(
                ($businessName ? $businessName.' | ' : '') .
                ($routeTitle ?: ($sectionTitle ?: 'Dashboard'))
            );
        @endphp
        <title>{{ $pageTitle }}</title>
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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/style.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

        <!-- page-specific styles -->
        @stack('styles')

        <!-- print styles: hide navigation / controls and show print-only content -->
        <style>
            .print-only{ display: none !important; }
            @media print {
                /* hide UI chrome */
                .startbar, .topbar, .footer, .startbar-overlay, .offcanvas, .no-print { display: none !important; }
                /* ensure content container uses full width */
                .page-content .container-fluid, .page-wrapper, .page-content { width: 100% !important; margin: 0; padding: 0; }
                /* show print-only blocks */
                .print-only { display: block !important; }
                /* adjust table font size for print */
                table { font-size: 12px; }
                /* avoid page breaks inside table rows */
                table tr, table td, table th { page-break-inside: avoid; }
            }
        </style>
    </head>

    <body>
        <!-- Top Bar Start -->
        <div class="topbar d-print-none">
            <div class="container-fluid">
                <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">
                    @php($user = auth()->user())
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    @php($currentBizId = session('business_id'))
                                    @php($currentBiz = $user && $currentBizId ? $user->businesses->firstWhere('id', $currentBizId) : null)
                                    @if($currentBiz)
                                        <div class="mt-1">Business: <strong>{{ $currentBiz->name }}</strong></div>
                                    @elseif($currentBizId)
                                        <div class="mt-1">Business ID: <strong>{{ $currentBizId }}</strong></div>
                                    @endif
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                        <li>
                            <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                                <i class="iconoir-menu"></i>
                            </button>
                        </li>
                        <li class="hide-phone app-search">
                            <form role="search" action="#" method="get">
                                <input type="search" name="search" class="form-control top-search mb-0" placeholder="Search here..." />
                                <button type="submit"><i class="iconoir-search"></i></button>
                            </form>
                        </li>
                    </ul>
                    <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                        @auth
                        <li class="me-2">
                            @include('partials.business-switcher')
                        </li>
                        @endauth
                        <li class="dropdown">
                            <a
                                class="nav-link dropdown-toggle arrow-none nav-icon"
                                data-bs-toggle="dropdown"
                                href="#"
                                role="button"
                                aria-haspopup="false"
                                aria-expanded="false"
                                data-bs-offset="0,19"
                            >
                                <img src="{{asset('/public/projectFile/home')}}/assets/images/flags/us_flag.jpg" alt="" class="thumb-sm rounded-circle" />
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#"
                                    ><img
                                        src="{{asset('/public/projectFile/home')}}/assets/images/flags/us_flag.jpg"
                                        alt=""
                                        height="15"
                                        class="me-2"
                                    />English</a
                                >
                                <a class="dropdown-item" href="#"
                                    ><img
                                        src="{{asset('/public/projectFile/home')}}/assets/images/flags/spain_flag.jpg"
                                        alt=""
                                        height="15"
                                        class="me-2"
                                    />Spanish</a
                                >
                                <a class="dropdown-item" href="#"
                                    ><img
                                        src="{{asset('/public/projectFile/home')}}/assets/images/flags/germany_flag.jpg"
                                        alt=""
                                        height="15"
                                        class="me-2"
                                    />German</a
                                >
                                <a class="dropdown-item" href="#"
                                    ><img
                                        src="{{asset('/public/projectFile/home')}}/assets/images/flags/french_flag.jpg"
                                        alt=""
                                        height="15"
                                        class="me-2"
                                    />French</a
                                >
                            </div>
                        </li>
                        <!--end topbar-language-->

                        <li class="topbar-item">
                            <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                                <i class="iconoir-half-moon dark-mode"></i>
                                <i class="iconoir-sun-light light-mode"></i>
                            </a>
                        </li>

                        @auth
                        @php($user = auth()->user())
                        @php($avatarUrl = asset('/public/projectFile/home/assets/images/users/avatar-1.jpg'))
                        @php(
                            $avatarUrl = ($user && $user->avatar)
                                ? (\Illuminate\Support\Str::startsWith($user->avatar, ['http://','https://'])
                                    ? $user->avatar
                                    : asset('public/storage/'.ltrim($user->avatar,'/')))
                                : $avatarUrl
                        )
                        <li class="dropdown topbar-item">
                            <a
                                class="nav-link dropdown-toggle arrow-none nav-icon"
                                data-bs-toggle="dropdown"
                                href="#"
                                role="button"
                                aria-haspopup="false"
                                aria-expanded="false"
                                data-bs-offset="0,19"
                            >
                                <i class="iconoir-bell"></i>
                                <span class="alert-badge"></span>
                            </a>
                            <div class="dropdown-menu stop dropdown-menu-end dropdown-lg py-0">
                                <h5
                                    class="dropdown-item-text m-0 py-3 d-flex justify-content-between align-items-center"
                                >
                                    Notifications
                                    <a href="#" class="badge text-body-tertiary badge-pill">
                                        <i class="iconoir-plus-circle fs-4"></i>
                                    </a>
                                </h5>
                                <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified mb-1" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a
                                            class="nav-link mx-0 active"
                                            data-bs-toggle="tab"
                                            href="#All"
                                            role="tab"
                                            aria-selected="true"
                                        >
                                            All
                                            <span class="badge bg-primary-subtle text-primary badge-pill ms-1">24</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a
                                            class="nav-link mx-0"
                                            data-bs-toggle="tab"
                                            href="#Projects"
                                            role="tab"
                                            aria-selected="false"
                                            tabindex="-1"
                                        >
                                            Projects
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a
                                            class="nav-link mx-0"
                                            data-bs-toggle="tab"
                                            href="#Teams"
                                            role="tab"
                                            aria-selected="false"
                                            tabindex="-1"
                                        >
                                            Team
                                        </a>
                                    </li>
                                </ul>
                                <div class="ms-0" style="max-height: 230px" data-simplebar>
                                    <div class="tab-content" id="myTabContent">
                                        <div
                                            class="tab-pane fade show active"
                                            id="All"
                                            role="tabpanel"
                                            aria-labelledby="all-tab"
                                            tabindex="0"
                                        >
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">2 min ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-wolf fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Your order is placed
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >Dummy text of the printing and industry.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">10 min ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-apple-swift fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Meeting with designers
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >It is a long established fact that a reader.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">40 min ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-birthday-cake fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            UX 3 Task complete.
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >Dummy text of the printing.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">1 hr ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-drone fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Your order is placed
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >It is a long established fact that a reader.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">2 hrs ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-user fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Payment Successfull
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >Dummy text of the printing.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                        </div>
                                        <div
                                            class="tab-pane fade"
                                            id="Projects"
                                            role="tabpanel"
                                            aria-labelledby="projects-tab"
                                            tabindex="0"
                                        >
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">40 min ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-birthday-cake fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            UX 3 Task complete.
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >Dummy text of the printing.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">1 hr ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-drone fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Your order is placed
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >It is a long established fact that a reader.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">2 hrs ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-user fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Payment Successfull
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >Dummy text of the printing.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                        </div>
                                        <div
                                            class="tab-pane fade"
                                            id="Teams"
                                            role="tabpanel"
                                            aria-labelledby="teams-tab"
                                            tabindex="0"
                                        >
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">1 hr ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-drone fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Your order is placed
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >It is a long established fact that a reader.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                            <!-- item-->
                                            <a href="#" class="dropdown-item py-3">
                                                <small class="float-end text-muted ps-2">2 hrs ago</small>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle"
                                                    >
                                                        <i class="iconoir-user fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 text-truncate">
                                                        <h6 class="my-0 fw-normal text-dark fs-13">
                                                            Payment Successfull
                                                        </h6>
                                                        <small class="text-muted mb-0"
                                                            >Dummy text of the printing.</small
                                                        >
                                                    </div>
                                                    <!--end media-body-->
                                                </div>
                                                <!--end media--> </a
                                            ><!--end-item-->
                                        </div>
                                    </div>
                                </div>
                                <!-- All-->
                                <a
                                    href="pages-notifications.html"
                                    class="dropdown-item text-center text-dark fs-13 py-2"
                                >
                                    View All <i class="fi-arrow-right"></i>
                                </a>
                            </div>
                        </li>

                        <li class="dropdown topbar-item">
                            <a
                                class="nav-link dropdown-toggle arrow-none nav-icon"
                                data-bs-toggle="dropdown"
                                href="#"
                                role="button"
                                aria-haspopup="false"
                                aria-expanded="false"
                                data-bs-offset="0,19"
                            >
                                <img src="{{ $avatarUrl }}" alt="" class="thumb-md rounded-circle" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-end py-0">
                                <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $avatarUrl }}" alt="" class="thumb-md rounded-circle" />
                                    </div>
                                    <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                        <h6 class="my-0 fw-medium text-dark fs-13">{{ $user ? $user->name : 'User' }}</h6>
                                        <small class="text-muted mb-0">{{ $user ? $user->email : '' }}</small>
                                    </div>
                                    <!--end media-body-->
                                </div>
                                <div class="dropdown-divider mt-0"></div>
                                <small class="text-muted px-2 pb-1 d-block">Account</small>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="las la-user fs-18 me-1 align-text-bottom"></i> Profile
                                </a>
                                <a class="dropdown-item" href="pages-faq.html"
                                    ><i class="las la-wallet fs-18 me-1 align-text-bottom"></i> Earning</a
                                >
                                <small class="text-muted px-2 py-1 d-block">Settings</small>
                                <a class="dropdown-item" href="pages-profile.html"
                                    ><i class="las la-cog fs-18 me-1 align-text-bottom"></i>Account Settings</a
                                >
                                <a class="dropdown-item" href="pages-profile.html"
                                    ><i class="las la-lock fs-18 me-1 align-text-bottom"></i> Security</a
                                >
                                <a class="dropdown-item" href="pages-faq.html"
                                    ><i class="las la-question-circle fs-18 me-1 align-text-bottom"></i> Help Center</a
                                >
                                <div class="dropdown-divider mb-0"></div>
                                <form method="POST" action="{{ route('auth.logout') }}" class="px-2 py-2">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </li>
                        @endauth
                    </ul>
                    <!--end topbar-nav-->
                </nav>
                <!-- end navbar-->
            </div>
        </div>
        <!-- Top Bar End -->
        <!-- leftbar-tab-menu -->
        <div class="startbar d-print-none">
            <!--start brand-->
            <div class="brand">
                @php($siteLogo = \App\Models\Config::get('sidebar_logo_path') ?: \App\Models\Config::get('logo_path'))
                @php($siteName = \App\Models\Config::get('site_name','Cash Calculas'))
                <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
                    <span class="flex-column text-center">
                        @if(!empty($siteLogo))
                            <img src="{{ asset('public/storage/'.$siteLogo) }}" alt="logo" class="w-50" />
                        @else
                            <img src="{{asset('/public/projectFile/home')}}/assets/images/logo-sm.png" alt="logo-small" class="logo-sm" />
                        @endif
                    </span>
                </a>
            </div>
            <!--end brand-->
            <!--start startbar-menu-->
            <div class="startbar-menu">
                <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                    @auth
                    @php($currentBizId = session('business_id'))
                    @php($currentBiz = auth()->user()->businesses->firstWhere('id', $currentBizId))
                    <div class="px-3 py-2 mb-2 border-bottom small text-muted d-flex align-items-center justify-content-between">
                        <span>
                            Current Business:
                            <strong>{{ $currentBiz ? $currentBiz->name : ($currentBizId ?? 'none') }}</strong>
                        </span>
                        <a href="{{ route('business.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    @endauth
                    <div class="d-flex align-items-start flex-column w-100">
                        <!-- Navigation -->
                        <ul class="navbar-nav mb-auto w-100">
                            <li class="menu-label mt-2">
                                <span>Main</span>
                            </li>

                            @php($canDashboard = auth()->check() ? auth()->user()->hasPermission('dashboard.view') : false)
                            @if($canDashboard)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboardView') }}">
                                    <i class="iconoir-report-columns menu-icon"></i>
                                    <span>Dashboard</span>
                                    <span class="badge text-bg-info ms-auto">New</span>
                                </a>
                            </li>
                            @endif
                            <!-- Mobile Banking -->
                            @php($canMobile = auth()->check() ? (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('mobile.manage')) : false)
                            @if($canMobile)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('mobile.index') }}">
                                    <i class="iconoir-smartphone-device menu-icon"></i>
                                    <span>Mobile Banking</span>
                                </a>
                            </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('mobile.providers.index') }}">
                                        <i class="iconoir-smartphone-device menu-icon"></i>
                                        <span>Mobile Providers</span>
                                    </a>
                                </li>
                            @endif
                            <!--end nav-item-->
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('bankAccountCreationView')}}">
                                    <i class="iconoir-credit-cards menu-icon"></i>
                                    <span>Bank account</span>
                                    <span class="badge text-bg-pink ms-auto">03</span>
                                </a>
                            @php(
                                $canBankTxView = auth()->check() ? auth()->user()->hasPermission('bank.transactions.view') : false
                            )
                            @php(
                                $canBankTxCreate = auth()->check() ? auth()->user()->hasPermission('bank.transactions.create') : false
                            )
                            @php(
                                $canBankReport = auth()->check() ? auth()->user()->hasPermission('reports.bank') : false
                            )
                            @if($canBankTxView || $canBankTxCreate || $canBankReport)
                            <li class="nav-item">
                                <a class="nav-link" href="#sidebarBankTransactions" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBankTransactions">
                                    <i class="iconoir-task-list menu-icon"></i>
                                    <span>Bank Transactions</span>
                                </a>
                                <div class="collapse" id="sidebarBankTransactions">
                                    <ul class="nav flex-column">
                                        @if($canBankTxView)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('bankTransactionList') }}">Overview</a>
                                        </li>
                                        @endif
                                        @if($canBankTxCreate)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('bankTransactionCreation') }}">Add Transactions</a>
                                        </li>
                                        @endif
                                        @if($canBankReport)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('reports.bankTransaction') }}">Transactions Report</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @endif
                            <!--end nav-item-->
                            @php($canClients = auth()->check() ? auth()->user()->hasPermission('clients.manage') : false)
                            @if($canClients)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('clientCreation') }}">
                                    <i class="iconoir-community menu-icon"></i>
                                    <span>Contact List</span>
                                </a>
                            </li>
                            @endif
                            <!--end nav-item-->
                            @php(
                                $canClientTxView = auth()->check() ? auth()->user()->hasPermission('transactions.view') : false
                            )
                            @php(
                                $canClientTxCreate = auth()->check() ? auth()->user()->hasPermission('transactions.create') : false
                            )
                            @php(
                                $canClientReport = auth()->check() ? auth()->user()->hasPermission('reports.client') : false
                            )
                            @if($canClientTxView || $canClientTxCreate || $canClientReport)
                            <li class="nav-item">
                                <a class="nav-link" href="#sidebarTransactions" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTransactions">
                                    <i class="iconoir-task-list menu-icon"></i>
                                    <span>Client Transactions</span>
                                </a>
                                <div class="collapse" id="sidebarTransactions">
                                    <ul class="nav flex-column">
                                        @if($canClientTxView)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('transactionList') }}">Overview</a>
                                        </li>
                                        @endif
                                        @if($canClientTxCreate)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('transactionCreation') }}">Add Transactions</a>
                                        </li>
                                        @endif
                                        @if($canClientReport)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('reports.clientTransaction') }}">Transaction Report</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @endif
                            <!--end nav-item-->
                            @php($canSource = auth()->check() ? auth()->user()->hasPermission('source.manage') : false)
                            @if($canSource)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sourceView') }}">
                                    <i class="iconoir-plug-type-l menu-icon"></i>
                                    <span>Source</span>
                                </a>
                            </li>
                            @endif
                            <!--end nav-item-->
                            @php($canBankManage = auth()->check() ? auth()->user()->hasPermission('bank.manage') : false)
                            @if($canBankManage)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('bankManageView') }}">
                                    <i class="iconoir-plug-type-l menu-icon"></i>
                                    <span>Bank Manage</span>
                                </a>
                            </li>
                            @endif
                            <!-- Business Management -->
                            @php($canBizManage = auth()->check() ? auth()->user()->hasPermission('business.manage') : false)
                            @if($canBizManage)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('business.index') }}">
                                    <i class="iconoir-building menu-icon"></i>
                                    <span>Business Management</span>
                                </a>
                            </li>
                            @endif
                            <!-- Admin Users -->
                            @php($canAdminUsers = auth()->check() ? (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('admin.users')) : false)
                            @if($canAdminUsers)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users.index') }}">
                                    <i class="iconoir-user-cog menu-icon"></i>
                                    <span>Admin</span>
                                </a>
                            </li>
                            @endif
                            <!-- Capital Account -->
                            @php($canCapital = auth()->check() ? (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('reports.capital')) : false)
                            @if($canCapital)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('reports.capitalAccount') }}">
                                    <i class="iconoir-wallet menu-icon"></i>
                                    <span>Capital Account</span>
                                </a>
                            </li>
                            @endif
                            <!-- Settings -->
                            @php($canSettings = auth()->check() ? (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('settings.manage')) : false)
                            @if($canSettings)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('settings.index') }}">
                                    <i class="iconoir-settings menu-icon"></i>
                                    <span>Settings</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                        <!--end navbar-nav--->
                        <div class="update-msg text-center">
                            <div
                                class="d-flex justify-content-center align-items-center thumb-lg update-icon-box rounded-circle mx-auto"
                            >
                                <!-- <i class="iconoir-peace-hand h3 align-self-center mb-0 text-primary"></i> -->
                                <img src="{{asset('/public/projectFile/home')}}/assets/images/extra/gold.png" alt="" class="" height="45" />
                            </div>
                            <h5 class="mt-3">Today's <span class="text-white">$2450.00</span></h5>
                            <p class="mb-3 text-muted">Today's best Investment for you.</p>
                            <a href="javascript: void(0);" class="btn text-primary shadow-sm rounded-pill px-3"
                                >Invest Now</a
                            >
                        </div>
                    </div>
                </div>
                <!--end startbar-collapse-->
            </div>
            <!--end startbar-menu-->
        </div>
        <!--end startbar-->
        <div class="startbar-overlay d-print-none"></div>
        <!-- end leftbar-tab-menu-->

        <div class="page-wrapper">
            <!-- Page Content-->
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                                <h4 class="page-title">@yield('bodyTitleFrist')</h4>
                                <div class="">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Cash Calculas</a></li>
                                        <!--end nav-item-->
                                        <li class="breadcrumb-item active">@yield('bodyTitleEnd')</li>
                                    </ol>
                                </div>
                            </div>
                            <!--end page-title-box-->
                        </div>
                        <!--end col-->
                    </div>
                   <div class="row justify-content-center">
                        @yield('bodyContent')
                   </div>
                    <!--end row-->
                </div>
                <!-- container -->

                <!--Start Rightbar-->
                <!--Start Rightbar/offcanvas-->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="Appearance" aria-labelledby="AppearanceLabel">
                    <div class="offcanvas-header border-bottom justify-content-between">
                        <h5 class="m-0 font-14" id="AppearanceLabel">Appearance</h5>
                        <button
                            type="button"
                            class="btn-close text-reset p-0 m-0 align-self-center"
                            data-bs-dismiss="offcanvas"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="offcanvas-body">
                        <h6>Account Settings</h6>
                        <div class="p-2 text-start mt-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="settings-switch1" />
                                <label class="form-check-label" for="settings-switch1">Auto updates</label>
                            </div>
                            <!--end form-switch-->
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="settings-switch2" checked />
                                <label class="form-check-label" for="settings-switch2">Location Permission</label>
                            </div>
                            <!--end form-switch-->
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="settings-switch3" />
                                <label class="form-check-label" for="settings-switch3">Show offline Contacts</label>
                            </div>
                            <!--end form-switch-->
                        </div>
                        <!--end /div-->
                        <h6>General Settings</h6>
                        <div class="p-2 text-start mt-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="settings-switch4" />
                                <label class="form-check-label" for="settings-switch4">Show me Online</label>
                            </div>
                            <!--end form-switch-->
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="settings-switch5" checked />
                                <label class="form-check-label" for="settings-switch5">Status visible to all</label>
                            </div>
                            <!--end form-switch-->
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="settings-switch6" />
                                <label class="form-check-label" for="settings-switch6">Notifications Popup</label>
                            </div>
                            <!--end form-switch-->
                        </div>
                        <!--end /div-->
                    </div>
                    <!--end offcanvas-body-->
                </div>
                <!--end Rightbar/offcanvas-->
                <!--end Rightbar-->
                <!--Start Footer-->

                <footer class="footer text-center text-sm-start d-print-none">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-0 rounded-bottom-0">
                                    <div class="card-body">
                                        <p class="text-muted mb-0">
                                            © 
                                            <script>
                                                document.write(new Date().getFullYear());
                                            </script>
                                            {{ session('business_name', 'Virtual IT Professional') }}
                                            <span class="text-muted d-none d-sm-inline-block float-end">
                                                Developed by
                                                <a href="https://www.virtualitprofessional.com" target="_blank" class="text-primary text-decoration-none">Virtual IT Professional</a>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>

                <!--end footer-->
            </div>
            <!-- end page content -->
        </div>
        <!-- end page-wrapper -->

        <!-- Global delete confirmation modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" data-confirm-message>Are you sure you want to delete this record?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" data-confirm-yes>Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Javascript  -->
        <!-- vendor js -->

        <script src="{{asset('/public/projectFile/home')}}/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="{{asset('/public/projectFile/home')}}/assets/libs/simplebar/simplebar.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/umd/simple-datatables.js"></script>

        <!-- Centralized delete confirmation for all DELETE forms -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalEl = document.getElementById('confirmDeleteModal');
                if (!modalEl || !window.bootstrap) return;

                var modal = new bootstrap.Modal(modalEl);
                var messageEl = modalEl.querySelector('[data-confirm-message]');
                var confirmBtn = modalEl.querySelector('[data-confirm-yes]');
                var pendingForm = null;
                var pendingAction = null;

                document.addEventListener('submit', function(e) {
                    var form = e.target;
                    if (!(form instanceof HTMLFormElement)) return;

                    var methodInput = form.querySelector('input[name="_method"]');
                    var isDeleteMethod = methodInput && methodInput.value && methodInput.value.toUpperCase() === 'DELETE';
                    var hasExplicitFlag = form.dataset.confirmDelete !== undefined;
                    if (!isDeleteMethod && !hasExplicitFlag) return;

                    if (form.dataset.confirming === 'yes') return; // already confirmed

                    e.preventDefault();
                    pendingForm = form;
                    pendingAction = function() {
                        var formToSubmit = pendingForm;
                        pendingForm = null;
                        pendingAction = null;
                        delete formToSubmit.dataset.confirming;
                        formToSubmit.submit();
                    };
                    form.dataset.confirming = 'yes';
                    var customMsg = form.dataset.confirmMessage;
                    if (customMsg && messageEl) messageEl.textContent = customMsg;
                    else if (messageEl) messageEl.textContent = 'Are you sure you want to delete this record?';
                    modal.show();
                });

                document.addEventListener('click', function(e) {
                    var trigger = e.target.closest('[data-confirm-delete]');
                    if (!trigger) return;
                    if (trigger.tagName === 'FORM') return; // submit handler covers forms
                    e.preventDefault();
                    var customMsg = trigger.dataset.confirmMessage;
                    if (customMsg && messageEl) messageEl.textContent = customMsg;
                    else if (messageEl) messageEl.textContent = 'Are you sure you want to delete this record?';
                    pendingForm = null;
                    pendingAction = function() {
                        pendingAction = null;
                        var href = trigger.getAttribute('href');
                        if (href) window.location.href = href;
                    };
                    modal.show();
                });

                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        modal.hide();
                        if (pendingAction) {
                            var action = pendingAction;
                            pendingAction = null;
                            action();
                        } else if (pendingForm) {
                            var formToSubmit = pendingForm;
                            pendingForm = null;
                            delete formToSubmit.dataset.confirming;
                            formToSubmit.submit();
                        }
                    });
                }

                modalEl.addEventListener('hidden.bs.modal', function() {
                    if (pendingForm) {
                        delete pendingForm.dataset.confirming;
                        pendingForm = null;
                    }
                    pendingAction = null;
                });
            });
        </script>

        <!-- Auto-enable simple data tables on every table (unless opted-out) -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (!window.simpleDatatables || !window.simpleDatatables.DataTable) return;
                var tables = document.querySelectorAll('table.table:not([data-no-datatable])');
                tables.forEach(function(tbl) {
                    if (tbl.dataset.datatableInit === 'yes') return;
                    tbl.dataset.datatableInit = 'yes';
                    try {
                        new simpleDatatables.DataTable(tbl, {
                            perPage: 10,
                            perPageSelect: [10, 25, 50, 100],
                            searchable: true,
                            labels: {
                                placeholder: 'Search...',
                                perPage: 'rows per page',
                                noRows: 'No matching records',
                                info: 'Showing {start} to {end} of {rows} entries'
                            }
                        });
                    } catch (err) {
                        console.error('DataTable init failed:', err);
                    }
                });
            });
        </script>

        <!-- Auto-dismiss alerts after a few seconds -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var AUTO_DISMISS_MS = 6000;
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(el) {
                    setTimeout(function() {
                        try {
                            if (window.bootstrap && window.bootstrap.Alert) {
                                var inst = window.bootstrap.Alert.getOrCreateInstance(el);
                                inst.close();
                            } else {
                                // Fallback: fade then remove
                                el.classList.add('fade');
                                el.classList.remove('show');
                                setTimeout(function(){
                                    if (el && el.parentNode) el.parentNode.removeChild(el);
                                }, 400);
                            }
                        } catch (e) {
                            el.style.display = 'none';
                        }
                    }, AUTO_DISMISS_MS);
                });
            });
        </script>

        <script src="{{asset('/public/projectFile/home')}}/assets/libs/apexcharts/apexcharts.min.js"></script>
        <script src="{{asset('/public/projectFile')}}/apexcharts.com/samples/assets/stock-prices.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- page-specific scripts (push index.init.js only on pages that include chart placeholders) -->
        @stack('pageScripts')
        <script src="{{asset('/public/projectFile/home')}}/assets/js/DynamicSelect.js"></script>
        <script src="{{asset('/public/projectFile/home')}}/assets/js/app.js"></script>

        {{-- page-specific inline scripts (e.g. @push('scripts') from views) --}}
        @stack('scripts')
    </body>
    <!--end body-->
</html>
