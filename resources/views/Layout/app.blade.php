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
        @yield('title')
    </title>
    <link rel="icon" href="/assets/logo.png" type="image/x-icon">
    {{-- Bootstrap 4.6 css --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
        integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="/fontawesome/css/all.min.css">
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
                    <span>PMA-Purchase Order Digital System</span>
                </marquee>
            </ul>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item font-weight-bold d-flex justify-content-center flex-column m-0"
                    style="font-weight: 600 !important; color: #FFF; font-size: 17px">
                    Selamat Datang, {{ Auth::user()->name }}
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
                                <span class="text-danger">{{ Auth::user()->email }}</span>
                            </span>
                        </a>

                        @if (Auth::user()->id == 1)
                            {{-- <a href="/development" class="dropdown-item bg-warning">
            <center><i class="fad fa-flask mr-2"></i>Development</center>
          </a> --}}
                        @endif
                        <a href="/profile" class="dropdown-item bg-light">
                            <center><i class="fad fa-address-card mr-2"></i>My Profile</center>
                        </a>

                        <a href="/myaccess" class="dropdown-item bg-info">
                            <center><i class="fad fa-key mr-2"></i>My Access</center>
                        </a>

                        <a href="/logout" class="dropdown-item bg-danger">
                            <center><i class="fad fa-sign-out-alt mr-2"></i>Logout</center>
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-light-navy elevation-4">
            <!-- Brand Logo -->
            <a href="/dashboard" class="brand-link elevation-4" style="background-color: #FFF">
                <img src="/assets/logo.png" alt="PMA Logo" class="brand-image" style="opacity: .8">
                <span class="brand-text font-weight-bold">PMA-PODS</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column nav-legacy nav-child-indent" data-widget="treeview"
                        role="menu" data-accordion="true">
                        <li class="nav-item has-treeview menu-close">
                            <a href="#" class="nav-link">
                                <i class="fad fa-chalkboard nav-icon"></i>
                                <p>
                                    Dashboard
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            @php
                                $barang_jasa_it = App\Http\Controllers\Dashboard\DashboardItBarangJasaReminderController::getItBarangJasaReminderCount();
                                $pengadaan_cops = App\Http\Controllers\Dashboard\DashboardPengadaanCOPController::getPengadaanCOPCount();
                                $vendorevaluation = App\Http\Controllers\Dashboard\DashboardVendorEvaluationController::getVendorEvaluationCount();
                                $BAverification = App\Http\Controllers\Dashboard\DashboardBaVerificationController::getBAverificationCount();
                                $pr_manual_asset = App\Http\Controllers\Dashboard\DashboardPrManualAssetController::getPrManualAssetCount();
                                $request_type_pending = App\Http\Controllers\Dashboard\DashboardRequestTypePendingController::getRequestTypePendingCount();
                                $request_approval = App\Http\Controllers\Dashboard\DashboardRequestApprovalController::getCurrentAuthorizationCount();
                                $po_will_expired = App\Http\Controllers\Dashboard\DashboardPoWillExpiredController::getPoWillExpiredCount();
                                $ga_pr_manual = App\Http\Controllers\Dashboard\DashboardGaPrManualController::getGaPrManualCount();
                            @endphp

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/dashboardRequestApproval" class="nav-link">
                                        <i class="fad fa-ticket nav-icon"></i>
                                        <p style="font-size: 14px">Request Approval
                                            <span class="font-weight-bolder" style="color: #A01E2A;">
                                                .......
                                            </span>
                                            <span class="font-weight-bolder"
                                                style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                {{ $request_approval }}
                                            </span>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/dashboardVendorEvaluation" class="nav-link">
                                        <i class="fad fa-list nav-icon"></i>
                                        <p style="font-size: 14px">Vendor Evaluation
                                            <span class="font-weight-bolder" style="color: #A01E2A;">
                                                .......
                                            </span>
                                            <span class="font-weight-bolder"
                                                style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                {{ $vendorevaluation }}
                                            </span>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/dashboardPengadaanCOP" class="nav-link">
                                        <i class="fad fa-truck-container nav-icon"></i>
                                        <p style="font-size: 14px">Pengadaan COP
                                            <span class="font-weight-bolder" style="color: #A01E2A;">
                                                ..........
                                            </span>
                                            <span class="font-weight-bolder"
                                                style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                {{ $pengadaan_cops }}
                                            </span>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/dashboardPoWillExpired" class="nav-link">
                                        <i class="fad fa-shopping-cart nav-icon"></i>
                                        <p style="font-size: 14px">PO Will Expired
                                            <span class="font-weight-bolder" style="color: #A01E2A;">
                                                ..........
                                            </span>
                                            <span class="font-weight-bolder"
                                                style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                {{ $po_will_expired->getData()->total }}
                                            </span>
                                        </p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/dashboardRequestTypePending" class="nav-link">
                                        <i class="fad fa-spinner nav-icon"></i>
                                        <p style="font-size: 14px">Request Pending
                                            <span class="font-weight-bolder" style="color: #A01E2A;">
                                                ......
                                            </span>
                                            <span class="font-weight-bolder"
                                                style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                {{ $request_type_pending }}
                                            </span>
                                        </p>
                                    </a>
                                </li>

                                @if (Auth::user()->id == 1 ||
                                        Auth::user()->id == 115 ||
                                        Auth::user()->id == 163 ||
                                        Auth::user()->id == 156 ||
                                        Auth::user()->id == 169 ||
                                        Auth::user()->id == 608 ||
                                        Auth::user()->id == 539 ||
                                        Auth::user()->id == 609 ||
                                        Auth::user()->id == 538)
                                    <li class="nav-item">
                                        <a href="/dashboardGaPrManual" class="nav-link">
                                            <i class="fad fa-shopping-bag nav-icon"></i>
                                            <p style="font-size: 14px">Request PR SAP (GA)
                                                <span class="font-weight-bolder" style="color: #A01E2A;">
                                                    .
                                                </span>
                                                <span class="font-weight-bolder"
                                                    style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                    {{ $ga_pr_manual->getData()->total }}
                                                </span>
                                            </p>
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->id == 1 ||
                                        Auth::user()->id == 115 ||
                                        Auth::user()->id == 116 ||
                                        Auth::user()->id == 117 ||
                                        Auth::user()->id == 197 ||
                                        Auth::user()->id == 483 ||
                                        Auth::user()->id == 548 ||
                                        Auth::user()->id == 484 ||
                                        Auth::user()->id == 120)
                                    <li class="nav-item">
                                        <a href="/dashboarditbarangjasareminder" class="nav-link">
                                            <i class="fad fa-bell nav-icon"></i>
                                            <p style="font-size: 14px">Barang Jasa Jenis IT
                                                <span class="font-weight-bolder" style="color: #A01E2A;">
                                                    ....
                                                </span>
                                                <span class="font-weight-bolder"
                                                    style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                    {{ $barang_jasa_it }}
                                                </span>
                                            </p>
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->id == 115 || Auth::user()->id == 1 || Auth::user()->id == 163)
                                    {{-- superadmin & PCM only --}}
                                    <li class="nav-item">
                                        <a href="/dashboardBaVerification" class="nav-link">
                                            <i class="fad fa-file-check nav-icon"></i>
                                            <p style="font-size: 14px">Verifikasi Upload BA
                                                <span class="font-weight-bolder" style="color: #A01E2A;">
                                                    ...
                                                </span>
                                                <span class="font-weight-bolder"
                                                    style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                    {{ $BAverification }}
                                                </span>
                                            </p>
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->id == 1 || Auth::user()->id == 116 || Auth::user()->id == 117 || Auth::user()->id == 197)
                                    <li class="nav-item">
                                        <a href="/dashboardPrManualAsset" class="nav-link">
                                            <i class="fad fa-shopping-bag nav-icon"></i>
                                            <p style="font-size: 14px">PR Manual Asset
                                                <span class="font-weight-bolder" style="color: #A01E2A;">
                                                    ........
                                                </span>
                                                <span class="font-weight-bolder"
                                                    style="color: black; background-color: white; border-radius: 3px; padding: 2px 5px;">
                                                    {{ $pr_manual_asset }}
                                                </span>
                                            </p>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>

                        {{-- MASTERDATA --}}
                        @if ((Auth::user()->menu_access->masterdata ?? 0) != 0)
                            <li class="nav-item has-treeview menu-close">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-database"></i>
                                    <p>
                                        Masterdata
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 1) != 0)
                                        <li class="nav-item">
                                            <a href="/employeeposition" class="nav-link">
                                                <i class="fad fa-user-cowboy nav-icon"></i>
                                                <p>Jabatan</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 2) != 0)
                                        <li class="nav-item">
                                            <a href="/employee" class="nav-link">
                                                <i class="fad fa-users nav-icon"></i>
                                                <p>Karyawan</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 4) != 0)
                                        <li class="nav-item">
                                            <a href="/salespoint" class="nav-link">
                                                <i class="fad fa-globe-asia nav-icon"></i>
                                                <p>Sales Point</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 8) != 0)
                                        <li class="nav-item">
                                            <a href="/employeeaccess" class="nav-link">
                                                <i class="fad fa-user-unlock nav-icon"></i>
                                                <p>Akses Karyawan</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 16) != 0)
                                        <li class="nav-item">
                                            <a href="/authorization" class="nav-link">
                                                <i class="fad fa-signature nav-icon"></i>
                                                <p>Matriks Approval Form</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 32) != 0)
                                        <li class="nav-item">
                                            <a href="/vendor" class="nav-link">
                                                <i class="fad fa-handshake nav-icon"></i>
                                                <p>Vendor</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 64) != 0)
                                        <li class="nav-item">
                                            <a href="/budgetpricing" class="nav-link">
                                                <i class="fad fa-calculator nav-icon"></i>
                                                <p>Budget Pricing</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 128) != 0)
                                        <li class="nav-item">
                                            <a href="/filecompletement" class="nav-link">
                                                <i class="fad fa-folders nav-icon"></i>
                                                <p>Kelengkapan Berkas</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 256) != 0)
                                        <li class="nav-item">
                                            <a href="/armada" class="nav-link">
                                                <i class="fad fa-garage-car nav-icon"></i>
                                                <p>Master Armada</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 512) != 0)
                                        <li class="nav-item">
                                            <a href="/additionalemail" class="nav-link">
                                                <i class="fad fa-mailbox nav-icon"></i>
                                                <p>Additional Email</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 1024) != 0)
                                        <li class="nav-item">
                                            <a href="/notificationemail" class="nav-link">
                                                <i class="fad fa-bell-on nav-icon"></i>
                                                <p>Notification Email</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 2048) != 0)
                                        <li class="nav-item">
                                            <a href="/customticketing" class="nav-link">
                                                <i class="fad fa-ticket nav-icon"></i>
                                                <p>Custom Ticketing</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 4096) != 0)
                                        <li class="nav-item">
                                            <a href="/ticketingblocking" class="nav-link">
                                                <i class="fad fa-ticket nav-icon"></i>
                                                <p>Ticketing Block</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 8192) != 0)
                                        <li class="nav-item">
                                            <a href="/emailcc" class="nav-link">
                                                <i class="fad fa-mailbox nav-icon"></i>
                                                <p>Email CC</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->masterdata ?? 0) & 16384) != 0)
                                        <li class="nav-item">
                                            <a href="/pomanual" class="nav-link">
                                                <i class="fad fa-file-upload nav-icon"></i>
                                                <p>Upload PO Manual</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- BUDGET --}}
                        @if ((Auth::user()->menu_access->budget ?? 0) != 0)
                            <li class="nav-item has-treeview menu-close">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-calculator"></i>
                                    <p>
                                        Budget
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->budget ?? 0) & 1) != 0)
                                        <li class="nav-item">
                                            <a href="/inventorybudget" class="nav-link">
                                                <i class="fad fa-inventory nav-icon"></i>
                                                <p>Inventory</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->budget ?? 0) & 2) != 0)
                                        <li class="nav-item">
                                            <a href="/armadabudget" class="nav-link">
                                                <i class="fad fa-truck nav-icon"></i>
                                                <p>Armada</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->budget ?? 0) & 4) != 0)
                                        <li class="nav-item">
                                            <a href="/assumptionbudget" class="nav-link">
                                                <i class="fad fa-th nav-icon"></i>
                                                <p>Assumption</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->budget ?? 0) & 8) != 0)
                                        <li class="nav-item">
                                            <a href="/ho_budget" class="nav-link">
                                                <i class="fad fa-house nav-icon"></i>
                                                <p>HO Budget</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- OPERATIONAL --}}
                        @if ((Auth::user()->menu_access->operational ?? 0) != 0)
                            <li class="nav-item has-treeview menu-close">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-briefcase"></i>
                                    <p>
                                        Operasional
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->operational ?? 0) & 128) != 0)
                                        <li class="nav-item">
                                            <a href="/renewalarmada" class="nav-link">
                                                <i class="fad fa-truck-container nav-icon"></i>
                                                <p>Peremajaan Armada</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->operational ?? 0) & 1) != 0)
                                        <li class="nav-item">
                                            <a href="/ticketing" class="nav-link">
                                                <i class="fad fa-ticket nav-icon"></i>
                                                <p>Pengadaan</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->operational ?? 0) & 2) != 0)
                                        <li class="nav-item">
                                            <a href="/bidding" class="nav-link">
                                                <i class="fad fa-less-than-equal nav-icon"></i>
                                                <p>Bidding</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->operational ?? 0) & 4) != 0)
                                        <li class="nav-item">
                                            <a href="/pr" class="nav-link">
                                                <i class="fad fa-shopping-bag nav-icon"></i>
                                                <p>Purchase Requisition</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->operational ?? 0) & 32) != 0)
                                        <li class="nav-item">
                                            <a href="/form-validation" class="nav-link">
                                                <i class="fad fa-file-check nav-icon"></i>
                                                <p>Form Validation</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (
                                        ((Auth::user()->menu_access->operational ?? 0) & 8) != 0 ||
                                            ((Auth::user()->menu_access->operational ?? 0) & 16) != 0)
                                        <li class="nav-item">
                                            <a href="/po" class="nav-link">
                                                <i class="fad fa-shopping-cart nav-icon"></i>
                                                <p>Purchase Order</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->operational ?? 0) & 1) != 0)
                                        <li class="nav-item">
                                            <a href="/vendor-evaluation" class="nav-link">
                                                <i class="fad fa-file-check nav-icon"></i>
                                                <p>Vendor Evaluation</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- MONITORING --}}
                        @if ((Auth::user()->menu_access->monitoring ?? 0) != 0)
                            <li class="nav-item has-treeview menu-close">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-eye"></i>
                                    <p>
                                        Monitoring
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->monitoring ?? 0) & 1) != 0)
                                        <li class="nav-item">
                                            <a href="/ticketmonitoring" class="nav-link">
                                                <i class="fad fa-ticket nav-icon"></i>
                                                <p>Monitor Pengadaan</p>
                                            </a>
                                        </li>
                                    @endif

                                    @if (((Auth::user()->menu_access->monitoring ?? 0) & 2) != 0)
                                        <li class="nav-item">
                                            <a href="/securitymonitoring" class="nav-link">
                                                <i class="fad fa-shield nav-icon"></i>
                                                <p>Monitor Security</p>
                                            </a>
                                        </li>
                                    @endif

                                    @if (((Auth::user()->menu_access->monitoring ?? 0) & 4) != 0)
                                        <li class="nav-item">
                                            <a href="/armadamonitoring" class="nav-link">
                                                <i class="fad fa-truck-container nav-icon"></i>
                                                <p>Monitor Armada</p>
                                            </a>
                                        </li>
                                    @endif

                                    @if (((Auth::user()->menu_access->monitoring ?? 0) & 8) != 0)
                                        <li class="nav-item">
                                            <a href="/citmonitoring" class="nav-link">
                                                <i class="fad fa-money-bill-wave nav-icon"></i>
                                                <p>Monitor CIT</p>
                                            </a>
                                        </li>
                                    @endif

                                    @if (((Auth::user()->menu_access->monitoring ?? 0) & 16) != 0)
                                        <li class="nav-item">
                                            <a href="/pestmonitoring" class="nav-link">
                                                <i class="fad fa-pastafarianism nav-icon"></i>
                                                <p>Monitor PEST</p>
                                            </a>
                                        </li>
                                    @endif

                                    @if (((Auth::user()->menu_access->monitoring ?? 0) & 32) != 0)
                                        <li class="nav-item">
                                            <a href="/merchandisermonitoring" class="nav-link">
                                                <i class="fad fa-bags-shopping nav-icon"></i>
                                                <p>Monitor Merchandiser</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- REPORTING --}}
                        @if ((Auth::user()->menu_access->reporting ?? 0) != 0)
                            <li class="nav-item has-treeview menu-close">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-bullhorn"></i>
                                    <p>
                                        Reporting
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (((Auth::user()->menu_access->reporting ?? 0) & 1) != 0)
                                        <li class="nav-item">
                                            <a href="/armadaaccident" class="nav-link">
                                                <i class="fad fa-car-crash nav-icon"></i>
                                                <p>Armada Accident</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->reporting ?? 0) & 4) != 0)
                                        <li class="nav-item">
                                            <a href="/uploadreport" class="nav-link">
                                                <i class="fad fa-file-upload nav-icon"></i>
                                                <p>Upload Report</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (((Auth::user()->menu_access->reporting ?? 0) & 8) != 0)
                                        <li class="nav-item">
                                            <a href="/downloadreport" class="nav-link">
                                                <i class="fad fa-file-download nav-icon"></i>
                                                <p>Download Report</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
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
