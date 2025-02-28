@extends('Layout.app')
@section('local-css')
    <style>
        #pills-tab .nav-link {
            background-color: #a01e2b48;
            color: black !important;
        }

        #pills-tab .nav-link.active {
            background-color: #A01E2A;
            color: white !important;
        }

        #ticketDT tbody td:nth-child(2) {
            white-space: nowrap;
        }

        #ticketDT tbody td:nth-child(6) {
            white-space: pre-line !important;
            font-size: 80% !important;
            font-weight: 400 !important;
        }

        #ticketDT tbody td:nth-child(8),
        #ticketDT tbody td:nth-child(9) {
            white-space: pre-line !important;
            font-size: 80% !important;
            font-weight: 400 !important;
            /* display: -webkit-box !important;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        -webkit-line-clamp: 4 !important; */
        }

        #armadaticketDT tbody td:nth-child(2) {
            white-space: nowrap;
        }

        #armadaticketDT tbody td:nth-child(6) {
            white-space: pre-line !important;
        }

        #armadaticketDT tbody td:nth-child(8) {
            white-space: pre-line !important;
            font-size: 80% !important;
            font-weight: 400 !important;
        }

        #securityticketDT tbody td:nth-child(2) {
            white-space: nowrap;
        }

        #securityticketDT tbody td:nth-child(8) {
            white-space: pre-line !important;
            font-size: 80% !important;
            font-weight: 400 !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">PO @if (request()->get('status') == -1)
                            (History)
                        @endif
                    </h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Sales</li>
                        <li class="breadcrumb-item active">PO @if (request()->get('status') == -1)
                                (History)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-1">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectPOModal">
                    Tambah PO Baru
                </button>
                @if (request()->get('status') == -1)
                    <a href="/po" class="btn btn-success ml-2">PO Aktif</a>
                @else
                    <a href="/po?status=-1" class="btn btn-info ml-2">History</a>
                @endif
            </div>
        </div>
    </div>

    <div class="content-body px-4">
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-posewa-tab" data-toggle="pill" href="#pills-posewa"
                    role="tab" aria-controls="pills-posewa" aria-selected="true">PO Sewa</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-pojual-tab" data-toggle="pill" href="#pills-pojual" role="tab"
                    aria-controls="pills-pojual" aria-selected="false">PO Jual</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-pocustom-tab" data-toggle="pill" href="#pills-pocustom" role="tab"
                    aria-controls="pills-pocustom" aria-selected="false">PO Custom</a>
            </li>
        </ul>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Filter Area / SalesPoint</label>
                    <select class="form-control select2 salespoint_select2" name="salespoint_id" id="salespoint_id">
                        <option value="" data-isjawasumatra="-1">-- Pilih SalesPoint --</option>
                        @foreach ($available_salespoints as $region)
                            <optgroup label="{{ $region->first()->region_name() }}">
                                @foreach ($region as $salespoint)
                                    <option value="{{ $salespoint->id }}"
                                        data-isjawasumatra="{{ $salespoint->isJawaSumatra }}">
                                        {{ $salespoint->name }} --
                                        {{ $salespoint->jawasumatra() }} Jawa Sumatra</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <small class="text-danger">* SalesPoint yang muncul berdasarkan hak akses tiap akun</small>
                </div>
            </div>
            <div class="col-md-2">
                <label>Filter Tahun Pengajuan</label>
                <select class="form-control select2" name="year_filter" id="year_filter">
                    <option value="">-- Tahun Pengajuan --</option>
                    @for ($i = date('Y')-3; $i <= date('Y')+3; $i++)
                        <option value="{{ $i }}"> {{ $i }} </option>
                    @endfor
                </select>
                <br>
            </div>
            <div class="col-md-2">
                <label>Filter Bulan Pengajuan</label>
                <select class="form-control select2" name="month_filter" id="month_filter">
                    <option value="0">-- Bulan Pengajuan --</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mey</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
                <br>
            </div>
        </div>

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-posewa" role="tabpanel"
                aria-labelledby="pills-posewa-tab">
                <div class="table-responsive">
                    <table id="posewaDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nomor PO</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Customer</th>
                                <th>Pembuat PO</th>
                                <th>Area</th>
                                <th width="23%">Keterangan</th>
                                <th width="23%">List Item</th>
                                <th width="33%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-pojual" role="tabpanel" aria-labelledby="pills-pojual-tab">
                <div class="table-responsive">
                    <table id="pojualDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nomor PO</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Customer</th>
                                <th>Pembuat PO</th>
                                <th>Area</th>
                                <th width="23%">Keterangan</th>
                                <th width="23%">List Item</th>
                                <th width="33%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-pocustom" role="tabpanel" aria-labelledby="pills-pocustom-tab">
                <div class="table-responsive">
                    <table id="pocustomDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nomor PO</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Customer</th>
                                <th>Pembuat PO</th>
                                <th>Area</th>
                                <th width="23%">Keterangan</th>
                                <th width="23%">List Item</th>
                                <th width="33%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="selectPOModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Jenis PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="get" action="/addnewpo">
                    @csrf
                    <div class="modal-body">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="request_type" value="1"
                                    checked>
                                PO Sewa
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="request_type" value="2">
                                PO Jual
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="request_type" value="3">
                                PO Custom
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat PO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status')
            let table = $('#posewaDT').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    'url': '/po/data?type=posewa&status=' + status,
                    'data': function(data) {
                        // Read values
                        let salespoint = $('#salespoint_id').val();
                        let month = $('#month_filter').val();
                        let year = $('#year_filter').val();

                        // Append to data
                        data.searchBySalesPoint = salespoint;
                        data.searchByMonth = month;
                        data.searchByyear = year;
                    }
                },
                "createdRow": function(row, data, dataIndex) {}
            });

            $('#salespoint_id').change(function() {
                table.draw();
            });
            $('#month_filter').change(function() {
                table.draw();
            });
            $('#year_filter').change(function() {
                table.draw();
            });

            let pojualtable;
            let securitytable;
            let selected_array = [];
            $('a[data-toggle="pill"]').on('shown.bs.tab', function(event) { 
                if ($(event.target).attr('href') == "#pills-pojual" && !selected_array.includes(
                        "#pills-pojual")) {
                    selected_array.push("#pills-pojual");
                    let pojualtable = $('#pojualDT').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            'url': '/po/data?type=pojual&status=' + status,
                            'data': function(data) {
                                // Read values
                                let salespoint = $('#salespoint_id').val();
                                let month = $('#month_filter').val();
                                let year = $('#year_filter').val();

                                // Append to data
                                data.searchBySalesPoint = salespoint;
                                data.searchByMonth = month;
                                data.searchByyear = year;
                            }
                        },
                        "createdRow": function(row, data, dataIndex) {}
                    });

                    $('#salespoint_id').change(function() {
                        pojualtable.draw();
                    });
                    $('#month_filter').change(function() {
                        pojualtable.draw();
                    });
                    $('#year_filter').change(function() {
                        pojualtable.draw();
                    });

                }
                if ($(event.target).attr('href') == "#pills-pocustom" && !selected_array.includes(
                        "#pills-pocustom")) {
                    selected_array.push("#pills-pocustom");
                    let pocustomtable = $('#pocustomDT').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            'url': '/po/data?type=pocustom&status=' + status,
                            'data': function(data) {
                                // Read values
                                let salespoint = $('#salespoint_id').val();
                                let month = $('#month_filter').val();
                                let year = $('#year_filter').val();

                                // Append to data
                                data.searchBySalesPoint = salespoint;
                                data.searchByMonth = month;
                                data.searchByyear = year;
                            }
                        },
                        "createdRow": function(row, data, dataIndex) {}
                    });

                    $('#salespoint_id').change(function() {
                        pocustomtable.draw();
                    });
                    $('#month_filter').change(function() {
                        pocustomtable.draw();
                    });
                    $('#year_filter').change(function() {
                        pocustomtable.draw();
                    });
                }
            });

            $('#posewaDT tbody').on('click', 'tr', function() {
                let id = $(this).data('id');
                let code = $(this).find('td:eq(1)').text().trim();
                // console.log(window.btoa(code));
                if (code != "") {
                    window.location.href = '/po/' + code;
                } else {
                    window.location.href = '/po/' + id;
                }
            });

            $('#pojualDT tbody').on('click', 'tr', function() {
                let id = $(this).data('id');
                let code = $(this).find('td:eq(1)').text().trim();
                if (code != "") {
                    window.location.href = '/po/' + code;
                } else {
                    window.location.href = '/po/' + id;
                }
            });

            $('#pocustomDT tbody').on('click', 'tr', function() {
                let id = $(this).data('id');
                let code = $(this).find('td:eq(1)').text().trim();
                if (code != "") {
                    window.location.href = '/po/' + code;
                } else {
                    window.location.href = '/po/' + id;
                }
            });

            $('#posewaDT thead th,#pojualDT thead th,#pocustomDT thead th').each(function() {
                $(this).css('width', '');
            });

            var type = getUrlVars()["menu"];
            switch (type) {
                case "PO Sewa":
                    $('#pills-posewa-tab').click();
                    break;
                case "PO Jual":
                    $('#pills-pojual-tab').click();
                    break;
                case "PO Custom":
                    $('#pills-pocustom-tab').click();
                    break;
                default:
                    break;
            }
        });
    </script>
@endsection
