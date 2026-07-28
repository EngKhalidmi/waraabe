<div class="header">
    <div class="header-left active" style="padding-bottom:50px; margin-top:25px">
        <a href="{{ route('dashboard') }}" class="logo">
            <img style="width: 130px; " height="auto" src="{{ asset('Logo/Logo1.png') }}" alt="">
        </a>
        <a href="{{ route('dashboard') }}" class="logo-small">
            <img  src="{{ asset('Logo/Logo1.png') }}" style="width: 130px; " height="auto" alt="">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
        </a>


    </div>
    <style>
        /* hide .school-info in mobile mood */
        @media (max-width: 991px) {

            .school-logo,
            .school-details {
                display: none !important;
            }
        }
    </style>
    <div class="school-info" style="display: flex; align-items: center; padding: 10px 10px;">
        <div class="school-logo" style="margin-left: 0px; margin-right: 10px;">
            <!--<img src="{{ asset('Logo/maal.png') }}" alt="Saacid"-->
            <!--    style="max-width: 50px; height: auto; border-radius: 50%; object-fit: cover;">-->
        </div>
        <div class="school-details  ml-2" style="flex: 1;">
            <h5 class="school-name"
                style="font-family:'SF Ui Text', sans-serif; font-weight: 700; color: #333333; margin-bottom: 0px; text-transform: uppercase;">
              
            </h5>
            <p class="school-location"
                style="font-family:'JetBrains Mono', sans-serif; font-weight: 400; color: #666666; padding-bottom: 5px;">
              
            </p>
        </div>


        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>



        <!-- Advanced Styling -->
        {{-- <style>
    /* Notification Bell Pulse Effect */
    .pulse-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 12px;
        height: 12px;
        background-color: #ff4d4d;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(255, 77, 77, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(255, 77, 77, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 77, 77, 0);
        }
    }

    /* Advanced Notification Dropdown */
    .notifications-dropdown {
        width: 300px;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        padding: 10px 0;
    }

    .notifications-dropdown .dropdown-header {
        font-weight: 600;
        padding: 10px 15px;
        border-bottom: 1px solid #eaeaea;
        color: #333;
    }

    .notifications-dropdown .notification-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        transition: background-color 0.3s;
    }

    .notifications-dropdown .notification-item:hover {
        background-color: #f9f9f9;
    }

    .notifications-dropdown .icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        margin-right: 10px;
    }

    .bg-gradient-info {
        background: linear-gradient(45deg, #36d1dc, #5b86e5);
    }

    .bg-gradient-success {
        background: linear-gradient(45deg, #11998e, #38ef7d);
    }

    .bg-gradient-warning {
        background: linear-gradient(45deg, #f7971e, #ffd200);
    }

    .notification-text p {
        margin: 0;
        font-size: 14px;
        font-weight: 500;
        color: #333;
    }

    .notification-text small {
        color: #999;
    }

    .dropdown-footer a {
        display: block;
        padding: 10px;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }

    .dropdown-footer a:hover {
        background-color: #f1f1f1;
    }

    .mark-all-read {
        font-size: 12px;
        color: #007bff;
        cursor: pointer;
    }

    .animate__animated.animate__fadeIn {
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style> --}}



        <ul class="nav user-menu">

            <!-- Advanced Notification Bell with Badge -->
            {{-- <li class="nav-item dropdown">
    <a href="javascript:void(0);" class="nav-link userset" data-bs-toggle="dropdown">
        <span class="user-img position-relative">
            <i class="fas fa-bell fa-lg"></i>
            <span class="pulse-badge"></span>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-end notifications-dropdown animate__animated animate__fadeIn">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <a href="#" class="mark-all-read">Mark all as read</a>
        </div>
        <div class="notification-item">
            <div class="icon-wrapper bg-gradient-info">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="notification-text">
                <p>New features are launching soon!</p>
                <small>5 mins ago</small>
            </div>
        </div>
        <div class="notification-item">
            <div class="icon-wrapper bg-gradient-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="notification-text">
                <p>System update completed successfully.</p>
                <small>1 hour ago</small>
            </div>
        </div>
        <div class="notification-item">
            <div class="icon-wrapper bg-gradient-warning">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="notification-text">
                <p>New product launch announced!</p>
                <small>2 hours ago</small>
            </div>
        </div>
        <div class="dropdown-footer text-center">
            <a href="#">View All Notifications</a>
        </div>
    </div>
</li>  --}}

            @if (auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager')
                <a href="{{ route('products.add') }}" class=" nav-link userset">
                    <span class="user-img"><i class="fas fa-cart-plus"></i> <span class="mr-2"></span>
                    </span>
                </a>
            @endif



            @if (auth()->user()->role === 'admin' || auth()->user()->role === 'branch-manager')
                <a href="{{ route('sales.add') }}" class=" nav-link userset">
                    <span class="user-img"><i class="fas fa-dollar-sign"></i> <span class="mr-2"></span>
                    </span>
                </a>
            @endif

            <li class="nav-item dropdown has-arrow main-drop">
                <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                    <span class="user-img"><img
                            src="{{ auth()->user()->image ? asset('images/users/' . auth()->user()->image) : asset('images/not.jpg') }}"
                            alt="">
                        <span class="status online"></span></span>
                </a>
                <div class="dropdown-menu menu-drop-user">
                    <div class="profilename">
                        <div class="profileset">
                            <span class="user-img"><img
                                    src="{{ auth()->user()->image ? asset('images/users/' . auth()->user()->image) : asset('images/not.jpg') }}"
                                    alt="User Image">
                                <span class="status online"></span></span>
                            <div class="profilesets">
                                <h6>{{ auth()->user()->username }}</h6>
                                <h5>{{ auth()->user()->role }}</h5>
                            </div>
                        </div>
                        <hr class="m-0">
                        <a class="dropdown-item" href="{{ route('profile.users') }}"> <img
                                src="{{ asset('/assets/img/icons/users1.svg') }}" class="me-2" alt="img"> My
                            Profile</a>
                        <hr class="m-0">
                        <form action="{{ route('logout') }}" Method="POST">
                            @csrf
                            <button class="dropdown-item" type="submit"><img
                                    src="{{ asset('/assets/img/icons/log-out.svg') }}" class="me-2" alt="img">
                                Logout</button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>



        <div class="dropdown mobile-user-menu">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('profile.users') }}"> My Profile</a>
                <form action="{{ route('logout') }}" Method="POST">
                    @csrf
                    <button class="dropdown-item" type="submit"> Logout</button>
                </form>

            </div>
        </div>
    </div>
</div>
