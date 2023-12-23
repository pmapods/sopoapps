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
                    <h1 class="m-0 text-dark">Monitoring Pengadaan</h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Monitoring</li>
                        <li class="breadcrumb-item active">Monitoring Pengadaan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-po-tab" data-toggle="pill" href="#pills-po" role="tab"
                    aria-controls="pills-po" aria-selected="true">Monitoring PO Manual</a>
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
                            <th>Salespoint</th>
                            <th>Status</th>
                            <th>Upload File</th>
                            <th>Lihat File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po_manuals as $po)
                            <td>{{ $po->po_number }}</td>
                            <td>Manual</td>
                            @if ($po->start_date && $po->end_date)
                                <td>{{ \Carbon\Carbon::parse($po->start_date)->translatedFormat('d F Y') }}</td>
                                <td @if (now()->addDays(60) > \Carbon\Carbon::parse($po->end_date)) class="text-danger" @endif>
                                    {{ \Carbon\Carbon::parse($po->end_date)->translatedFormat('d F Y') }}</td>
                            @else
                                <td>-</td>
                                <td>-</td>
                            @endif
                            <td>{{ $po->salespoint_name }}</td>
                            <td>{{ $po->status() }}</td>

                            @if (
                                $po->barang_jasa_form_bidding_filepath == null ||
                                    $po->barang_jasa_pr_manual_filepath == null ||
                                    $po->barang_jasa_po_filepath == null ||
                                    $po->barang_jasa_lpb_filepath == null ||
                                    $po->barang_jasa_invoice_filepath == null)
                                <td>
                                    <button type="button" class="btn btn-success btn-sm" id="button_attachment_1"
                                        onclick="uploadFileAttachment()" name="button_upload" id="button_upload">Upload
                                        File
                                        Attachment</button>
                                </td>
                            @elseif (
                                $po->barang_jasa_form_bidding_filepath ||
                                    $po->barang_jasa_pr_manual_filepath ||
                                    $po->barang_jasa_po_filepath ||
                                    $po->barang_jasa_lpb_filepath ||
                                    $po->barang_jasa_invoice_filepath)
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
                                $po->barang_jasa_form_bidding_filepath ||
                                    $po->barang_jasa_pr_manual_filepath ||
                                    $po->barang_jasa_po_filepath ||
                                    $po->barang_jasa_lpb_filepath ||
                                    $po->barang_jasa_invoice_filepath)
                                <td>
                                    <button type="button" class="btn btn-success btn-sm" id="button_attachment_2"
                                        onclick="viewFileAttachment( '{{ $po->barang_jasa_form_bidding_filepath }} ',
                                          '{{ $po->barang_jasa_pr_manual_filepath }} ',
                                          '{{ $po->barang_jasa_po_filepath }} ',
                                          '{{ $po->barang_jasa_lpb_filepath }} ',
                                          '{{ $po->barang_jasa_invoice_filepath }} ')">Lihat
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

    <!-- Modal -->
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

    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File Attachment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/ticketmonitoring/uploadfileattachmentticketmonitoring" method="post"
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
                                    <label>Form Bidding</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_bidding" id="file_bidding">
                                </div>
                                <div class="form-group">
                                    <label>PR Manual</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_pr_manual" id="file_pr_manual">
                                </div>
                                <div class="form-group">
                                    <label>PO</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_po" id="file_po">
                                </div>
                                <div class="form-group">
                                    <label>LPB</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_lpb" id="file_lpb">
                                </div>
                                <div class="form-group">
                                    <label>Invoice</label>
                                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                                        name="file_invoice" id="file_invoice">
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
                                <label>File Form Bidding</label>
                                <div>
                                    <a target="_blank" id="attachment_1">tampilkan
                                        attachment</a>
                                    <span style="display:none" id="strip_attachment_1">
                                        -
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>File PR Manual</label>
                                <div>
                                    <a target="_blank" id="attachment_2">tampilkan
                                        attachment</a>
                                    <span style="display:none" id="strip_attachment_2">
                                        -
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>File PO</label>
                                <div>
                                    <a target="_blank" id="attachment_3">tampilkan
                                        attachment</a>
                                    <span style="display:none" id="strip_attachment_3">
                                        -
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>File LPB</label>
                                <div>
                                    <a target="_blank" id="attachment_4">tampilkan
                                        attachment</a>
                                    <span style="display:none" id="strip_attachment_4">
                                        -
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>File Invoice</label>
                                <div>
                                    <a target="_blank" id="attachment_5">tampilkan
                                        attachment</a>
                                    <span style="display:none" id="strip_attachment_5">
                                        -
                                    </span>
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
        $(document).ready(function() {
            var table = $('#monitoringStatus').DataTable(datatable_settings);
            var table_po_manual = $('#monitoringPO').DataTable(datatable_settings);
            $('#monitoringStatus tbody').on('click', 'tr', function() {
                let code = $(this).find('td:eq(1)').text().trim();
                let ticket_id = $(this).data('ticket_id');
                let status_column_el = $(this).find('td:eq(5)');
                $.ajax({
                    type: "get",
                    url: "/ticketmonitoringlogs/" + ticket_id,
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

            $('#monitoringPO tbody').on('click', 'tr', function() {
                let po_number = $(this).find('td:eq(0)').text().trim();
                $('#button_upload').val(po_number);
                $('input[name="po_number"]').val(po_number);
            });
        });

        function uploadFileAttachment() {
            $('#uploadModal').modal('show');
        }

        function viewFileAttachment(barang_jasa_form_bidding_filepath,
            barang_jasa_pr_manual_filepath,
            barang_jasa_po_filepath,
            barang_jasa_lpb_filepath,
            barang_jasa_invoice_filepath) {

            $('#viewModal').modal('show');

            if (barang_jasa_form_bidding_filepath.trim()) {
                $('#attachment_1').attr("href",
                    'storage/' + barang_jasa_form_bidding_filepath);
                $('#attachment_1').show();
                console.log(barang_jasa_form_bidding_filepath);
            } else {
                $('#attachment_1').hide();
                $('#strip_attachment_1').show();
            }

            if (barang_jasa_pr_manual_filepath.trim()) {
                $('#attachment_2').attr("href",
                    'storage/' + barang_jasa_pr_manual_filepath);
                $('#attachment_2').show();
                console.log(barang_jasa_pr_manual_filepath);
            } else {
                $('#attachment_2').hide();
                $('#strip_attachment_2').show();
            }

            if (barang_jasa_po_filepath.trim()) {
                $('#attachment_3').attr("href",
                    'storage/' + barang_jasa_po_filepath);
                $('#attachment_3').show();
                console.log(barang_jasa_po_filepath);
            } else {
                $('#attachment_3').hide();
                $('#strip_attachment_3').show();
            }

            if (barang_jasa_lpb_filepath.trim()) {
                $('#attachment_4').attr("href",
                    'storage/' + barang_jasa_lpb_filepath);
                $('#attachment_4').show();
                console.log(barang_jasa_lpb_filepath);
            } else {
                $('#attachment_4').hide();
                $('#strip_attachment_4').show();
            }

            if (barang_jasa_invoice_filepath.trim()) {
                $('#attachment_5').attr("href",
                    'storage/' + barang_jasa_invoice_filepath);
                $('#attachment_5').show();
                console.log(barang_jasa_invoice_filepath);
            } else {
                $('#attachment_5').hide();
                $('#strip_attachment_5').show();
            }

        }
    </script>
@endsection
