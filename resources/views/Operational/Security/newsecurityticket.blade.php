@extends('Layout.app')
@section('local-css')
    <style>
        .bottom_action button {
            margin-right: 1em;
        }

        .box {
            background: #FFF;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
            border: 1px solid;
            border-color: gainsboro;
            border-radius: 0.5em;
        }

        .select2-results__option--disabled {
            display: none;
        }

        .remove_attachment {
            margin-left: 2em;
            font-weight: bold;
            cursor: pointer;
            color: red;
        }

        .tdbreak {
            /* word-break : break-all; */
        }

        .other_attachments tr td:first-of-type {
            overflow-wrap: anywhere;
            max-width: 300px;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Pengadaan Security Baru</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Pengadaan</li>
                        <li class="breadcrumb-item active">Pengadaan Security Baru</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
        <form action="/createsecurityticket" id="securityform" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-warning" id="oldbudget_button" data-toggle="modal"
                        data-target="#oldbudget_modal" style="display:none">
                        Tampilkan Budget Aktif
                    </button>

                    <div class="modal fade text-left" id="oldbudget_modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Budget Aktif <span class="budget_code"></span></h5>
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
                                                    <tr>
                                                        <td class="font-weight-bold">Tahun</td>
                                                        <td class="year">-</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-12 d-flex flex-column">
                                            <table class="table table-bordered list_table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Kode</th>
                                                        <th>Kategori</th>
                                                        <th>Nama</th>
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
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required_field">Tanggal Pengajuan</label>
                        <input type="date" class="form-control created_date"
                            value="{{ now()->translatedFormat('Y-m-d') }}" disabled>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required_field">Tanggal Setup</label>
                        <input type="date" class="form-control requirement_date" name="requirement_date" required>
                        <small class="text-danger">*Tanggal pengadaan minimal 14 hari dari tanggal pengajuan</small>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required_field">Pilihan Area / SalesPoint</label>
                        <select class="form-control select2 salespoint_select2" name="salespoint_id" id="salespoint_select"
                            required>
                            <option value="" data-isjawasumatra="-1">-- Pilih SalesPoint --</option>
                            @foreach ($available_salespoints as $region)
                                <optgroup label="{{ $region->first()->region_name() }}">
                                    @foreach ($region as $salespoint)
                                        <option value="{{ $salespoint->id }}"
                                            data-isjawasumatra="{{ $salespoint->isJawaSumatra }}">{{ $salespoint->name }} --
                                            {{ $salespoint->jawasumatra() }} Jawa Sumatra</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small class="text-danger">* SalesPoint yang muncul berdasarkan hak akses tiap akun</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="required_field">Tipe Pengadaan Security</label>
                        <select class="form-control" name="ticketing_type" id="ticketing_type" required disabled>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="0">Pengadaan</option>
                            <option value="1">Perpanjangan</option>
                            <option value="2">Replace</option>
                            <option value="3">End Kontrak</option>
                            <option value="4">Pengadaan Lembur</option>
                            <option value="5">Percepatan Replace</option>
                            <option value="6">Percepatan End Kontrak</option>
                        </select>
                    </div>
                </div>
                <div class="col-4" id="po_field">
                    <div class="form-group">
                        <label>Pilih PO</label>
                        <select class="form-control select2" name="po_number" id="po_select" disabled>
                            <option value="">-- Pilih PO Lama --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" id="ba_field" style="display: none">
                    <div class="form-group">
                        <label class="required_field">Upload File BA</label>
                        <input type="file" class="form-control-file form-control-sm validatefilesize" name="upload_ba"
                            id="upload_ba" required>
                    </div>
                </div>
                <div class="col-md-4" id="personil_count_field" style="display: none">
                    <div class="form-group">
                        <label class="required_field">Jumlah Personil</label>
                        <div class="input-group ">
                            <input type="number" class="form-control autonumber" name="personil_count" min="1"
                                value="1">
                            <div class="input-group-append">
                                <span class="input-group-text">Personil</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none" id="reason_field">
                <div class="col-12">
                    <div class="form-group">
                        <label class="required_field">Alasan Pengadaan Lembur</label>
                        <textarea class="form-control" name="reason" id="reason_textarea" rows="3" style="resize: none"
                            placeholder="Masukkan Alasan"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>Pilih Matriks Approval</label>
                        <select class="form-control" id="authorization" name="authorization_id" disabled>
                            <option value="">-- Pilih Matriks Approval --</option>
                        </select>
                        <small class="text-danger">*approval ticket hanya untuk pengadaan baru dan muncul berdasarkan
                            pilihan salespoint</small>
                    </div>
                </div>
                <div class="col-12 d-flex flex-row justify-content-center align-items-center" id="authorization_field">
                </div>
            </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        <button type="submit" class="btn btn-primary">Buat Ticket Security</button>
    </div>
    </form>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
            $('.requirement_date').val(moment().add(14, 'days').format('YYYY-MM-DD'));
            $('.requirement_date').prop('min', moment().add(14, 'days').format('YYYY-MM-DD'));
            $('.requirement_date').trigger('change');

            $('.salespoint_select2').change(function() {
                let salespoint_id = $(this).val();
                $('#ticketing_type').prop('disabled', true);
                $('#ticketing_type').val("");
                loadAuthorizationbySalespoint(salespoint_id);
                if (salespoint_id != "") {
                    $('#ticketing_type').prop('disabled', false);
                    checkifBudgetExist(salespoint_id);
                }
                $('#ticketing_type').trigger('change');
            });

            $('#ticketing_type').change(function() {
                $('#po_select').val("");
                $('#po_select').trigger('change');
                $('#po_field label').removeClass('required_field');
                $('#po_select').find('option[value!=""]').remove();
                $('#po_select').prop('disabled', true);
                $('#po_select').prop('required', false);

                $('#personil_count_field').hide();
                $('#personil_count_field input').prop('required', false);
                $('#authorization').prop('disabled', true).prop('required', false);

                $('#reason_field').addClass('d-none');
                $('#reason_textarea').prop('required', false);
                switch ($(this).val()) {
                    case '0':
                        // Pengadaan
                        $('#personil_count_field').show();
                        $('#personil_count_field input').prop('required', true);
                        $('#authorization').prop('disabled', false).prop('required', true);
                        $('#ba_field').hide();
                        $('#upload_ba').prop('required', false);
                        break;

                    case '1':
                        // Perpanjangan
                        $('#po_select').prop('required', true);
                        $('#po_field label').addClass('required_field');
                        $('#ba_field').hide();
                        $('#upload_ba').prop('required', false);
                        refreshPO();
                        break;

                    case '2':
                        // Replace
                        $('#po_select').prop('required', true);
                        $('#po_field label').addClass('required_field');
                        $('#ba_field').hide();
                        $('#upload_ba').prop('required', false);
                        refreshPO();
                        break;

                    case '3':
                        // End Kontrak
                        $('#po_select').prop('required', true);
                        $('#po_field label').addClass('required_field');
                        $('#ba_field').hide();
                        $('#upload_ba').prop('required', false);
                        refreshPO();
                        break;

                    case '4':
                        // Pengadaan Lembur
                        $('#authorization').prop('disabled', false).prop('required', true);
                        $('#reason_field').removeClass('d-none');
                        $('#reason_textarea').prop('required', true);
                        $('#reason_textarea').val("");
                        $('#ba_field').hide();
                        $('#upload_ba').prop('required', false);
                        break;

                    case '5':
                        // Percepatan Replace
                        $('#po_select').prop('required', true);
                        $('#po_field label').addClass('required_field');
                        $('#ba_field').show();
                        $('#upload_ba').prop('required', true);
                        refreshPO();
                        break;

                    case '6':
                        // Percepatan End Kontrak
                        $('#po_select').prop('required', true);
                        $('#po_field label').addClass('required_field');
                        $('#ba_field').show();
                        $('#upload_ba').prop('required', true);
                        refreshPO();
                        break;

                    default:
                        break;
                }
            });

            $('#authorization').change(function() {
                let list = $(this).find('option:selected').data('list');
                $('#authorization_field').empty();
                if (list !== undefined) {
                    list.forEach(function(item, index) {
                        $('#authorization_field').append(
                            '<div class="d-flex text-center flex-column mr-3"><div class="font-weight-bold">' +
                            item.sign_as + '</div><div>' + item.employee.name +
                            '</div><div class="text-secondary">(' + item.employee_position
                            .name + ')</div></div>');
                        if (index != list.length - 1) {
                            $('#authorization_field').append(
                                '<div class="mr-3"><i class="fa fa-chevron-right" aria-hidden="true"></i></div>'
                            );
                        }
                    });
                }
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
                url: '/getSecurityAuthorizationbySalespoint/' + salespoint_id,
                success: function(response) {
                    let data = response.data;
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Pengadaan Security tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin'
                        );
                        return;
                    }
                    data.forEach(item => {
                        let namelist = item.list.map(a => a.employee_name);
                        let option_text = '<option value="' + item.id + '">' + namelist.join(" -> ");
                        if (item.notes != "") {
                            option_text += " (" + item.notes + ")";
                        }
                        option_text += '</option>';
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

        function refreshPO() {
            let salespoint_id = $('#salespoint_select').val();
            $('#po_select').find('option[value!=""]').remove();
            let requestdata = {
                salespoint_id: salespoint_id,
                type: 'security',
            };
            $.ajax({
                type: "get",
                url: '/getActivePO',
                data: requestdata,
                success: function(response) {
                    let data = response.data;
                    data.forEach(item => {
                        let option_text = '<option value="' + item.po_number + '">' + item.po_number +
                            ' (' + item.code + '[' + item.end_date + '])</option>';
                        $('#po_select').append(option_text);
                    });
                    $('#po_select').val("");
                    $('#po_select').trigger('change');
                    $('#po_select').prop('disabled', false);
                },
                error: function(response) {
                    $('#po_select').prop('disabled', true);
                    alert('load data failed. Please refresh browser or contact admin');
                },
                complete: function() {
                    $('#po_select').prop('disabled', false);
                    $('#po_select').trigger('change');
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
                    status: [1],
                    type: "assumption",
                    year: {{ now()->format('Y') }},
                };
                $.ajax({
                    type: "GET",
                    url: "/getSalespointBudget",
                    data: requestdata,
                    success: function(response) {
                        let data = response.data;
                        if (data.budget != null) {
                            $('#oldbudget_button').show();
                            $('#oldbudget_modal .modal-title').text('Bugdet (' + data.budget.code + ')');
                            $('#oldbudget_modal .status').text(':' + data.budget.status);
                            $('#oldbudget_modal .period').text(':' + data.budget.period);
                            $('#oldbudget_modal .year').text(data.budget.year);

                            data.lists.forEach(function(item, index) {
                                let highlight_text = "";
                                if (item.code == 'SCRT') {
                                    highlight_text = "table-success";
                                }
                                let append_row_text = '<tr class="' + highlight_text + '">';
                                append_row_text += '<td>' + item.code + '</td>';
                                append_row_text += '<td>' + item.group + '</td>';
                                append_row_text += '<td>' + item.name + '</td>';
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
                        alert(response.message);
                    }
                });
            }
        }
    </script>
@endsection
