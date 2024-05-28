@extends('Layout.app')

@section('local-css')
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0px 5px 10px 0 rgba(0, 0, 0, 0.2);
        overflow: hidden;
        position: relative;
    }

    .card-body {
        z-index: 1;
    }

    .card-footer {
        z-index: 1;
    }

    .card-footer .black-txt {
        color: black !important;
        background-color: transparent;
        text-decoration: none;
    }

    .wave-container {
        position: absolute;
        width: 100%;
        height: 70%;
        top: 0;
    }

    .wave {
        position: absolute;
        height: 1000px;
        width: 900px;
        opacity: 0.6;
        border-radius: 35%;
        left: -250px;
        top: 100px;
        background: radial-gradient(#83a4d4, #b6fbff);
        animation: wave 12s infinite linear;
    }

    .wave:nth-child(2) {
        animation-delay: 0.2s;
    }

    .wave:nth-child(3) {
        animation-delay: 0.3s;
    }

    @keyframes wave {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
@endsection

@section('content')
    <div class="content-body">
        <h1>Dashboard</h1>
    </div>
    <br>

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

    <div class="row">
        <div class="col-xl-3 col-md-4 ">
            @if ($request_approval != 0)
            <div class="card text-black mb-4">
                <div class="card-body">
                    <div class="ml-0" style="font-size: 25px">
                        {{ $request_approval }}
                    </div>
                    <i class="fad fa-ticket"></i>
                    <span style="color: #007bff;">&nbsp;</span>
                    <span>Request Approval</span>
                </div>
                <div class="wave-container">
                    <div class="wave"></div>
                    <div class="wave"></div>
                    <div class="wave"></div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <a class="small stretched-link black-txt" href="/dashboardRequestApproval">View Details</a>
                    <span style="color: #007bff;">&nbsp;&nbsp;</span>
                    <div class="small text-black"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
            @else
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="ml-0" style="font-size: 25px">
                        {{ $request_approval }}
                    </div>
                    <i class="fad fa-ticket"></i>
                    <span style="color: #007bff;">&nbsp;</span>
                    <span>Request Approval</span>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <a class="small stretched-link text-white" href="/dashboardRequestApproval">View Details</a>
                    <span style="color: #007bff;">&nbsp;&nbsp;</span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-xl-3 col-md-4">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <div class="ml-0" style="font-size: 25px">
                        {{ $vendorevaluation }}
                    </div>
                    <i class="fad fa-list"></i>
                    <span style="color: #6c757d;">..</span>
                    <span>Vendor Evaluation</span>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <a class="small text-white stretched-link" href="/dashboardVendorEvaluation">View Details</a>
                    <span style="color: #6c757d;">...</span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="ml-0" style="font-size: 25px">
                        {{ $pengadaan_cops }}
                    </div>
                    <i class="fad fa-truck-container"></i>
                    <span style="color: #28a745;">..</span>
                    <span>Pengadaan COP</span>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <a class="small text-white stretched-link" href="/dashboardPengadaanCOP">View Details</a>
                    <span style="color: #28a745;">...</span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="ml-0" style="font-size: 25px">
                        {{ $po_will_expired->getData()->total }}
                    </div>
                    <i class="fad fa-shopping-cart"></i>
                    <span style="color: #dc3545;">..</span>
                    <span>PO Will Expired</span>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <a class="small text-white stretched-link" href="/dashboardPoWillExpired">View Details</a>
                    <span style="color: #dc3545;">...</span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="ml-0" style="font-size: 25px">
                        {{ $request_type_pending }}
                    </div>
                    <i class="fad fa-spinner"></i>
                    <span style="color: #17a2b8;">..</span>
                    <span>Request Pending</span>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <a class="small text-white stretched-link" href="/dashboardRequestTypePending">View Details</a>
                    <span style="color: #17a2b8;">...</span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        @if (Auth::user()->id == 1 ||
                Auth::user()->id == 115 ||
                Auth::user()->id == 163 ||
                Auth::user()->id == 156 ||
                Auth::user()->id == 169 ||
                Auth::user()->id == 608 ||
                Auth::user()->id == 539 ||
                Auth::user()->id == 609 ||
                Auth::user()->id == 538)
            <div class="col-xl-3 col-md-4">
                <div class="card bg-dark text-white mb-4">
                    <div class="card-body">
                        <div class="ml-0" style="font-size: 25px">
                            {{ $ga_pr_manual->getData()->total }}
                        </div>
                        <i class="fad fa-shopping-bag"></i>
                        <span style="color: #343a40;">..</span>
                        <span>Request PR SAP (GA)</span>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <a class="small text-white stretched-link" href="/dashboardGaPrManual">View Details</a>
                        <span style="color: #343a40;">...</span>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
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
            <div class="col-xl-3 col-md-4">
                <div class="card bg-secondary text-white mb-4">
                    <div class="card-body">
                        <div class="ml-0" style="font-size: 25px">
                            {{ $barang_jasa_it }}
                        </div>
                        <i class="fad fa-bell"></i>
                        <span style="color: #6c757d;">..</span>
                        <span>Barang Jasa Jenis IT</span>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <a class="small text-white stretched-link" href="/dashboarditbarangjasareminder">View Details</a>
                        <span style="color: #6c757d;">...</span>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        @endif

        @if (Auth::user()->id == 115 || Auth::user()->id == 1 || Auth::user()->id == 163)
            <div class="col-xl-3 col-md-4">
                @if ($BAverification != 0)
                <div class="card text-black mb-4">
                    <div class="card-body">
                        <div class="ml-0" style="font-size: 25px">
                            {{ $BAverification }}
                        </div>
                        <i class="fad fa-file-check"></i>
                        <span style="color: #007bff;">&nbsp;</span>
                        <span>Verifikasi Upload BA</span>
                    </div>
                    <div class="wave-container">
                        <div class="wave"></div>
                        <div class="wave"></div>
                        <div class="wave"></div>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <a class="small stretched-link black-txt" href="/dashboardBaVerification">View Details</a>
                        <span style="color: #007bff;">&nbsp;&nbsp;</span>
                        <div class="small text-black"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
                @else
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="ml-0" style="font-size: 25px">
                            {{ $BAverification }}
                        </div>
                        <i class="fad fa-file-check"></i>
                        <span style="color: #007bff;">..</span>
                        <span>Verifikasi Upload BA</span>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <a class="small text-white stretched-link" href="/dashboardBaVerification">View Details</a>
                        <span style="color: #007bff;">...</span>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
                @endif
            </div>
        @endif

        @if (Auth::user()->id == 1 || Auth::user()->id == 116 || Auth::user()->id == 117 || Auth::user()->id == 197)
            <div class="col-xl-3 col-md-4">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">
                        <div class="ml-0" style="font-size: 25px">
                            {{ $pr_manual_asset }}
                        </div>
                        <i class="fad fa-shopping-bag"></i>
                        <span style="color: #dc3545;">..</span>
                        <span>PR Manual Asset</span>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <a class="small text-white stretched-link" href="/dashboardPrManualAsset">View Details</a>
                        <span style="color: #dc3545;">...</span>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
@section('local-js')
    <script></script>
@endsection
