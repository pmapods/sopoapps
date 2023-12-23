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
                    <h1 class="m-0 text-dark">Pengadaan Jasa Lainnya @isset($ticket)
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
                        <li class="breadcrumb-item active">Pengadaan Jasa Lainnya @isset($ticket)
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
        <form action="/additionalticketing/create" id="additionalform" enctype="multipart/form-data" method="post">
            @csrf
            <div id="additionaldata"></div>
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
                        <label class="required_field">Jenis Budget</label>
                        <select class="form-control budget_type" name="budget_type" disabled>
                            <option value="">-- Pilih Jenis Budget --</option>
                            <option value="0">Budget</option>
                            <option value="1">Non Budget</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Pilih Tipe Jasa</label>
                        <select class="form-control ticket_type" name="ticket_type" disabled>
                            <option value="">-- Pilih Tipe Jasa --</option>
                            <option value="CIT">CIT</option>
                            <option value="PEST CONTROL">Pest Control</option>
                            <option value="MERCHANDISER">Merchandiser</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Pilih Jenis Pengadaan</label>
                        <select class="form-control request_type" name="request_type" disabled>
                            <option value="">-- Pilih Jenis Pengadaan --</option>
                            <option value="0">Pengadaan Baru</option>
                            <option value="3">Perpanjangan</option>
                            <option value="1">Replace</option>
                            <option value="4">End Kontrak</option>
                            <option value="5">Percepatan Replace</option>
                            <option value="6">Percepatan End Kontrak</option>

                        </select>
                    </div>
                </div>
                <div class="col-md-4 po_field" style="display: none">
                    <div class="form-group">
                        <label class="required_field">Pilih PO</label>
                        <select class="form-control po_select" name="po_number">
                        </select>
                        <small class="text-danger">*po yang tampil berdasarkan Tipe jasa dan Salespoint terpilih</small>
                    </div>
                </div>
                <div class="col-md-4 ba_field" style="display: none">
                    <div class="form-group">
                        <label class="required_field">Upload File BA</label>
                        <input type="file" class="form-control-file validatefilesize" name="upload_ba">
                    </div>
                </div>
                <div class="col-md-4 cit_field" style="display:none">
                    <div class="form-group">
                        <label class="required_field">Jumlah Bulan (Qty)</label>
                        <div class="input-group ">
                            <input type="number" class="form-control autonumber" name="months" min="1"
                                value="1">
                            <div class="input-group-append">
                                <span class="input-group-text">Bulan</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 cit_field" style="display:none">
                    <div class="form-group">
                        <label class="required_field">Expense Amount (Value) / bulan</label>
                        <input type="text" class="form-control rupiah" name="expense">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Pilih Matriks Approval</label>
                        <select class="form-control" id="authorization" name="authorization_id" disabled>
                            <option value="">-- Pilih Matriks Approval --</option>
                        </select>
                        <small class="text-danger">*Matriks Approval hanya untuk pengadaan baru dan muncul berdasarkan
                            pilihan salespoint</small>
                    </div>
                </div>
                <div class="col-md-12 d-flex flex-row justify-content-center align-items-center" id="authorization_field">
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button type="submit" class="btn btn-primary">Buat Ticket</button>
            </div>
        </form>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('.autonumber').change(function() {
                autonumber($(this));
            });

            // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
            $('.requirement_date').val(moment().add(14, 'days').format('YYYY-MM-DD'));
            $('.requirement_date').prop('min', moment().add(14, 'days').format('YYYY-MM-DD'));
            $('.requirement_date').trigger('change');

            $('.salespoint_select2').change(function() {

                if ($(this).val() != "") {
                    $('.budget_type').prop('disabled', false);
                } else {
                    $('.budget_type').prop('disabled', true);
                }
                $('.budget_type').val('');
                $('.budget_type').trigger('change');

                let salespoint_id = $(this).val();
                if (salespoint_id != "") {
                    checkifBudgetExist(salespoint_id);
                }
            });
            $('.budget_type').change(function() {
                if ($(this).val() != "") {
                    $('.ticket_type').prop('disabled', false);
                } else {
                    $('.ticket_type').prop('disabled', true);
                }
                $('.ticket_type').val('');
                $('.ticket_type').trigger('change');
            });

            $('.ticket_type').change(function() {
                if ($(this).val() != "") {
                    $('.request_type').prop('disabled', false);
                } else {
                    $('.request_type').prop('disabled', true);
                }
                $('.request_type').val('');
                $('.request_type').trigger('change');
            });

            $('.request_type').change(function() {
                let salespoint_id = $('.salespoint_select2').val();
                let ticket_type = $('.ticket_type').val();
                let request_type = $(this).val();

                $('.po_select').val("");
                $('.po_select').trigger('change');
                // field otorisasi untuk semua pengadaan , pengadaan baru menggunakan otorisasi barang jasa, perpanjangan , replace, end kontrak menggunakan pengadaan lembur
                if (request_type == "0") {
                    $('.po_field').hide();
                    $('.ba_field').hide();
                    $('.po_select').prop('required', false);
                    $('#authorization').val("");
                    $('#authorization').prop('disabled', false);
                    $('#authorization').prop('required', true);
                    $('.cit_field').show();
                    $('.cit_field input').prop('required', true);
                    loadAuthorization(salespoint_id, 0, "");
                } else if (request_type == 5 || request_type == 6) {
                    $('.po_field').show();
                    $('.ba_field').show();
                    $('.po_select').prop('required', true);
                    $('.upload_ba').prop('required', true);
                    $('#authorization').val("");
                    $('#authorization').prop('disabled', false);
                    $('#authorization').prop('required', true);
                    $('#authorization').trigger('change');
                    loadAuthorization(salespoint_id, 8, "Pengadaan Lembur");
                    loadPO(ticket_type, request_type, salespoint_id);
                } else if (request_type != "") {
                    // selain pengadaan baru minta untuk memilih po terkait
                    $('.po_field').show();
                    $('.ba_field').hide();
                    $('.po_select').prop('required', true);
                    $('.upload_ba').prop('required', false);
                    $('#authorization').val("");
                    $('#authorization').prop('disabled', false);
                    $('#authorization').prop('required', true);
                    $('#authorization').trigger('change');
                    loadAuthorization(salespoint_id, 8, "Pengadaan Lembur");
                    loadPO(ticket_type, request_type, salespoint_id);
                } else {
                    $('.po_field').hide();
                    $('.ba_field').hide();
                    $('.po_select').prop('required', false);
                    $('.upload_ba').prop('required', false);
                    $('#authorization').prop('disabled', true);
                    $('#authorization').prop('required', false);
                    $('#authorization').val("");
                    $('#authorization').trigger('change');
                }
            });

            $('.po_select').change(function() {
                if ($(this).val() != "" && $('.ticket_type').val() == "cit" && ["3", "1"].includes($(
                        '.request_type').val())) {
                    $('.cit_field').show();
                    $('.cit_field input').prop('required', true);
                } else {
                    $('.cit_field').hide();
                    $('.cit_field input').prop('required', false);
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

        function loadAuthorization(salespoint_id, form_type, notes) {
            $('#authorization').find('option[value!=""]').remove();
            $('#authorization').prop('disabled', true);
            if (salespoint_id == "") {
                return;
            }
            $.ajax({
                type: "get",
                url: '/getAuthorization?salespoint_id=' + salespoint_id + '&form_type=' + form_type + '&notes=' +
                    notes,
                success: function(response) {
                    let data = response.data;
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Barang Jasa tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin'
                        );
                        return;
                    }
                    data.forEach(item => {
                        let namelist = item.list.map(a => a.employee_name);
                        let option_text = '<option value="' + item.id + '">' + namelist.join(" -> ") +
                            '</option>';
                        $('#authorization').append(option_text);
                    });
                    $('#authorization').prop('disabled', false);
                },
                error: function(response) {
                    alert('load data failed. Please refresh browser or contact admin');
                    $('#authorization').prop('disabled', true);
                },
                complete: function() {
                    $('#authorization').val("");
                    $('#authorization').trigger('change');
                }
            });
        }

        function loadPO(ticket_type, request_type, salespoint_id) {
            let requestdata = {
                ticket_type: ticket_type,
                request_type: request_type,
                salespoint_id: salespoint_id,
                type: 'additional'
            };
            $('.po_select').empty();
            $('.po_select').append('<option value="">-- Pilih PO --</option>');
            $.ajax({
                type: "get",
                url: "/getActivePO",
                data: requestdata,
                success: function(response) {
                    let data = response.data;
                    console.log(requestdata);

                    data.forEach(item => {
                        let option_text = '<option data-vendor="' + item.vendor_name + '" value="' +
                            item.po_number + '">' + item.po_number + ' (' + item.salespoint_name +
                            ') - ' + item.vendor_name + '</option>';
                        $('.po_select').append(option_text);
                    });
                    $('.po_select').val("");
                    $('.po_select').prop('disabled', false);
                },

                error: function(response) {
                    $('.po_select').prop('disabled', true);
                    alert('error: ', response);
                    console.log(response);
                },
                complete: function() {
                    $('.po_select').trigger('change');
                }
            });
        }

        // add vendor
        function addVendor(el) {
            let select_vendor = $('.select_vendor');
            let table_vendor = $('.table_vendor');
            let id = select_vendor.find('option:selected').data('id');
            let name = select_vendor.find('option:selected').data('name');
            let code = select_vendor.find('option:selected').data('code');
            let salesperson = select_vendor.find('option:selected').data('salesperson');
            if (select_vendor.val() == "") {
                alert('Harap pilih vendor terlebih dulu');
                return;
            }
            if ($('.vendor_item_list').length > 2) {
                alert('Maksimal 3 vendor');
                return;
            }
            table_vendor.find('tbody').append('<tr class="vendor_item_list" data-vendor_id="' + id + '"><td>' + code +
                '</td><td>' + name + '</td><td>' + salesperson +
                '</td><td>-</td><td>Terdaftar</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>'
            );
            select_vendor.val('');
            select_vendor.trigger('change');
            tableVendorRefreshed(select_vendor);
        }

        function addOTVendor(el) {
            let vendor_name = $('.ot_vendor_name');
            let vendor_sales = $('.ot_vendor_sales');
            let vendor_phone = $('.ot_vendor_phone');
            let table_vendor = $('.table_vendor');
            if (vendor_name.val() == "") {
                alert('Nama Vendor tidak boleh kosong');
                return;
            }
            if (vendor_sales.val() == "") {
                alert('Sales Vendor tidak boleh kosong');
                return;
            }
            if (vendor_phone.val() == "") {
                alert('Telfon Vendor tidak boleh kosong');
                return;
            }
            if ($('.vendor_item_list').length > 1) {
                alert('Maksimal 2 vendor');
                return;
            }
            table_vendor.find('tbody').append('<tr class="vendor_item_list" data-name="' + vendor_name.val() +
                '" data-sales="' + vendor_sales.val() + '" data-phone="' + vendor_phone.val() + '"><td>-</td><td>' +
                vendor_name.val() + '</td><td>' + vendor_sales.val() + '</td><td>' + vendor_phone.val() +
                '</td><td>One Time</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>'
            );
            vendor_name.val('');
            vendor_sales.val('');
            vendor_phone.val('');
            tableVendorRefreshed(vendor_name);
        }

        // remove vendor
        function removeVendor(el) {
            let tr = $(el).closest('tr');
            tr.remove();
            tableVendorRefreshed();
        }

        // table on refresh
        function tableVendorRefreshed(current_element) {
            let table_vendor = $('.table_vendor');

            let row_count = 0;
            table_vendor.find('tbody tr').not('.empty_row').each(function() {
                row_count++;
            });
            if (row_count > 0) {
                table_vendor.find('.empty_row').remove();
            } else {
                table_vendor.find('tbody').append(
                    '<tr class="empty_row text-center"><td colspan="6">Vendor belum dipilih</td></tr>');
            }
            if ($('.vendor_item_list').length < 2) {
                $('.vendor_ba_field').show();
            } else {
                $('.vendor_ba_field').hide();
            }
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
                            $('#oldbudget_modal .year').text(':' + data.budget.year);

                            data.lists.forEach(function(item, index) {
                                let highlight_text = "";
                                if (item.code == 'CIT') {
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
