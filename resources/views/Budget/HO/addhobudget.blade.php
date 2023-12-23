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
                    <h1 class="m-0 text-dark">Tambah HO Budget</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Budget</li>
                        <li class="breadcrumb-item">HO Budget</li>
                        <li class="breadcrumb-item active">Tambah HO Budget</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <form action="/createBudgetRequest/ho" method="post" id="submitform" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-6 row">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="required_field">Pilihan Area / SalesPoint</label>
                            <select class="form-control select2" name="salespoint_id" id="salespoint_select" required>
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
                    <div class="col-4">
                        <div class="form-group">
                            <label class="required_field">Divisi</label>
                            <select class="form-control" name="divisi" required>
                                @foreach (config('customvariable.division') as $division)
                                    <option value="{{ $division }}">{{ $division }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="required_field">Tahun</label>
                            <select class="form-control" name="year" required>
                                @for ($i = 0; $i < 5; $i++)
                                    <option @if ($i == 0) selected @endif
                                        value="{{ now()->format('Y') + $i }}">{{ now()->format('Y') + $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <label class="required_field">Pilih File Template</label>
                        <input type="file" class="form-control-file" name="file" onclick="this.value=null;"
                            placeholder="Pilih File Template HO" id="file_template" required="required"
                            accept=".xls, .xlsx" />
                    </div>
                </div>
                <div class="col-4 mr-2 pt-4 d-flex justify-content-start">
                    <div><button type="button" class="btn btn-info" onclick="downloadtemplate()">Download Template</button>
                    </div>

                    <div><button type="button" class="btn btn-warning ml-2" id="oldbudget_button" data-toggle="modal"
                            data-target="#oldbudget_modal" style="display: none">Tampilkan Budget Lama</button></div>
                </div>
                <div class="col-3 d-flex align-items-center justify-content-end">
                </div>
            </div>
            <div class="row">
                {{-- <div class="col-12 h5">Monthly</div> --}}
                <div class="col-12 d-flex flex-column align-items-end">
                    <div>Pilihan Salespoint File Upload : <span class="upload_selected_salespoint"></span></div>
                    <div>Pilihan Divisi File Upload : <span class="upload_selected_division"></span></div>
                    <div>Pilihan Tahun File Upload : <span class="upload_selected_year"></span></div>
                </div>
                <div class="col-12 table-responsive">
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

                    <span class="spinner-border text-danger" id="monthly_table_loading" role="status"
                        style="display: none">
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
                        <tr>
                            <td class='text-center' colspan="11">Tidak Ada Data</td>
                        </tr>
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
                        <tr>
                            <td class='text-center' colspan="5">Tidak Ada Data</td>
                        </tr>
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
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="required_field">Pilih Matriks Approval</label>
                        <select class="form-control select2" id="authorization" name="authorization_id" disabled required>
                            <option value="">-- Pilih Matriks Approval --</option>
                        </select>
                        <small class="text-danger">*Matriks Approval yang muncul berdasarkan pilihan salespoint</small>
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

    <!-- Modal -->
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
                                        <td class="font-weight-bold">Divisi</td>
                                        <td class="division"></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tahun</td>
                                        <td class="year"></td>
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
                        <div class="col-12 d-flex flex-column table-responsive">
                            <table class="table table-bordered list_table table-sm small">
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

    <!-- Modal -->
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
                                    $('.upload_selected_salespoint').text(response.info
                                        .salespoint_name + " || " + response.info
                                        .salespoint_code);
                                    $('.upload_selected_division').text(response.info.division);
                                    $('.upload_selected_year').text(response.info.year);
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
                                            // console.log(result.length);
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
                                    $('.upload_selected_salespoint').text("");
                                    $('.upload_selected_division').text("");
                                    $('.upload_selected_year').text("");
                                }

                            } else {
                                alert('file not uploaded');
                            }
                        },
                        error: function(response) {
                            alert("Terjadi kesalahan: " + response.message);
                            $('.upload_selected_salespoint').text("");
                            $('.upload_selected_division').text("");
                            $('.upload_selected_year').text("");
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
            $('#salespoint_select').change(function() {
                let salespoint_id = $(this).val();
                loadAuthorizationbySalespoint(salespoint_id);
                checkifBudgetExist(salespoint_id);
            });
            $('.list_table tbody').on('click', 'tr', function() {
                let budget_upload_id = $(this).data('budget_upload_id');
                let kode = $(this).find('td:eq(0)').text().trim();
                let kategori = $(this).find('td:eq(1)').text().trim();
                let nama = $(this).find('td:eq(2)').text().trim();
                let data = {
                    'code': kode,
                    'category': kategori,
                    'name': nama,
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
                    // console.log(data);
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Upload Budget tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin'
                            );
                        return;
                    }
                    data.forEach(item => {
                        let namelist = item.list.map(a => a.employee_name);
                        let option_text = '<option value="' + item.id + '">' + namelist.join(" -> ") +
                            ' (' + item.notes + ')</option>';
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
                    type: "ho",
                    division: $('select[name="divisi"]').val(),
                    year: $('select[name="year"]').val(),
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
                            $('#oldbudget_modal .division').text(':' + data.budget.division);
                            $('#oldbudget_modal .year').text(':' + data.budget.year);

                            data.lists.forEach(function(item, index) {
                                let append_text = "<tr>";
                                append_text += '<td class="text-nowrap">' + item.code + '</td>';
                                append_text += '<td class="text-nowrap">' + item.category + '</td>';
                                append_text += '<td class="text-nowrap">' + item.name + '</td>';
                                for (let i = 1; i <= 12; i++) {
                                    let result = JSON.parse(item.values).filter(obj => {
                                        return obj.months === i;
                                    });
                                    if (result.length > 0) {
                                        append_text += '<td>' + result[0].qty + '</td>';
                                        append_text += '<td class="text-nowrap">' + setRupiah(result[0]
                                            .value) + '</td>';
                                    } else {
                                        append_text += '<td>-</td>';
                                        append_text += '<td>-</td>';
                                    }
                                }
                                append_text += "</tr>";
                                $('#oldbudget_modal .list_table tbody').append(append_text);
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

        function loadItemTracking(budget_upload_id, data) {
            let trackingtable = $('.tracking-table');
            $('.selected_item').text('Tracking ' + data.code + ' ' + data.category + ' ' + data.name);
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

        function downloadtemplate() {
            // let salespoint_id  = $('select[name="salespoint_id"]').val();
            // let divisi         = $('select[name="divisi"]').val();
            // let year           = $('select[name="year"]').val();
            // if(salespoint_id == ""){
            //     alert('Salespoint harus dipilih');
            //     return;
            // }
            // if(divisi == ""){
            //     alert('Divisi harus dipilih');
            //     return;
            // }
            // if(year == ""){
            //     alert('Tahun harus dipilih');
            //     return;
            // }
            // window.location.href = "/ho_budget/create/template?salespoint_id="+salespoint_id+"&divisi="+divisi+"&year="+year;
            window.location.href = "/ho_budget/create/template";
        }
    </script>
@endsection
