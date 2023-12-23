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
                    <h1 class="m-0 text-dark">Monitoring Armada @if (request()->get('status') == 4)
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
                        <li class="breadcrumb-item active">Monitoring Armada @if (request()->get('status') == 4)
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
                <a href="/armadamonitoring?status=4" class="btn btn-success mr-2">Tampilkan Closed PO</a>
            @else
                <a href="/armadamonitoring" class="btn btn-primary mr-2">Tampilkan PO Aktif</a>
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
                <table id="monitoringPO" class="table table-bordered dataTable table-sm small" role="grid">
                    <thead>
                        <tr>
                            <th>Nomor PO</th>
                            <th>Jenis Pengadaan PO terkait</th>
                            <th>Start Period</th>
                            <th>End Period</th>
                            <th>Vendor</th>
                            <th>Salespoint</th>
                            <th>Nopol GS</th>
                            <th>Nopol GT</th>
                            <th>Jenis Armada</th>
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
                                    <td @if (now()->addDays(30) > \Carbon\Carbon::parse($po->end_date)) class="text-danger" @endif>
                                        {{ \Carbon\Carbon::parse($po->end_date)->translatedFormat('d F Y') }}</td>
                                @else
                                    <td>-</td>
                                    <td>-</td>
                                @endif
                                <td>{{ $po->vendor_name ? $po->vendor_name : '-' }}</td>
                                <td>{{ $po->salespoint_name }}</td>
                                <td>{{ $po->gs_plate }}</td>
                                <td>{{ $po->gt_plate }}</td>
                                <td>{{ $po->brand_name }} {{ $po->type_name }}</td>
                                <td>
                                    @if ($po->start_date && $po->end_date)
                                        {{ $po->current_ticketing ? $po->current_ticketing->type() . ' ' . $po->current_ticketing->code : 'Belum di proses' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $po->status_name }}
                                </td>

                                @if (
                                    $po->ticket_type == 'Manual' &&
                                        $po->armada_pr_manual_filepath == null &&
                                        $po->armada_po_filepath == null &&
                                        $po->armada_bastk_filepath == null)
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" id="button_attachment_1"
                                            onclick="uploadFileAttachment()" name="button_upload" id="button_upload">Upload
                                            File
                                            Attachment</button>
                                    </td>
                                @elseif (
                                    $po->ticket_type == 'Manual' &&
                                        $po->armada_pr_manual_filepath &&
                                        $po->armada_po_filepath &&
                                        $po->armada_bastk_filepath)
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
                                        $po->armada_pr_manual_filepath &&
                                        $po->armada_po_filepath &&
                                        $po->armada_bastk_filepath)
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" id="button_attachment_2"
                                            onclick="viewFileAttachment( '{{ $po->armada_pr_manual_filepath }} ',
                                          '{{ $po->armada_po_filepath }} ',
                                          '{{ $po->armada_bastk_filepath }} ')">Lihat
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
                        <table class="table table-borderless" id="armada_monitoring_table">
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
                        <div class="row update_gt_field border p-3">
                            <h5>Update GT Plate</h5>
                            <form class="form-inline" method="post" action="/armadamonitoring/updategtplate">
                                @csrf
                                <input type="hidden" name="po_number" id="po_number_update" value="">
                                <div class="form-group mr-3">
                                    <input type="text" class="form-control form-control-sm" name="gt_plate" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">Update Nomor Polisi</button>
                            </form>
                        </div>
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

    <!-- Modal -->
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
                                    <option value="getGSArmada">Armada GS</option>
                                    <option value="getMonthlyArmadaReport">Monthly Report</option>
                                    {{-- <option value="getExpiredPO">Expired PO</option> --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="getMonthlyArmadaReport_field">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Pilih Bulan</label>
                                <select class="form-control" name="month">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Pilih Tahun</label>
                                <select class="form-control" name="year">
                                    @for ($i = 0; $i < 5; $i++)
                                        @php
                                            $year = intval(now()->format('Y'));
                                            $year -= $i;
                                        @endphp
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
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
                <form action="/armadamonitoring/uploadfileattachmentarmadamonitoring" method="post"
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
                                <div class="form-group">
                                    <label class="required_field">BASTK</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_bastk" id="file_bastk" required>
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
                            <div class="form-group">
                                <label>File BASTK</label>
                                <div>
                                    <a target="_blank" id="attachment_3">tampilkan
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
            $('#monitoringPO tbody').on('click', 'tr', function() {
                let po_number = $(this).find('td:eq(0)').text().trim();
                let ticket_type = $(this).find('td:eq(1)').text().trim();
                let gt_plate = $(this).find('td:eq(7)').text().trim();
                $('#monitoringmodal').modal('show');
                $('#modal_po_number').text(po_number);
                $('#po_number_update').val(po_number);
                $('#button_upload').val(po_number);
                $('input[name="po_number"]').val(po_number);
                $('#armada_monitoring_table tbody').empty();
                $.ajax({
                    type: "get",
                    url: "/armadamonitoringpologs/" + po_number,
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
                            $('#armada_monitoring_table tbody').append(row_element);
                        });
                    },
                    error: function(response) {
                        alert("error");
                    }
                });

                if (ticket_type == 'Manual' && gt_plate == "") {
                    $('#monitoringmodal .update_gt_field').show()
                } else {
                    $('#monitoringmodal .update_gt_field').hide()
                }
            });
            $('#monitoringStatus tbody').on('click', 'tr', function() {
                let code = $(this).find('td:eq(1)').text().trim();
                let armada_ticket_id = $(this).data('ticket_id');
                let status_column_el = $(this).find('td:eq(5)');
                $.ajax({
                    type: "get",
                    url: "/armadamonitoringticketlogs/" + code,
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
                switch (export_url) {
                    case 'getGSArmada':
                        window.open('/armadamonitoring/getGSArmada');
                        break;
                    case 'getMonthlyArmadaReport':
                        let month = $('#getMonthlyArmadaReport_field select[name="month"]').val();
                        let year = $('#getMonthlyArmadaReport_field select[name="year"]').val();
                        if (month == "" || year == "") {
                            alert('Bulan dan Tahun harus dipilih');
                            return;
                        }
                        window.open('/armadamonitoring/getMonthlyArmadaReport?month=' + month + '&year=' +
                            year);
                        break;

                    default:
                        break;
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

        function viewFileAttachment(armada_pr_manual_filepath, armada_po_filepath, armada_bastk_filepath) {
            $('#viewModal').modal('show');
            console.log(armada_pr_manual_filepath);
            console.log(armada_po_filepath);
            console.log(armada_bastk_filepath);

            $('#attachment_1').attr("href",
                'storage/' + armada_pr_manual_filepath);

            $('#attachment_2').attr("href",
                'storage/' + armada_po_filepath);

            $('#attachment_3').attr("href",
                'storage/' + armada_bastk_filepath);
        }
    </script>
@endsection
