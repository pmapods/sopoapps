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
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Monitoring Security @if (request()->get('status') == 4)
                            (Closed PO)
                        @endif
                    </h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Monitoring</li>
                        <li class="breadcrumb-item active">Monitoring Security @if (request()->get('status') == 4)
                                (Closed PO)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="d-flex justify-content-end mb-2">
            @if (request()->get('status') != 4)
                <a href="/securitymonitoring?status=4" class="btn btn-success mr-2">Tampilkan Closed PO</a>
            @else
                <a href="/securitymonitoring" class="btn btn-primary mr-2">Tampilkan PO Aktif</a>
            @endif
            <button type="button" class="btn btn-info" onclick="showExportModal()">Export Data</button>
        </div>
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-po-tab" data-toggle="pill" href="#pills-po" role="tab"
                    aria-controls="pills-po" aria-selected="true">Monitoring PO</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-status-tab" data-toggle="pill" href="#pills-status" role="tab"
                    aria-controls="pills-status" aria-selected="false">Monitoring Status</a>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-po" role="tabpanel" aria-labelledby="pills-po-tab">
                <table id="monitoringPO" class="table table-bordered dataTable" role="grid">
                    <thead>
                        <tr>
                            <th>Nomor PO</th>
                            <th>Jenis Pengadaan PO terkait</th>
                            <th>Start Period</th>
                            <th>End Period</th>
                            <th>Vendor</th>
                            <th>Salespoint</th>
                            <th>Pengadaan Terkait</th>
                            <th>Status</th>
                            <th>Upload File</th>
                            <th>Lihat File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pos as $po)
                            <tr @if ($po->status == 4) class="table-success" @endif>
                                <td>{{ $po->po_number }}</td>
                                <td>{{ $po->ticket_type }}</td>
                                @if ($po->start_date && $po->end_date)
                                    <td>{{ \Carbon\Carbon::parse($po->start_date)->translatedFormat('d F Y') }}</td>
                                    <td @if (now()->addDays(60) > \Carbon\Carbon::parse($po->end_date)) class="text-danger" @endif>
                                        {{ \Carbon\Carbon::parse($po->end_date)->translatedFormat('d F Y') }}</td>
                                @else
                                    <td>-</td>
                                    <td>-</td>
                                @endif
                                <td>{{ $po->vendor_name ? $po->vendor_name : '-' }}</td>
                                <td>{{ $po->salespoint_name }}</td>

                                <td>
                                    @if ($po->start_date && $po->end_date)
                                        {{ $po->current_ticketing ? $po->current_ticketing->type() . ' ' . $po->current_ticketing->code : 'Belum di proses' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $po->status_name }}</td>

                                @if (
                                    $po->ticket_type == 'Manual' &&
                                        $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath == null &&
                                        $po->security_cit_pestcontrol_merchandiser_po_filepath == null)
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" id="button_attachment_1"
                                            onclick="uploadFileAttachment()" name="button_upload" id="button_upload">Upload
                                            File
                                            Attachment</button>
                                    </td>
                                @elseif (
                                    $po->ticket_type == 'Manual' &&
                                        $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath &&
                                        $po->security_cit_pestcontrol_merchandiser_po_filepath)
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" id="button_attachment_1"
                                            onclick="uploadFileAttachment()" name="button_upload" id="button_upload">Upload
                                            File
                                            Attachment</button>
                                    </td>
                                @else
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" disabled>Upload
                                            File
                                            Attachment</button>
                                    </td>
                                @endif

                                @if (
                                    $po->ticket_type == 'Manual' &&
                                        $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath &&
                                        $po->security_cit_pestcontrol_merchandiser_po_filepath)
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" id="button_attachment_2"
                                            onclick="viewFileAttachment( '{{ $po->security_cit_pestcontrol_merchandiser_pr_manual_filepath }} ',
                                              '{{ $po->security_cit_pestcontrol_merchandiser_po_filepath }} ')">Lihat
                                            File
                                            Attachment</button>
                                    </td>
                                @else
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" disabled>Lihat
                                            File
                                            Attachment</button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="pills-status" role="tabpanel" aria-labelledby="pills-status-tab">
                <table id="monitoringStatus" class="table table-bordered table-striped dataTable" role="grid">
                    <thead>
                        <tr role="row">
                            <th>
                                #
                            </th>
                            <th>
                                Kode Pengadaan
                            </th>
                            <th>
                                SalesPoint
                            </th>
                            <th>
                                Tanggal Mulai Pengadaan
                            </th>
                            <th>
                                Lama Pengadaan (hari)
                            </th>
                            <th>
                                Status saat ini
                            </th>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $key => $ticket)
                            <tr data-status="{{ $ticket->status() }}" data-ticket_id="{{ $ticket->id }}">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $ticket->code }}</td>
                                <td>{{ $ticket->salespoint->name }}</td>
                                <td>{{ $ticket->created_at->translatedFormat('d F Y') }}</td>
                                <td>{{ $ticket->created_at->diffForHumans(now()) }}</td>
                                <td>{{ $ticket->status() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="monitoringmodal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Monitoring PO (<span id="modal_po_number"></span>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <table class="table table-borderless" id="security_monitoring_table">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Pengadaan</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="monitormodal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Monitoring (<span class="code"></span>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Aktivitas</th>
                                <th>Oleh</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div>status saat ini : <b class="status">Dalam Proses Bidding oleh tim Purchasing</b></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exportmodal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Jenis Export</label>
                                <select class="form-control" id="select_export">
                                    <option value="">-- Pilih Jenis Export --</option>
                                    <option value="securityPOByArea">Monitor PO per Area</option>
                                    {{-- <option value="getExpiredPO">Expired PO</option> --}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="button_export">Export</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File Attachment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/securitymonitoring/uploadfileattachmentsecuritymonitoring" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nomor PO</label>
                                    <input type="text" class="form-control" name="po_number" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="required_field">PR Manual</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_pr_manual" id="file_pr_manual" required>
                                </div>
                                <div class="form-group">
                                    <label class="required_field">PO</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_po" id="file_po" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lihat File Attachment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>File PR Manual</label>
                                <div>
                                    <a target="_blank" id="attachment_1">tampilkan
                                        attachment</a>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>File PO</label>
                                <div>
                                    <a target="_blank" id="attachment_2">tampilkan
                                        attachment</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        var po_number = getUrlVars()["po_number"];
        $(document).ready(function() {
            var table_status = $('#monitoringStatus').DataTable(datatable_settings);
            if (po_number) {
                datatable_settings.search.search = po_number;
            }
            var table = $('#monitoringPO').DataTable(datatable_settings);
            $('.dataTables_filter input').val(po_number);
            $('#monitoringPO tbody').on('click', 'tr', function() {
                let po_number = $(this).find('td:eq(0)').text().trim();
                $('#monitoringmodal').modal('show');
                $('#modal_po_number').text(po_number);
                $('#button_upload').val(po_number);
                $('input[name="po_number"]').val(po_number);
                $('#security_monitoring_table tbody').empty();
                $.ajax({
                    type: "get",
                    url: "/securitymonitoringpologs/" + po_number,
                    success: function(response) {
                        let data = response.data;
                        data.forEach(log => {
                            let row_element = '<tr>';
                            if (log.po_number == po_number) {
                                row_element += '<td class="font-weight-bold">' + log
                                    .po_number + '</td>';
                            } else {
                                row_element += '<td>' + log.po_number + '</td>';
                            }
                            row_element += '<td>' + log.date + '</td>';
                            row_element += '<td>' + log.type + '</td>';
                            row_element += '</tr>';
                            $('#security_monitoring_table tbody').append(row_element);
                        });
                    },
                    error: function(response) {
                        alert("error");
                    }
                });
            });
            $('#monitoringStatus tbody').on('click', 'tr', function() {
                let code = $(this).find('td:eq(1)').text().trim();
                let security_ticket_id = $(this).data('ticket_id');
                let status_column_el = $(this).find('td:eq(5)');
                $.ajax({
                    type: "get",
                    url: "/securitymonitoringticketlogs/" + code,
                    success: function(response) {
                        let logs = []
                        $('#monitormodal').find('table tbody tr').remove();
                        let data = response.data;
                        let status = response.status;
                        data.forEach(log => {
                            let row_element = '<tr>';
                            row_element +=
                                '<td style="width:60%; overflow-wrap: anywhere">' + log
                                .message + '</td>';
                            row_element += '<td>' + log.employee_name + '</td>';
                            row_element += '<td>' + log.date + '</td>';
                            row_element += '</tr>';
                            $('#monitormodal').find('table tbody').append(row_element);
                        });
                        $('#monitormodal').find('.status').text(status);
                        status_column_el.text(status);
                    },
                    error: function(response) {
                        alert("error");
                    }
                });
                $('#monitormodal').find('.code').text(code);
                $('#monitormodal').modal('show');
            });
            $('#button_export').click(function() {
                let export_url = $('#select_export').val();
                if (export_url != "") {
                    window.open('/securitymonitoring/' + export_url);
                }
            });
        });

        function showExportModal() {
            $('#select_export').val("");
            $('#select_export').trigger("change");
            $('#exportmodal').modal('show');
        }

        function uploadFileAttachment() {
            $('#uploadModal').modal('show');
        }

        function viewFileAttachment(security_cit_pestcontrol_merchandiser_pr_manual_filepath,
            security_cit_pestcontrol_merchandiser_po_filepath) {

            $('#viewModal').modal('show');
            console.log(security_cit_pestcontrol_merchandiser_pr_manual_filepath);
            console.log(security_cit_pestcontrol_merchandiser_po_filepath);

            $('#attachment_1').attr("href",
                'storage/' + security_cit_pestcontrol_merchandiser_pr_manual_filepath);

            $('#attachment_2').attr("href",
                'storage/' + security_cit_pestcontrol_merchandiser_po_filepath);
        }
    </script>
@endsection
