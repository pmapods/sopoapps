@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Upload PO Manual</h1>
                    {{-- <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                    <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                </div> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Upload PO Manual</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#addUploadPoManualModal">
                    Upload PO Manual
                </button>
                <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#listUploadFailedModal">
                    List Failed Upload PO Manual
                </button>
                <div>
                    <button type="button" class="btn btn-info" onclick="downloadtemplate()">Download Template Po Manual</button>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="uploadPOManualDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>Id</th>
                        <th>SalesPoint</th>
                        <th>Po Number</th>
                        <th>Type Budget</th>
                        <th>Status Upload</th>
                        <th>Upload Date</th>
                </thead>
                <tbody>
                    @foreach ($po_manuals as $key => $po_manual)
                        <tr data-po-manual="{{ $po_manual }}">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $po_manual->salespoint_name }}
                            </td>
                            <td>
                                {{ $po_manual->po_number }}
                            </td>
                            <td>{{ $po_manual->type_budget }}</td>
                            <td>{{ $po_manual->status_upload }}</td>
                            <td>{{ $po_manual->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addUploadPoManualModal" tabindex="-1" data-backdrop="static" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload PO Manual</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('upload.excel') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Upload File Po Manual</label>
                                    <input type="file" class="form-control-file validatefilesize"
                                        name="po_manual_file" accept=".xls, .xlsx" required>
                                    <small class="text-danger">*xls, xlsx (MAX 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="listUploadFailedModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">List Failed Upload PO Manual</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="content-body px-4">
                    <div class="table-responsive">
                        <table id="uploadPOManualDT" class="table table-bordered table-striped dataTable" role="grid">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>SalesPoint</th>
                                    <th>Po Number</th>
                                    <th>Type Budget</th>
                                    <th>Status Upload</th>
                                    <th>Reason</th>
                                    <th>Upload Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($po_manual_failed as $key => $po_manual)
                                    <tr data-po-manual="{{ $po_manual }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            {{ $po_manual->salespoint_name }}
                                        </td>
                                        <td>
                                            {{ $po_manual->po_number }}
                                        </td>
                                        <td>{{ $po_manual->type_budget }}</td>
                                        <td>{{ $po_manual->status_upload }}</td>
                                        <td>{{ $po_manual->reason_upload }}</td>
                                        <td>{{ $po_manual->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('.autonumber').change(function() {
                autonumber($(this));
            });

            var table = $('#uploadPOManualDT').DataTable(datatable_settings);
            $('#uploadPOManualDT tbody').on('click', 'tr', function() {
                let modal = $('#detailPoManualModal', '#listUploadFailedModal');
                let data = $(this).data('po_manual');
                modal.find('input[name="armada_iidd"]').val(data['id']);
                modal.find('select[name="salespoint_id"]').val((data['salespoint_id'] == null) ? '' : data[
                    'salespoint_id']);
                modal.find('select[name="salespoint_id"]').trigger('change');
                modal.find('select[name="po_number"]').val(data['po_number']);
                modal.find('select[name="po_number"]').trigger('change');
                modal.find('select[name="category_name"]').val(data['category_name']);
                modal.find('select[name="category_name"]').trigger('change');
                modal.modal('show');
            });

        })
        function downloadtemplate() {
            window.location.href = "template/upload_po_manual_template.xlsx";
        }
    </script>
@endsection