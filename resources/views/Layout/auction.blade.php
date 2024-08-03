<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ Session::token() }}">
    <title>
        @yield('title', 'List Auction Ticket')
    </title>
    <link rel="icon" href="/assets/logo.png" type="image/x-icon">
    {{-- Bootstrap 4.6 css --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
        integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/css/register.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="/css/global.css">
    {{-- datatable CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css">
    {{-- select2 css --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Select 2 CSS bootstrap theme --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

    @yield('local-css')
</head>

<body class="sidebar-collapse layout-fixed layout-navbar-fixed sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>
            <style>
                marquee {
                    width: 60vw !important;
                }

                marquee span {
                    color: #ffffff;
                    font-size: 1.5em !important;
                    font-weight: bold;
                }
            </style>
            <ul class="navbar-nav ml-auto">
                <marquee scrolldelay="60" vspace="0">
                    <span>PMA-PODS Auction</span>
                </marquee>
            </ul>
            @if (Auth::guard('vendor')->check())
                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item font-weight-bold d-flex justify-content-center flex-column m-0"
                        style="font-weight: 600 !important; color: #FFF; font-size: 17px">
                        Selamat Datang, {{ Auth::guard('vendor')->user()->name }}
                    </li>
                    
                    {{-- Profile Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="fad fa-user"></i><span class="ml-1"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- Button trigger modal -->
                            <a href="#">
                                <span class="dropdown-header font-weight-bolder">
                                    Notification Email<br>
                                    <span class="text-danger">{{ Auth::guard('vendor')->user()->email }}</span>
                                </span>
                            </a>
                            <!-- <a href="/auction/userProfile" class="dropdown-item bg-light">
                                <center><i class="fad fa-address-card mr-2"></i>User Profile</center>
                            </a>
                            <a href="/auction/companyProfile" class="dropdown-item bg-light">
                                <center><i class="fad fa-address-card mr-2"></i>Company Profile</center>
                            </a> -->

                            <!-- Form logout -->
                            <form id="logout-form" action="{{ route('vendor.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a href="#" class="dropdown-item bg-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <center><i class="fad fa-sign-out-alt mr-2"></i>Logout</center>
                            </a>
                        </div>
                    </li>
                </ul>
            @else
                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="{{ route('loginVendor') }}" class="nav-link">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('vendor.register') }}" class="nav-link">Register</a>
                    </li>
                </ul>
            @endif
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-light-navy elevation-4">
            <!-- Brand Logo -->
            <a href="/dashboard" class="brand-link elevation-4" style="background-color: #FFF">
                <img src="/assets/logo.png" alt="PMA Logo" class="brand-image" style="opacity: .8">
                <span class="brand-text font-weight-bold">PMA-PODS Auction</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column nav-legacy nav-child-indent" data-widget="treeview"
                        role="menu" data-accordion="true">
                        <li class="nav-item has-treeview menu-close">
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <div class="content-wrapper p-3">
            @if ($errors->any())
                <div class="m-1 alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (Session::has('success'))
                <div class="m-1 alert alert-success alert-dismissible fade show" role="alert">
                    {!! nl2br(e(Session::get('success'))) !!}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (Session::has('error'))
                <div class="m-1 alert alert-danger alert-dismissible fade show" role="alert">
                    {!! nl2br(e(Session::get('error'))) !!}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="loading_modal" tabindex="-1" role="dialog" data-backdrop="static"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    Loading...
                </div>
            </div>
        </div>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    {{-- Jquery serialization object --}}
    <script src="/js/jquery.serialize-object.min.js"></script>
    <!-- Bootstrap 4.6 -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"
        integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous">
    </script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/js/adminlte.min.js"></script>
    {{-- Select 2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    {{-- Autonumeric --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.min.js"></script>
    {{-- Datatable --}}
    <script src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
    {{-- moment --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script src="/js/layout.js?ver={{ now()->format('Ymdhi') }}"></script>

    <!-- Local JS -->
    @yield('local-js')
</body>

</html>
