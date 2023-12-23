@extends('Layout.app')
@section('local-css')
    <style>
        table.table-bordered {
            border: 1px solid rgb(128, 128, 128) !important;
        }

        table.table-bordered>thead>tr>th {
            border: 1px solid rgb(128, 128, 128) !important;
        }

        table.table-bordered>tbody>tr>td {
            border: 1px solid rgb(128, 128, 128) !important;
        }
    </style>
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
                        <li class="breadcrumb-item">HO Budget</li>
                        <li class="breadcrumb-item active">{{ $budget->code }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="row">
            <div class="col-8 row">
                <div class="col-3 font-weight-bold">Salespoint</div>
                <div class="col-3">{{ $budget->salespoint->name }}</div>
                <div class="col-3 font-weight-bold">Divisi</div>
                <div class="col-3">{{ $budget->division }}</div>
                <div class="col-3 font-weight-bold">Tahun</div>
                <div class="col-3">{{ $budget->year }}</div>
                <div class="col-3 font-weight-bold">Waktu Pengajuan</div>
                <div class="col-3">{{ $budget->created_at->translatedFormat('d F Y (H:i)') }}</div>
                <div class="col-3 font-weight-bold">Jenis Pengajuan</div>
                <div class="col-3">{{ $budget->type }}</div>
                <div class="col-3 font-weight-bold">Nama Pengaju</div>
                <div class="col-3">{{ $budget->created_by_employee->name }}</div>
            </div>
            <div class="col-4">
                <button type="button" class="btn btn-info" onclick="downloadtemplate()">Download Template</button>
            </div>
        </div>

        <div class="row">
            {{-- <div class="col-12 h5 mt-3">Monthly</div> --}}
            <div class="col-12 table-responsive mt-3">
                <table class="table table-sm small table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2">Kode</th>
                            <th rowspan="2">Kategori</th>
                            <th rowspan="2">Nama</th>
                            @for ($i = 1; $i <= 12; $i++)
                                <th colspan="2">{{ \Carbon\Carbon::create()->month($i)->format('F') }}</th>
                            @endfor
                        </tr>
                        <tr>
                            @for ($i = 1; $i <= 12; $i++)
                                <th>Qty</th>
                                <th>Value</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budget->budget_detail as $item)
                            <tr>
                                <td class="text-nowrap">{{ $item->code }}</td>
                                <td class="text-nowrap">{{ $item->category }}</td>
                                <td class="text-nowrap">{{ $item->name }}</td>
                                @for ($i = 0; $i < 12; $i++)
                                    @if (json_decode($item->values)[$i]->qty != 0 || json_decode($item->values)[$i]->value != 0)
                                        <td>{{ json_decode($item->values)[$i]->qty }}</td>
                                        <td class="rupiah text-nowrap">{{ json_decode($item->values)[$i]->value }}</td>
                                    @else
                                        <td>-</td>
                                        <td>-</td>
                                    @endif
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <span class="spinner-border text-danger" id="monthly_table_loading" role="status" style="display: none">
                    <span class="sr-only">Loading...</span>
                </span>
            </div>
            {{-- <hr>
        <div class="col-12 h5">Quarterly</div>
        <div class="col-12 table-responsive">
            <table class="table table-sm small table-bordered" id="quarterly_template_table">
                <thead>
                    <tr>
                        <th rowspan="2">Kode</th>
                        <th rowspan="2">Kategori</th>
                        <th rowspan="2">Nama</th>
                        @for ($i = 1; $i <= 4; $i++)
                            <th colspan="2">Q{{ $i }}</th>
                        @endfor
                    </tr>
                    <tr>
                        @for ($i = 1; $i <= 4; $i++)
                            <th>qty</th>
                            <th>value</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($budget->budget_detail->where('frequency', 'quarterly') as $item)
                    <tr>
                        <td class="text-nowrap">{{ $item->code }}</td>
                        <td class="text-nowrap">{{ $item->category }}</td>
                        <td class="text-nowrap">{{ $item->name }}</td>
                        @for ($i = 0; $i < 4; $i++)
                            <td>{{ json_decode($item->values)[$i]->qty }}</td>
                            <td class="rupiah text-nowrap">{{ json_decode($item->values)[$i]->value }}</td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <span class="spinner-border text-danger" id="quarterly_table_loading" role="status" style="display: none">
                <span class="sr-only">Loading...</span>
            </span>
        </div>
        <hr>
        <div class="col-12 h5">Yearly</div>
        <div class="col-12 table-responsive">
            <table class="table table-sm small table-bordered" id="yearly_template_table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Kategori</th>
                        <th>Nama</th>
                        <th>qty</th>
                        <th>value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($budget->budget_detail->where('frequency', 'yearly') as $item)
                    <tr>
                        <td class="text-nowrap">{{ $item->code }}</td>
                        <td class="text-nowrap">{{ $item->category }}</td>
                        <td class="text-nowrap">{{ $item->name }}</td>
                        @for ($i = 0; $i < 1; $i++)
                            <td>{{ json_decode($item->values)[$i]->qty }}</td>
                            <td class="rupiah text-nowrap">{{ json_decode($item->values)[$i]->value }}</td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <span class="spinner-border text-danger" id="yearly_table_loading" role="status" style="display: none">
                <span class="sr-only">Loading...</span>
            </span>
        </div>
        <hr>
        <div class="col-12 h5">If Any</div>
        <div class="col-12 table-responsive">
            <table class="table table-sm small table-bordered" id="ifany_template_table">
                <thead>
                    <tr>
                        <th rowspan="2">Kode</th>
                        <th rowspan="2">Kategori</th>
                        <th rowspan="2">Nama</th>
                        @for ($i = 1; $i <= 12; $i++)
                            <th colspan="2">{{ \Carbon\Carbon::create()->month($i)->format('F') }}</th>
                        @endfor
                    </tr>
                    <tr>
                        @for ($i = 1; $i <= 12; $i++)
                            <th>qty</th>
                            <th>value</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class='text-center' colspan="27">Tidak Ada Data</td>
                    </tr>
                </tbody>
            </table>

            <span class="spinner-border text-danger" id="ifany_table_loading" role="status" style="display: none">
                <span class="sr-only">Loading...</span>
            </span>
        </div> --}}
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
            <form action="/ho_budget/reviseBudget" method="post" id="submitform" enctype="multipart/form-data">
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
                                <input type="file" required class="form-control-file" name='file'
                                    onclick="this.value = null;" placeholder="Pilih File Template HO" id="file_template"
                                    accept=".xls, .xlsx" />
                            </div>
                            <div>
                                <button type="button" class="btn btn-info" onclick="downloadtemplate()">Download
                                    Template</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col table-responsive">
                            <table class="table table-sm small table-bordered border-dark" id="monthly_template_table">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Kode</th>
                                        <th rowspan="2">Kategori</th>
                                        <th rowspan="2">Nama</th>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <th colspan="2">{{ \Carbon\Carbon::create()->month($i)->format('F') }}</th>
                                        @endfor
                                    </tr>
                                    <tr>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <th>Qty</th>
                                            <th>Value</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center" colspan="27">Tidak Ada Data</td>
                                    </tr>
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
                            <select class="form-control select2" id="authorization" name="authorization_id" required>
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
                                    <option value="{{ $authorization->id }}">{{ $string }}
                                        ({{ $authorization->notes }})</option>
                                @endforeach
                            </select>
                            <small class="text-danger">*Matriks Approval yang muncul berdasarkan pilihan salespoint</small>
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
                $('#monthly_table_loading').show();
                // $('#quarterly_table_loading').show();
                // $('#yearly_table_loading').show();
                // $('#ifany_table_loading').show();
                var selectedFile = evt.target.files[0];
                var fd = new FormData();
                var files = $('#file_template')[0].files;
                $('#monthly_template_table tbody').empty();
                // $('#quarterly_template_table tbody').empty();
                // $('#yearly_template_table tbody').empty();
                // $('#ifany_template_table tbody').empty();
                // Check file selected or not
                if (files.length > 0) {
                    fd.append('file', files[0]);
                    fd.append('_token', csrf);

                    $.ajax({
                        url: '/ho_budget/create/readtemplate',
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
                                    data.monthly.forEach(item => {
                                        let append_text = "<tr>";
                                        append_text += '<td class="text-nowrap">' + item
                                            .code + '</td>';
                                        append_text += '<td class="text-nowrap">' + item
                                            .category + '</td>';
                                        append_text += '<td class="text-nowrap">' + item
                                            .name + '</td>';
                                        for (let i = 1; i <= 12; i++) {
                                            let result = item.values.filter(obj => {
                                                return obj.months === i;
                                            });
                                            if (result.length > 0 && (result[0].qty !=
                                                    0 || result[0].value != 0)) {
                                                append_text += '<td>' + result[0].qty +
                                                    '</td>';
                                                append_text +=
                                                    '<td class="text-nowrap">' +
                                                    setRupiah(result[0].value) +
                                                    '</td>';
                                            } else {
                                                append_text += '<td>-</td>';
                                                append_text += '<td>-</td>';
                                            }
                                        }
                                        append_text += "</tr>";
                                        $('#monthly_template_table tbody').append(
                                            append_text);
                                    });
                                    if (data.monthly.length == 0) {
                                        let append_text = "<tr>";
                                        append_text +=
                                            '<td class="text-center" colspan="27">Tidak Ada Data</td>';
                                        append_text += "</tr>";
                                        $('#monthly_template_table tbody').append(append_text);
                                    }
                                    // data.quarterly.forEach(item => {
                                    //     let append_text = "<tr>";
                                    //     append_text += '<td class="text-nowrap">'+item.code+'</td>';
                                    //     append_text += '<td class="text-nowrap">'+item.category+'</td>';
                                    //     append_text += '<td class="text-nowrap">'+item.name+'</td>';
                                    //     for(let i = 1; i <= 4; i++){
                                    //         let result = item.values.filter(obj => {
                                    //             return obj.months === i;
                                    //         });
                                    //         if(result.length > 0){
                                    //             append_text += '<td>'+ result[0].qty +'</td>';
                                    //             append_text += '<td class="text-nowrap">'+setRupiah(result[0].value)+'</td>';
                                    //         }else{
                                    //             append_text += '<td>-</td>';
                                    //             append_text += '<td>-</td>';
                                    //         }
                                    //     }
                                    //     append_text += "</tr>";
                                    //     $('#quarterly_template_table tbody').append(append_text);
                                    // });
                                    // data.yearly.forEach(item => {
                                    //     let append_text = "<tr>";
                                    //     append_text += '<td class="text-nowrap">'+item.code+'</td>';
                                    //     append_text += '<td class="text-nowrap">'+item.category+'</td>';
                                    //     append_text += '<td class="text-nowrap">'+item.name+'</td>';
                                    //     append_text += '<td>'+ item.qty +'</td>';
                                    //     append_text += '<td class="text-nowrap">'+setRupiah(item.value)+'</td>';
                                    //     append_text += "</tr>";
                                    //     $('#yearly_template_table tbody').append(append_text);
                                    // });
                                    // data.ifany.forEach(item => {
                                    //     let append_text = "<tr>";
                                    //     append_text += '<td class="text-nowrap">'+item.code+'</td>';
                                    //     append_text += '<td class="text-nowrap">'+item.category+'</td>';
                                    //     append_text += '<td class="text-nowrap">'+item.name+'</td>';
                                    //     for(let i = 1; i <= 12; i++){
                                    //         let result = item.values.filter(obj => {
                                    //             return obj.months === i;
                                    //         });
                                    //         if(result.length > 0){
                                    //             append_text += '<td>'+ result[0].qty +'</td>';
                                    //             append_text += '<td class="text-nowrap">'+setRupiah(result[0].value)+'</td>';
                                    //         }else{
                                    //             append_text += '<td>-</td>';
                                    //             append_text += '<td>-</td>';
                                    //         }
                                    //     }
                                    //     append_text += "</tr>";
                                    //     $('#ifany_template_table tbody').append(append_text);
                                    // });
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
                            $('#monthly_table_loading').hide();
                            // $('#quarterly_table_loading').hide();
                            // $('#yearly_table_loading').hide();
                            // $('#ifany_table_loading').hide();
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
            $('#submitform').prop('action', '/ho_budget/approvebudgetauthorization');
            $('#submitform').find('div').empty();
            $('#submitform').find('div').append('<input type="hidden" name="budget_upload_id" value="' + upload_budget_id +
                '">');
            $('#submitform').prop('method', 'POST');
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
            $('#submitform').prop('action', '/ho_budget/rejectbudgetauthorization');
            $('#submitform').find('div').append('<input type="hidden" name="budget_upload_id" value="' + upload_budget_id +
                '">');
            $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').prop('method', 'POST');
            $('#submitform').submit();
        }

        function terminateBudgetUpload(upload_budget_id, reason) {
            $('#submitform').prop('action', '/ho_budget/terminateBudget');
            $('#submitform').find('div').append('<input type="hidden" name="budget_upload_id" value="' + upload_budget_id +
                '">');
            $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').prop('method', 'POST');
            $('#submitform').submit();
        }

        function showUploadInfo(data, errordata) {
            $('#uploadInfoModal .modal-body').empty();
            $('#uploadInfoModal .modal-body').append('<div><b>Berhasil :</b>' + data.monthly.length + '</div>');
            $('#uploadInfoModal .modal-body').append('<div><b>Gagal :</b>' + errordata.monthly.length + '</div>');
            $('#uploadInfoModal .modal-body').append('<div><b>List Gagal Input :</b></div>');
            let table_append_text =
                '<table class="table table-sm table-bordered small"><thead><tr><th>Nama</th><th>value</th></thead><tbody>';
            errordata.monthly.forEach(item => {
                table_append_text += '<tr><td>' + item.name + '</td><td>' + item.error + '</td></tr>'
            });
            table_append_text += '</tbody></table>';
            $('#uploadInfoModal .modal-body').append(table_append_text);
            $('#uploadInfoModal').modal('show');
        }

        function downloadtemplate() {
            let salespoint_id = @json($budget->salespoint_id);
            let divisi = @json($budget->division);
            let year = @json($budget->year);
            let budget_upload_id = @json($budget->id);
            if (salespoint_id == "") {
                alert('Salespoint harus dipilih');
                return;
            }
            if (divisi == "") {
                alert('Divisi harus dipilih');
                return;
            }
            if (year == "") {
                alert('Tahun harus dipilih');
                return;
            }
            window.location.href = "/ho_budget/create/template?salespoint_id=" + salespoint_id + "&divisi=" + divisi +
                "&year=" + year + "&budget_upload_id=" + budget_upload_id;
        }
    </script>
@endsection
