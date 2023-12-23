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
                    <h1 class="m-0 text-dark">Monitoring PEST @if (request()->get('status') == 4)
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
                        <li class="breadcrumb-item active">Monitoring PEST @if (request()->get('status') == 4)
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
                <a href="/pestmonitoring?status=4" class="btn btn-success mr-2">Tampilkan Closed PO</a>
            @else
                <a href="/pestmonitoring" class="btn btn-primary mr-2">Tampilkan PO Aktif</a>
            @endif
        </div>
        <table id="monitoringPO" class="table table-sm small table-bordered dataTable" role="grid">
            <thead>
                <tr>
                    <th>Nomor PO</th>
                    <th>Jenis Pengadaan PO terkait</th>
                    <th width="13%">Start Period</th>
                    <th width="13%">End Period</th>
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
                        <td>{{ \Carbon\Carbon::parse($po->start_date)->translatedFormat('d F Y') }}</td>
                        <td @if (now()->addDays(30) > \Carbon\Carbon::parse($po->end_date)) class="text-danger" @endif>
                            {{ \Carbon\Carbon::parse($po->end_date)->translatedFormat('d F Y') }}</td>
                        <td>{{ $po->vendor_name ? $po->vendor_name : '-' }}</td>
                        <td>{{ $po->salespoint_name }}</td>
                        <td>
                            @if ($po->start_date && $po->end_date)
                                {{ $po->current_ticketing ? $po->current_ticketing->request_type() . ' ' . $po->current_ticketing->code : 'Belum di proses' }}
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

    <!-- Modal -->
    <div class="modal fade" id="poupdatemodal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/pestmonitoring/updatepo" method="post" enctype="multipart/form-data" id="submitform">
                    @csrf
                    <input type="hidden" name="type" value="pest_control">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Nomor PO Lama</label>
                                    <input type="text" class="form-control" name="old_po_number" readonly>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label>SalesPoint</label>
                                    <input type="text" class="form-control" name="salespoint_name" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Nomor PO Baru</label>
                                    <input type="text" class="form-control" name="new_po_number" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Vendor</label>
                                    <input type="text" class="form-control" name="vendor" required>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Start Period</label>
                                    <input type="date" class="form-control" name="start_period" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">End Period</label>
                                    <input type="date" class="form-control" name="end_period" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
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
                <form action="/pestmonitoring/uploadfileattachmentpestmonitoring" method="post"
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
            var table = $('#monitoringPO').DataTable(datatable_settings);
            $('.dataTables_filter input').val(po_number);
            $('#monitoringPO tbody').on('click', 'tr', function() {
                let po_number = $(this).find('td:eq(0)').text().trim();
                let salespoint_name = $(this).find('td:eq(4)').text().trim();
                let vendor_name = $(this).find('td:eq(5)').text().trim();
                $('#submitform')[0].reset();
                $('input[name="old_po_number"]').val(po_number);
                $('input[name="salespoint_name"]').val(salespoint_name);
                $('input[name="vendor"]').val(vendor_name);
                $('#poupdatemodal').modal('show');
                $('#button_upload').val(po_number);
                $('input[name="po_number"]').val(po_number);
            });
        });

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
