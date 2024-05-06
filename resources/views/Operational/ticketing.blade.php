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
                    <h1 class="m-0 text-dark">Ticketing @if (request()->get('status') == -1)
                            (History)
                        @endif
                    </h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item active">Ticketing @if (request()->get('status') == -1)
                                (History)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-1">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectTicketModal">
                    Tambah Tiket Baru
                </button>
                @if (request()->get('status') == -1)
                    <a href="/ticketing" class="btn btn-success ml-2">Ticket Aktif</a>
                @else
                    <a href="/ticketing?status=-1" class="btn btn-info ml-2">History</a>
                @endif
                @if (Auth::user()->id)
                    {{-- superadmin only --}}
                    <a href="/ticketing/BAVerification" class="btn btn-warning ml-2">Verfikasi BA Upload</a>
                @endif
            </div>
        </div>
    </div>

    <div class="content-body px-4">
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-barangjasa-tab" data-toggle="pill" href="#pills-barangjasa"
                    role="tab" aria-controls="pills-barangjasa" aria-selected="true">Barang Jasa</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-armada-tab" data-toggle="pill" href="#pills-armada" role="tab"
                    aria-controls="pills-armada" aria-selected="false">Armada</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-security-tab" data-toggle="pill" href="#pills-security" role="tab"
                    aria-controls="pills-security" aria-selected="false">Security</a>
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
                    <option value="2022">2022</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
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
            <div class="col-md-3">
                <label>Filter Tipe Ticket Armada</label>
                <select class="form-control select2" name="armada_filter" id="armada_filter">
                    <option value="">-- Tipe Ticket --</option>
                    <option value="pengadaan">Pengadaan</option>
                    <option value="perpanjangan">Perpanjangan</option>
                    <option value="replace">Replace</option>
                    <option value="renewal">Renewal</option>
                    <option value="end">End Kontrak</option>
                    <option value="mutasi">Mutasi</option>
                    <option value="percepatan_replace">Percepatan Replace</option>
                    <option value="percepatan_renewal">Percepatan Renewal</option>
                    <option value="percepatan_end">Percepatan End Kontrak</option>
                </select>
                <br>
            </div>

            <div class="col-md-2">
                <label>Filter Tipe Ticket Security</label>
                <select class="form-control select2" name="security_filter" id="security_filter">
                    <option value="">-- Tipe Ticket --</option>
                    <option value="0">Pengadaan</option>
                    <option value="1">Perpanjangan</option>
                    <option value="2">Replace</option>
                    <option value="3">End Kontrak</option>
                    <option value="4">Pengadaan Lembur</option>
                    <option value="5">Percepatan Replace</option>
                    <option value="6">Percepatan End Kontrak</option>
                </select>
                <br>
            </div>
        </div>

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="ticketDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Pembuat Form</th>
                                <th>Area</th>
                                <th width="23%">Keterangan</th>
                                <th>Tanggal Pembuatan Tiket</th>
                                <th width="23%">List Item</th>
                                <th width="33%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-armada" role="tabpanel" aria-labelledby="pills-armada-tab">
                <div class="table-responsive">
                    <table id="armadaticketDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Pembuat Form</th>
                                <th>Area</th>
                                <th>Tipe Tiket</th>
                                <th>Tanggal Requirement</th>
                                <th width="30%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                <div class="table-responsive">
                    <table id="securityticketDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Pembuat Form</th>
                                <th>Area</th>
                                <th>Tipe Tiket</th>
                                <th>Tanggal Requirement</th>
                                <th width="30%">Status</th>
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
    <div class="modal fade" id="selectTicketModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Jenis</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="get" action="/addnewticket">
                    @csrf
                    <div class="modal-body">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="ticketing_type" value="0"
                                    checked>
                                Barang Jasa
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="ticketing_type" value="2">
                                Armada
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="ticketing_type" value="1">
                                Security
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="ticketing_type" value="3">
                                Jasa Lainnya
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="ticketing_type" value="4">
                                Custom Ticket
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Pengadaan</button>
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
            let table = $('#ticketDT').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    'url': '/ticketing/data?type=ticket&status=' + status,
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

            let armadatable;
            let securitytable;
            let selected_array = [];
            $('a[data-toggle="pill"]').on('shown.bs.tab', function(event) { 
                if ($(event.target).attr('href') == "#pills-armada" && !selected_array.includes(
                        "#pills-armada")) {
                    selected_array.push("#pills-armada");
                    let armadatable = $('#armadaticketDT').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            'url': '/ticketing/data?type=armada&status=' + status,
                            'data': function(data) {
                                // Read values
                                let salespoint = $('#salespoint_id').val();
                                let month = $('#month_filter').val();
                                let armadafilter = $('#armada_filter').val();
                                let year = $('#year_filter').val();

                                // Append to data
                                data.searchBySalesPoint = salespoint;
                                data.searchByMonth = month;
                                data.searchByArmada = armadafilter;
                                data.searchByyear = year;
                            }
                        },
                        "createdRow": function(row, data, dataIndex) {}
                    });

                    $('#salespoint_id').change(function() {
                        armadatable.draw();
                    });
                    $('#month_filter').change(function() {
                        armadatable.draw();
                    });
                    $('#armada_filter').change(function() {
                        armadatable.draw();
                    });
                    $('#year_filter').change(function() {
                        armadatable.draw();
                    });

                }
                if ($(event.target).attr('href') == "#pills-security" && !selected_array.includes(
                        "#pills-security")) {
                    selected_array.push("#pills-security");
                    let securitytable = $('#securityticketDT').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            'url': '/ticketing/data?type=security&status=' + status,
                            'data': function(data) {
                                // Read values
                                let salespoint = $('#salespoint_id').val();
                                let month = $('#month_filter').val();
                                let securityfilter = $('#security_filter').val();
                                let year = $('#year_filter').val();

                                // Append to data
                                data.searchBySalesPoint = salespoint;
                                data.searchByMonth = month;
                                data.searchBySecurity = securityfilter;
                                data.searchByyear = year;
                            }
                        },
                        "createdRow": function(row, data, dataIndex) {}
                    });

                    $('#salespoint_id').change(function() {
                        securitytable.draw();
                    });
                    $('#month_filter').change(function() {
                        securitytable.draw();
                    });
                    $('#security_filter').change(function() {
                        securitytable.draw();
                    });
                    $('#year_filter').change(function() {
                        securitytable.draw();
                    });
                }
            });

            $('#ticketDT tbody').on('click', 'tr', function() {
                let id = $(this).data('id');
                let code = $(this).find('td:eq(1)').text().trim();
                // console.log(window.btoa(code));
                if (code != "") {
                    window.location.href = '/ticketing/' + code;
                } else {
                    window.location.href = '/ticketing/' + id;
                }
            });

            $('#armadaticketDT tbody').on('click', 'tr', function() {
                let id = $(this).data('id');
                let code = $(this).find('td:eq(1)').text().trim();
                if (code != "") {
                    window.location.href = '/armadaticketing/' + code;
                } else {
                    window.location.href = '/armadaticketing/' + id;
                }
            });

            $('#securityticketDT tbody').on('click', 'tr', function() {
                let id = $(this).data('id');
                let code = $(this).find('td:eq(1)').text().trim();
                if (code != "") {
                    window.location.href = '/securityticketing/' + code;
                }
            });
            $('#ticketDT thead th,#armadaticketDT thead th,#securityticketDT thead th').each(function() {
                $(this).css('width', '');
            });

            var type = getUrlVars()["menu"];
            switch (type) {
                case "Barangjasa":
                    $('#pills-barangjasa-tab').click();
                    break;
                case "Armada":
                    $('#pills-armada-tab').click();
                    break;
                case "Security":
                    $('#pills-security-tab').click();
                    break;
                default:
                    break;
            }
        });
    </script>
@endsection
