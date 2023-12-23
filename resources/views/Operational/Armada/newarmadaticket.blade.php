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
                    <h1 class="m-0 text-dark">Pengadaan Armada @isset($ticket)
                            ({{ $ticket->code }})
                        @else
                            Baru
                        @endisset
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Pengadaan</li>
                        <li class="breadcrumb-item active">Pengadaan Armada @isset($ticket)
                                ({{ $ticket->code }})
                            @else
                                Baru
                            @endisset
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
        <form action="/createarmadaticket" id="armadaform" method="post">
            @csrf
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-warning" id="oldbudget_button" data-toggle="modal"
                        data-target="#oldbudget_modal" style="display: none">
                        Tampilkan Budget Aktif
                    </button>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Tanggal Pengajuan</label>
                        <input type="date" class="form-control created_date"
                            value="{{ now()->translatedFormat('Y-m-d') }}" disabled>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Tanggal Setup</label>
                        <input type="date" class="form-control requirement_date" name="requirement_date" required>
                        <small class="text-danger">*Tanggal pengadaan minimal 14 hari dari tanggal pengajuan</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Pilihan Area / SalesPoint</label>
                        <select class="form-control select2 salespoint_select2" name="salespoint_id" required>
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Jenis Armada</label>
                        <select class="form-control isNiaga" name="isNiaga" required disabled>
                            <option value="">Pilih Jenis Armada</option>
                            <option value="0">Non Niaga</option>
                            <option value="1">Niaga</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Jenis Pengadaan</label>
                        <select class="form-control pengadaan_type" name="pengadaan_type" required disabled>
                            <option value="">Pilih Jenis Pengadaan</option>
                        </select>
                    </div>
                </div>
                {{-- untuk pengadaan baru --}}
                <div class="col-md-4 armada_type_field" style="display:none">
                    <div class="form-group">
                        <label class="required_field">Jenis Kendaraan</label>
                        <select class="form-control armada_type" name="armada_type_id">
                            <option>-- Pilih Jenis Kendaraan --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 is_budget_field" style="display:none">
                    <div class="form-group">
                        <label class="required_field">Jenis Budget</label>
                        <select class="form-control is_budget" name="isBudget">
                            <option value="">-- Pilih Jenis Budget --</option>
                            <option value="1">Budget</option>
                            <option value="0">Non Budget</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 author_select_field" style="display:none">
                    <div class="form-group">
                        <label class="required_field">Pilih Jenis Otorisasi</label>
                        <select class="form-control author_select" name="authorSelect">
                            <option value="">-- Pilih Jenis Otorisasi --</option>
                            <option value="facility_form">Form Fasilitas</option>
                            <option value="pr_manual">PR Manual</option>
                        </select>
                    </div>
                </div>
                {{-- untuk replace/mutasi/stop --}}
                <div class="col-md-4 po_field" style="display:none">
                    <div class="form-group">
                        <label class="required_field">Pilih PO</label>
                        <select class="form-control po select2" name="po_id">
                            <option value="">-- Pilih PO --</option>
                        </select>
                        <small class="text-danger">* PO aktif yang muncul berdasarkan tipe niaga dan salespoint
                            terpilih</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vendor">Pilih Rekomendasi Vendor</label>
                        <select class="form-control" name="vendor_recommendation_name" id="vendor" required>
                            @foreach ($armada_vendors as $vendor)
                                <option value="{{ $vendor->alias }}">{{ $vendor->name }} ({{ $vendor->alias }})</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" id="hidden_vendor" name="vendor_recommendation_name" disabled>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Pilih Matriks Approval</label>
                        <select class="form-control" id="authorization" name="authorization_id" disabled>
                            <option value="">-- Pilih Matriks Approval --</option>
                        </select>
                        {{-- <small class="text-danger">*approval hanya untuk pengadaan baru dan muncul berdasarkan pilihan salespoint</small> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-12 d-flex flex-row justify-content-center align-items-center" id="authorization_field">
            </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        <button type="submit" class="btn btn-primary">Buat Ticket Armada</button>
    </div>
    </form>
    </div>


    <div class="modal fade" id="oldbudget_modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Budget Aktif</h5>
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
            // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
            $('.requirement_date').val(moment().add(14, 'days').format('YYYY-MM-DD'));
            $('.requirement_date').prop('min', moment().add(14, 'days').format('YYYY-MM-DD'));
            $('.requirement_date').trigger('change');

            $('.salespoint_select2').change(function() {
                let salespoint_id = $(this).val();
                $('.isNiaga').prop('disabled', true);
                $('.isNiaga').val("");
                $('.isNiaga').trigger('change');
                if (salespoint_id != "") {
                    $('.isNiaga').prop('disabled', false);
                    loadAuthorizationbySalespoint(salespoint_id);
                    checkifBudgetExist(salespoint_id);
                }
                $('.isNiaga').trigger('change');
            });


            $('.isNiaga').change(function(event) {
                $('.pengadaan_type').empty();
                $('.pengadaan_type').prop('disabled', true);
                $('.pengadaan_type').append('<option value="">Pilih Jenis Pengadaan</option>');
                if ($(this).val() != "") {
                    $('.pengadaan_type').append('<option value="0">Pengadaan Baru</option>');
                    $('.pengadaan_type').append(
                        '<option value="1">Perpanjangan/Replace/Renewal/Stop Sewa</option>');
                    $('.pengadaan_type').append('<option value="2">Mutasi</option>');
                    if ($(this).val() == 0) {
                        $('.pengadaan_type').append('<option value="3">COP</option>');
                    }
                    $('.pengadaan_type').append(
                        '<option value="4">Percepatan Replace/Renewal/Stop Sewa</option>');
                    $('.pengadaan_type').prop('disabled', false);
                }
                $('.pengadaan_type').trigger('change');
            });
            $('.isNiaga').trigger('change');

            $('.pengadaan_type').change(function() {
                let isNiaga = $('#armadaform').find('.isNiaga').val();
                let salespoint_id = $('#armadaform').find('.salespoint_select2').val();
                let armada_type_select = $('#armadaform').find('.armada_type');
                armada_type_select.prop('disabled', true);
                armada_type_select.empty();
                let option_text = '<option value="">-- Pilih Jenis Kendaraan --</option>';
                armada_type_select.append(option_text);
                $('.po_field').hide();
                $('.po').val("").prop('disabled', true).prop('required', false);
                $('.po').trigger('change');
                $('.armada_type_field').hide();
                $('.armada_type').val("").prop('disabled', true).prop('required', false);

                $('#authorization').prop('disabled', true).prop('required', false);
                if ($(this).val() == '') {
                    return;
                }
                if ($(this).val() == '0') {
                    // REVISI 09-06-2022
                    if (isNiaga == true) {
                        $('#authorization').prop('disabled', false).prop('required', true);
                    }

                    $('.armada_type_field').show();
                    $('.armada_type').prop('disabled', false).prop('required', true);
                    $.ajax({
                        type: "get",
                        url: '/getarmadatypebyniaga/' + isNiaga,
                        success: function(response) {
                            let data = response.data;

                            data.forEach(item => {
                                let option_text = '<option value="' + item.id + '">' +
                                    item.name + ' -- ' + item.brand_name + '</option>';
                                armada_type_select.append(option_text);
                            });
                            armada_type_select.val("");
                            armada_type_select.trigger('change');
                            armada_type_select.prop('disabled', false);
                        },
                        error: function(response) {
                            alert('load data failed. Please refresh browser or contact admin');
                        },
                        complete: function() {
                            armada_type_select.trigger('change');
                        }
                    });
                }

                if ($(this).val() == '1' || $(this).val() == '2' || $(this).val() == '4') {
                    $('.po_field').show();
                    $('.po').prop('disabled', true).prop('required', true);
                    $('.po').find('option[value!=""]').remove();
                    let requestdata = {
                        salespoint_id: salespoint_id,
                        isNiaga: isNiaga,
                        pengadaan_type: $('.pengadaan_type').val(),
                        type: 'armada'
                    };
                    $.ajax({
                        type: "get",
                        url: '/getActivePO',
                        data: requestdata,
                        success: function(response) {
                            let data = response.data;
                            data.forEach(item => {
                                let option_text = '<option data-vendor="' + item
                                    .vendor + '" value="' + item.po_number + '">' + item
                                    .po_number + ' (' + item.plate + ' - ' + item
                                    .vendor + ') [' + item.end_date + ']</option>';
                                $('.po').append(option_text);
                            });
                            $('.po').val("");
                            $('.po').trigger('change');
                            $('.po').prop('disabled', false);
                        },
                        error: function(response) {
                            alert('load data failed. Please refresh browser or contact admin');
                            console.log(response);
                        },
                        complete: function() {
                            $('.po').trigger('change');
                        }
                    });
                }
            });

            $('.armada_type').change(function() {
                let armada_type_id = $(this).val();
                let isNiaga = $('.isNiaga').val();

                $('.is_budget_field, .author_select_field').hide();
                $('.is_budget, .author_select').prop('disabled', true).prop('required', false);
                $('.is_budget, .author_select').val('');
                if (armada_type_id != "") {
                    if (isNiaga == true || armada_type_id == "8") {
                        $('.is_budget_field').show();
                        $('.is_budget').prop('disabled', false).prop('required', true);
                    } else {
                        $('.is_budget_field').show();
                        $('.is_budget').val(1);
                    }
                    $('.is_budget').trigger('change');

                    if (armada_type_id == "8") {
                        $('.author_select_field').show();
                        $('.author_select').prop('disabled', false).prop('required', true);
                    }
                }
            });
            $('.author_select').change(function() {
                $('#authorization').val("");
                $('#authorization').trigger('change');
                if ($(this).val() == "pr_manual") {
                    $('#authorization').prop('disabled', false).prop('required', true);
                } else {
                    $('#authorization').prop('disabled', true).prop('required', false);
                }
            })

            $('.po').on('change', function() {
                // set rekomendasi vendor jika tersedia
                let vendor = $(this).find('option:selected').data('vendor');
                if (vendor == null || vendor == '') {
                    $('#vendor').prop('disabled', false);
                    $('#hidden_vendor').val('');
                    $('#hidden_vendor').prop('disabled', true);
                } else {
                    $('#vendor').val(vendor);
                    $('#vendor').trigger('change');
                    $('#vendor').prop('disabled', true);
                    $('#hidden_vendor').val(vendor);
                    $('#hidden_vendor').prop('disabled', false);
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

        function checkifBudgetExist(salespoint_id) {
            $('#oldbudget_button').hide();
            $('#oldbudget_modal .list_table tbody').empty();

            if (salespoint_id == "") {
                return;
            } else {
                let requestdata = {
                    salespoint_id: salespoint_id,
                    status: [1],
                    type: "armada",
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
                            $('#oldbudget_modal .modal-title').text(data.budget.code);
                            $('#oldbudget_modal .status').text(':' + data.budget.status);
                            $('#oldbudget_modal .period').text(':' + data.budget.period);
                            $('#oldbudget_modal .year').text(':' + data.budget.year);

                            data.lists.forEach(function(item, index) {
                                let append_row_text = '<tr>';
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
                        alert(response.message);
                    }
                });
            }
        }

        function loadAuthorizationbySalespoint(salespoint_id) {
            $('#authorization').find('option[value!=""]').remove();
            $('#authorization').prop('disabled', true);
            if (salespoint_id == "") {
                return;
            }
            $.ajax({
                type: "get",
                url: '/getArmadaAuthorizationbySalespoint/' + salespoint_id,
                success: function(response) {
                    let data = response.data;
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Armada tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin'
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
    </script>
@endsection
