@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{ $budget->code }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Budget</li>
                        <li class="breadcrumb-item">Armada Budget</li>
                        <li class="breadcrumb-item active">{{ $budget->code }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="row">
            <div class="col-6">
                <table class="table table-borderless table-sm">
                    <tbody>
                        <tr>
                            <td class="font-weight-bold">Salespoint</td>
                            <td>: {{ $budget->salespoint->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Waktu Pengajuan</td>
                            <td>: {{ $budget->created_at->translatedFormat('d F Y (H:i)') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jenis Pengajuan</td>
                            <td>: {{ $budget->type }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Nama Pengaju</td>
                            <td>: {{ $budget->created_by_employee->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tahun</td>
                            <td>: {{ $budget->year ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-6 text-right">
                <a class="btn btn-info mr-2"
                    href='/armadabudget/create/template?budget_upload_id={{ $budget->id }}'>Download</a>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipe Armada</th>
                            <th>Kode Vendor</th>
                            <th>Nama Vendor</th>
                            <th>Qty</th>
                            <th>Value</th>
                            <th>Amount</th>
                            <th>Pending</th>
                            <th>Terpakai</th>
                            <th>Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budget->budget_detail as $b)
                            <tr>
                                <td>{{ $b->armada_type_name }}</td>
                                <td>{{ $b->vendor_code }}</td>
                                <td>{{ $b->vendor_name }}</td>
                                <td>{{ $b->qty }}</td>
                                <td class="rupiah_text">{{ $b->value }}</td>
                                <td class="rupiah_text">{{ $b->value * $b->qty }}</td>
                                <td>{{ $b->pending_quota }}</td>
                                <td>{{ $b->used_quota }}</td>
                                <td>{{ $b->qty - $b->pending_quota - $b->used_quota }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if ($budget->status == 0 || $budget->status == 1)
            <div class="row mt-3">
                <div class="col-md-12 d-flex flex-row justify-content-center align-items-center">
                    @foreach ($budget->authorizations as $authorization)
                        <div class="d-flex text-center flex-column mr-3">
                            <div class="font-weight-bold">{{ $authorization->as }}</div>
                            @if (($budget->current_authorization()->id ?? -1) == $authorization->id)
                                <div class="text-warning">Pending</div>
                            @endif

                            @if ($authorization->status == 1)
                                <div class="text-success">Approved {{ $authorization->updated_at->format('Y-m-d (H:i)') }}
                                </div>
                            @endif
                            <div>{{ $authorization->employee_name }} ({{ $authorization->employee_position }})</div>
                        </div>
                        @if (!$loop->last)
                            <div class="mr-3">
                                <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if ($budget->status == -1 || $budget->status == 1)
            <form action="/armadabudget/reviseBudget" method="post" id="submitform" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="budget_upload_id" value="{{ $budget->id }}">
                <div id="revise_section" @if ($budget->status == 1) style="display: none" @endif>
                    <div>
                        <h3>Upload Revisi</h3>
                    </div>
                    @if ($budget->status == -1)
                        <div class="text-danger"><b>Ditolak Oleh :&nbsp;</b>{{ $budget->rejected_by_employee->name }}</div>
                        <div class="text-danger"><b>Alasan penolakan :&nbsp;</b>{{ $budget->reject_notes }}</div>
                    @endif
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <div class="form-group mr-2">
                                <label class="required_field">Pilih File Template</label>
                                <input type="file" class="form-control-file" name='file' onclick="this.value = null;"
                                    placeholder="Pilih File Template armada" required id="file_template"
                                    accept=".xls, .xlsx" />
                            </div>
                            <div>
                                <a class="btn btn-info mr-2"
                                    href='/armadabudget/create/template?budget_upload_id={{ $budget->id }}'>Download
                                    Template</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            Pilihan Salespoint File Upload : <span class="upload_selected_salespoint"></span>
                        </div>
                        <div class="col-12">
                            <table class="table" id="template_table">
                                <thead>
                                    <tr>
                                        <th>Tipe Armada</th>
                                        <th>Kode Vendor</th>
                                        <th>Nama Vendor</th>
                                        <th>Qty</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                            <span class="spinner-border text-danger" id="table_loading" role="status"
                                style="display: none">
                                <span class="sr-only">Loading...</span>
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="required_field">Pilih Matriks Approval</label>
                            <select class="form-control" id="authorization" name="authorization_id" required>
                                <option value="">-- Pilih Matriks Approval --</option>
                                @php
                                    if ($budget->status == -1) {
                                        $authorizations = $newauthorization;
                                    } else {
                                        $authorizations = $reviseauthorization;
                                    }
                                @endphp
                                @foreach ($authorizations as $authorization)
                                    @php
                                        $list = $authorization->authorization_detail;
                                        $string = '';
                                        foreach ($list as $key => $author) {
                                            $string = $string . $author->employee->name;
                                            $open = $author->employee_position;
                                            if (count($list) - 1 != $key) {
                                                $string = $string . ' -> ';
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $authorization->id }}">{{ $string }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger">*matriks approval yang muncul berdasarkan pilihan salespoint</small>
                        </div>
                    </div>
                </div>

                @if ($budget->status == 1)
                    <div class="text-center mt-3 d-flex flex-row justify-content-center">
                        <button type="button" id="revise_trigger" class="btn btn-primary mr-2">Ajukan Revisi</button>
                        <button type="submit" id="revise_button" class="btn btn-primary mr-2"
                            style="display: none">Revise</button>
                    </div>
                @endif
                @if ($budget->status == -1)
                    <div class="text-center mt-3 d-flex flex-row justify-content-center">
                        <button type="submit" class="btn btn-primary mr-2">Revise</button>
                        <button type="button" class="btn btn-danger mr-2" onclick="popupTerminateModal()">Batalkan
                            Pengajuan</button>
                    </div>
                @endif
            </form>
        @endif

        @if ($budget->status == 0 && ($budget->current_authorization()->employee_id ?? -1) == Auth::user()->id)
            <div class="text-center mt-3 d-flex flex-row justify-content-center">
                <button type="button" class="btn btn-success mr-2"
                    onclick="approveAuthorization('{{ $budget->id }}')">Approve</button>
                <button type="button" class="btn btn-danger mr-2" onclick="popupRejectModal()">Reject</button>
            </div>
        @endif
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rejectModalForm">
                    <input type="hidden" name="upload_budget_id" value="{{ $budget->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Pengajuan Budget</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class='required_field'>Masukan alasan pembatalan</label>
                            <textarea class="form-control" name="reason" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">Reject Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="terminateModal" tabindex="-1" role="dialog" data-backdrop="static"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="terminateModalForm">
                    <input type="hidden" name="upload_budget_id" value="{{ $budget->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Terminate Pengajuan Budget</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class='required_field'>Masukan alasan pembatalan</label>
                            <textarea class="form-control" name="reason" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">Terminate Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form method="post" action="" id="submitform">
        @csrf
        <div></div>
    </form>

    <div class="modal fade" id="uploadInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Info</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

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
        var csrf = "{{ csrf_token() }}";
        $(document).ready(function() {
            $('#rejectModalForm').submit(function(event) {
                event.preventDefault();
                const data = Object.fromEntries(new FormData(event.target).entries());
                rejectAuthorization(data.upload_budget_id, data.reason);
            });
            $('#terminateModalForm').submit(function(event) {
                event.preventDefault();
                const data = Object.fromEntries(new FormData(event.target).entries());
                terminateBudgetUpload(data.upload_budget_id, data.reason);
            });

            $('#file_template').change(function(evt) {
                $('#table_loading').show();
                var selectedFile = evt.target.files[0];
                var fd = new FormData();
                var files = $('#file_template')[0].files;
                $('#template_table tbody').empty();
                // Check file selected or not
                if (files.length > 0) {
                    fd.append('file', files[0]);
                    fd.append('_token', csrf);

                    $.ajax({
                        url: '/armadabudget/create/readtemplate',
                        type: 'POST',
                        data: fd,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response != 0) {
                                let data = response.data;
                                let errordata = response.errordata;
                                let error = response.error;
                                if (!error) {
                                    $('.upload_selected_salespoint').text(response.salespoint
                                        .name + " || " + response.salespoint.code);
                                    data.forEach(item => {
                                        let append_text = "<tr>";
                                        append_text += '<td>' + item.armada_type_name +
                                            '</td>';
                                        append_text += '<td>' + item.vendor_code +
                                            '</td>';
                                        append_text += '<td>' + item.vendor_name +
                                            '</td>';
                                        append_text += '<td>' + item.qty + '</td>';
                                        append_text += '<td>' + setRupiah(item.value) +
                                            '</td>';
                                        append_text += "</tr>";
                                        $('#template_table tbody').append(append_text);
                                    });
                                    // show info
                                    showUploadInfo(data, errordata);
                                } else {
                                    alert(response.message);
                                }

                            } else {
                                alert('file not uploaded');
                            }
                        },
                        complete: function() {
                            $('#table_loading').hide();
                        }
                    });
                } else {
                    alert("Harap memilih file");
                }
            });

            $('#revise_trigger').click(function() {
                $(this).hide();
                $('#revise_button').show();
                $('#revise_section').show();
            });
        });

        function approveAuthorization(upload_budget_id) {
            $('#submitform').prop('action', '/armadabudget/approvebudgetauthorization');
            $('#submitform').find('div').empty();
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="budget_upload_id" value="' + upload_budget_id +
                '">');
            $('#submitform').submit();
        }

        function popupRejectModal() {
            $('#rejectModal textarea').val('');
            $('#rejectModal').modal('show');
        }

        function popupTerminateModal() {
            $('#terminateModal textarea').val('');
            $('#terminateModal').modal('show');
        }

        function rejectAuthorization(upload_budget_id, reason) {
            $('#submitform').prop('action', '/armadabudget/rejectbudgetauthorization');
            $('#submitform').find('div').append('<input type="hidden" name="budget_upload_id" value="' + upload_budget_id +
                '">');
            $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').prop('method', 'POST');
            $('#submitform').submit();
        }

        function terminateBudgetUpload(upload_budget_id, reason) {
            $('#submitform').prop('action', '/armadabudget/terminateBudget');
            $('#submitform').find('div').append('<input type="hidden" name="budget_upload_id" value="' + upload_budget_id +
                '">');
            $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').prop('method', 'POST');
            $('#submitform').submit();
        }

        function showUploadInfo(data, errordata) {
            $('#uploadInfoModal .modal-body').empty();
            $('#uploadInfoModal .modal-body').append('<div><b>Berhasil :</b>' + data.length + '</div>');
            $('#uploadInfoModal .modal-body').append('<div><b>Gagal :</b>' + errordata.length + '</div>');
            $('#uploadInfoModal .modal-body').append('<div><b>List Gagal Input :</b></div>');
            let table_append_text =
                '<table class="table table-sm table-bordered small"><thead><tr><th>Armada</th><th>Vendor</th><th>qty</th><th>value</th></thead><tbody>';
            errordata.forEach(item => {
                table_append_text += '<tr><td>' + item.armadatype + '</td><td>' + item.vendor + '</td><td>' + item
                    .qty + '</td><td>' + item.value + '</td></tr>'
            });
            table_append_text += '</tbody></table>';
            $('#uploadInfoModal .modal-body').append(table_append_text);
            $('#uploadInfoModal').modal('show');
        }
    </script>
@endsection
