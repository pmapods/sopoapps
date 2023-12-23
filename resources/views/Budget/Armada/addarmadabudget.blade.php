@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Tambah Armada Budget</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Budget</li>
                        <li class="breadcrumb-item">Armada Budget</li>
                        <li class="breadcrumb-item active">Tambah Armada Budget</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <form action="/createBudgetRequest/armada" method="post" id="submitform" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-2">
                    <div class="form-group">
                        <label class="required_field">Pilihan Area / SalesPoint</label>
                        <select class="form-control select2" name="salespoint_id" id="salespoint_select">
                            <option value="" data-isjawasumatra="-1">-- Pilih SalesPoint --</option>
                            @foreach ($available_salespoints as $region)
                                <optgroup label="{{ $region->first()->region_name() }}">
                                    @foreach ($region as $salespoint)
                                        <option value="{{ $salespoint->id }}">{{ $salespoint->name }} --
                                            {{ $salespoint->code }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <label class="required_field">Tahun</label>
                        <select class="form-control" name="year" required>
                            <option value="">-- Pilih Tahun --</option>
                            @for ($i = 0; $i < 5; $i++)
                                <option value="{{ now()->format('Y') + $i }}">{{ now()->format('Y') + $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-4 d-flex align-items-center">
                    <div class="form-group mr-2">
                        <label class="required_field">Pilih File Template</label>
                        <input type="file" class="form-control-file" name="file" onclick="this.value=null;"
                            placeholder="Pilih File Template Armada" id="file_template" required accept=".xls, .xlsx" />
                    </div>
                    <div>
                        <a class="btn btn-info mr-2" href='/armadabudget/create/template'>Download Template</a>
                    </div>
                </div>
                <div class="col-2 d-flex align-items-center justify-content-end">
                    <button type="button" class="btn btn-warning" id="oldbudget_button" data-toggle="modal"
                        data-target="#oldbudget_modal" style="display: none">
                        Tampilkan Budget Lama
                    </button>
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

                    <span class="spinner-border text-danger" id="table_loading" role="status" style="display: none">
                        <span class="sr-only">Loading...</span>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="required_field">Pilih Matriks Approval</label>
                        <select class="form-control" id="authorization" name="authorization_id" disabled required>
                            <option value="">-- Pilih Matriks Approval --</option>
                        </select>
                        <small class="text-danger">*matriks approval yang muncul berdasarkan pilihan salespoint</small>
                    </div>
                </div>
            </div>
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">Buat Pengajuan Budget</button><br>
                <small class="text-danger">* Budget sebelumnya dengan status belum aktif dan aktif di salespoint terkait
                    status akan menjadi Replaced / Diganti</small>
            </div>
        </form>
    </div>

    <div class="modal fade" id="oldbudget_modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Budget Lama</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold">Status</td>
                                        <td class="status"></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Periode</td>
                                        <td class="period"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-8">
                            <b class="selected_item">Tracking</b>
                            <table class="table table-bordered table-sm small tracking-table">
                                <thead>
                                    <tr>
                                        <th width="20%">Tiket</th>
                                        <th width="35%">PR</th>
                                        <th width="35%">PO</th>
                                        <th width="10%">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="loading" style="display: none">
                                        <td colspan="3" class="text-center">
                                            <span class="spinner-border text-danger" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12 d-flex flex-column">
                            <table class="table table-bordered list_table table-sm">
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
                                </tbody>
                            </table>
                        </div>

                        <div class="col-12 text-danger">
                            * Status budget lama akan menjadi non aktif saat melakukan pengajuan budget baru di salespoint
                            yang sama
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

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

            $('#salespoint_select').change(function() {
                let salespoint_id = $(this).val();
                loadAuthorizationbySalespoint(salespoint_id);
                checkifBudgetExist(salespoint_id);
            });

            $('.list_table tbody').on('click', 'tr', function() {
                let budget_upload_id = $(this).data('budget_upload_id');
                let armada_type = $(this).find('td:eq(0)').text().trim();
                let vendor_code = $(this).find('td:eq(1)').text().trim();
                let vendor_name = $(this).find('td:eq(2)').text().trim();
                let data = {
                    'armada_type': armada_type,
                    'vendor_code': vendor_code,
                    'vendor_name': vendor_name,
                }

                loadItemTracking(budget_upload_id, data);
            });
        });

        function loadAuthorizationbySalespoint(salespoint_id) {
            $('#authorization').find('option[value!=""]').remove();
            $('#authorization').prop('disabled', true);
            if (salespoint_id == "") {
                return;
            }
            $.ajax({
                type: "get",
                url: '/getBudgetAuthorizationbySalespoint/' + salespoint_id,
                success: function(response) {
                    let data = response.data;
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Upload Budget tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin'
                        );
                        return;
                    }
                    data.forEach(item => {
                        let namelist = item.list.map(a => a.employee_name);
                        let option_text = '<option value="' + item.id + '">' + namelist.join(" -> ") +
                            '</option>';
                        $('#authorization').append(option_text);
                    });
                    $('#authorization').val("");
                    $('#authorization').trigger('change');
                    $('#authorization').prop('disabled', false);
                },
                error: function(response) {
                    alert('load data failed. Please refresh browser or contact admin');
                    $('#authorization').find('option[value!=""]').remove();
                    $('#authorization').prop('disabled', true);
                },
                complete: function() {
                    $('#authorization').val("");
                    $('#authorization').trigger('change');
                    $('#authorization').prop('disabled', false);
                }
            });
        }

        function checkifBudgetExist(salespoint_id) {
            $('#oldbudget_button').hide();
            $('#oldbudget_modal .list_table tbody').empty();

            if (salespoint_id == "") {
                return;
            } else {
                let requestdata = {
                    salespoint_id: salespoint_id,
                    status: [-1, 0, 1],
                    type: "armada"
                };
                $.ajax({
                    type: "GET",
                    url: "/getSalespointBudget",
                    data: requestdata,
                    success: function(response) {
                        let data = response.data;
                        if (data.budget != null) {
                            let budget_upload_id = data.budget.id;
                            $('#oldbudget_button').show();
                            $('#oldbudget_modal .modal-title').text(data.budget.code);
                            $('#oldbudget_modal .status').text(':' + data.budget.status);
                            $('#oldbudget_modal .period').text(':' + data.budget.period);
                            data.lists.forEach(function(item, index) {
                                let append_row_text = '<tr data-budget_upload_id="' + budget_upload_id +
                                    '">';
                                append_row_text += '<td>' + item.armada_type_name + '</td>';
                                append_row_text += '<td>' + item.vendor_code + '</td>';
                                append_row_text += '<td>' + item.vendor_name + '</td>';
                                append_row_text += '<td>' + item.qty + '</td>';
                                append_row_text += '<td>' + setRupiah(item.value) + '</td>';
                                append_row_text += '<td>' + setRupiah(item.qty * item.value) + '</td>';
                                append_row_text += '<td>' + item.pending_quota + '</td>';
                                append_row_text += '<td>' + item.used_quota + '</td>';
                                append_row_text += '<td>' + (item.qty - item.pending_quota - item
                                    .used_quota) + '</td>';
                                append_row_text += '</tr>';
                                $('#oldbudget_modal .list_table tbody').append(append_row_text);
                            });
                        }

                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }
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

        function loadItemTracking(budget_upload_id, data) {
            let trackingtable = $('.tracking-table');
            $('.selected_item').text('Tracking ' + data.armada_type + ' ' + data.vendor_code + ' ' + data.vendor_name);
            trackingtable.find('.loading').show();
            trackingtable.find('tbody tr').not('.loading').remove();
            if (budget_upload_id == "") {
                return;
            } else {
                let requestdata = {
                    budget_upload_id: budget_upload_id,
                    data: data
                };
                $.ajax({
                    type: "GET",
                    url: "/budget/itemtracking",
                    data: requestdata,
                    success: function(response) {
                        if (!response.error) {
                            let data = response.data;
                            if (data.length > 0) {
                                data.forEach(item => {
                                    let append_text = '<tr>';
                                    append_text += '<td>' + item.ticket_code + '</td>';
                                    append_text += '<td>' + item.prs.join(', ') + '</td>';
                                    append_text += '<td>' + item.pos.join(', ') + '</td>';
                                    append_text += '<td>' + item.qty + '</td>';
                                    append_text += '</tr>';
                                    trackingtable.find('tbody').append(append_text);
                                });
                            } else {
                                trackingtable.find('tbody').append(
                                    '<tr><td colspan="4" class="text-center">Tidak ada Data</td></tr>')
                            }
                        }
                    },
                    error: function(response) {
                        console.log(response);
                    },
                    complete: function() {
                        trackingtable.find('.loading').hide();
                    }
                });
            }
        }
    </script>
@endsection
