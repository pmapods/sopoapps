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
                    <h1 class="m-0 text-dark">PO Sewa @isset($ticket)
                            ({{ $ticket->code }})
                        @else
                            Baru
                        @endisset
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Sales</li>
                        <li class="breadcrumb-item">PO/Quotation</li>
                        <li class="breadcrumb-item active">PO Sewa @isset($ticket)
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
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Tanggal Pengajuan</label>
                    <input type="date" class="form-control created_date" value="{{ now()->translatedFormat('Y-m-d') }}"
                        disabled>
                    <small class="text-danger">* tanggal pengajuan yang tercatat adalah tanggal sistem saat approval
                        dimulai</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Tanggal Pengadaan</label>
                    <input type="date" class="form-control requirement_date">
                    <small class="text-danger">*Tanggal pengadaan minimal 14 hari dari tanggal pengajuan</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Pembuat Form</label>
                    <input type="text" class="form-control form_creator" value="{{ Auth::user()->name }}" disabled>
                    <small class="text-danger">* Pembuat form yang tercatat di sistem sesuai dengan identitas login saat
                        memulai approval</small>
                </div>
            </div>

            <div class="col-md-3" hidden="hidden">
                <div class="form-group">
                    <input type="hidden" name="request_type" class="request_type" value="{{ $request_type }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Pilihan Area / SalesPoint</label>
                    <select class="form-control select2 salespoint_select2">
                        <option value="" data-isjawasumatra="-1">-- Pilih SalesPoint --</option>
                        @foreach ($available_salespoints as $region)
                            <optgroup label="{{ $region->first()->region_name() }}">
                                @foreach ($region as $salespoint)
                                    <option value="{{ $salespoint->id }}" data-status="{{ $salespoint->status }}"
                                        data-isjawasumatra="{{ $salespoint->isJawaSumatra }}"
                                        data-region="{{ $salespoint->region }}">
                                        {{ $salespoint->name }}
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <small class="text-danger">* SalesPoint yang muncul berdasarkan hak akses tiap akun</small>
                </div>
                <span class="spinner-border text-danger loading_salespoint_select2" role="status" style="display:none">
                    <span class="sr-only">Loading...</span>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="required_field">Pilih Matriks Approval</label>
                <select class="form-control select2 authorization_select2" disabled>
                    <option value="">-- Pilih Matriks Approval --</option>
                </select>
                <small class="text-danger">* Pilihan Matriks Approval yang muncul berdasarkan salespoint yang dipilih.
                    Untuk membuat Matriks Approval dapat melakukan request ke super admin</small>
            </div>
            <div class="col-md-12 box p-3 mb-3">
                <div class="font-weight-bold h5">Urutan Approval</div>
                <div class="authorization_list_field row row-cols-md-3 row-cols-2 p-3">
                    <div>Belum memilih Matriks Approval</div>
                </div>
            </div>

            {{-- PARTING --}}
            <div class="col-md-12 box p-3 mt-3">
                <h5 class="font-weight-bold required_field">Daftar Barang</h5>
                <table class="table table-bordered table_item">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Jumlah</th>
                            <th>Harga Sewa Harian</th>
                            <th>Sub Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="empty_row text-center">
                            <td colspan="8">Item belum dipilih</td>
                        </tr>
                    </tbody>
                </table>

                <div class="row justify-content-between item_adder">
                    <div class="row col-md-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Pilih Item</label>
                                <select class="form-control select2 select_item">
                                    <option value="">-- Pilih Item --</option>
                                            @foreach ($product as $product)
                                                <option value="{{ $product->id }}"
                                                    data-code="{{ $product->code }}"
                                                    data-alias="{{ $product->alias }}"
                                                    data-dimension="{{ $product->dimension }}"
                                                    data-stock="{{ $product->jml_stock }}"
                                                    data-weight="{{ $product->berat_barang }}"
                                                    data-hargasewa="{{ $product->harga_sewa_harian }}"
                                                    data-salespoint="{{ $product->salespoint }}">
                                                    {{ $product->nama_barang }} ({{ $product->code }})
                                                </option>
                                            @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label class="required_field">Hari Sewa</label>
                        <input type="number" class="form-control count_item autonumber" min="1">
                    </div>
                    <div class="form-group col-md-3 pl-1">
                        <label class="required_field">Harga Item</label>
                        <input class="form-control rupiah price_item" readonly>
                    </div>
                    <div class="form-group col-md-3 pl-1">
                        <label class="required_field">Sub Total</label>
                        <input class="form-control rupiah subtot_price" readonly>
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label>&nbsp</label>
                        <button type="button" class="btn btn-primary form-control add_button"
                            onclick="addItem(this)">Tambah</button>
                    </div>
                </div>
            </div>

            <div class="col-md-12 box p-3 mt-3">
                <h5 class="font-weight-bold required_field">Daftar Customer</h5>
                <table class="table table-bordered table_customer">
                    <thead>
                        <tr>
                            <th>Kode Customer</th>
                            <th>Nama Customer</th>
                            <th>Store Manager</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Tipe</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="row">
                    <div class="row col-md-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Pilih Customer</label>
                                <select class="form-control select2 select_customer">
                                    <option value="">-- Pilih Customer --</option>
                                    @foreach ($customers as $customers)
                                        <option value="{{ $customers->id }}" data-id="{{ $customers->id }}"
                                            data-code="{{ $customers->code }}" data-name="{{ $customers->name }}"
                                            data-salesperson="{{ $customers->store_staff }}" data-type="{{ $customers->type }}">
                                            {{ $customers->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2 pr-1 pt-4 m-2">
                        <label>&nbsp</label>
                        <button type="button" class="btn btn-primary" onclick="addCustomer(this)">Tambah Customer</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3 bottom_action">
            <button type="button" class="btn btn-info" onclick="addRequest(0)" id="draftbutton">Simpan Sebagai
                Draft</button>
            <button type="button" class="btn btn-primary" onclick="addRequest(1)" id="startauthorizationbutton">Mulai
                Approval</button>
            <button type="button" class="btn btn-danger" onclick="reject()" id="rejectbutton"
                style="display:none">Reject</button>
            <button type="button" class="btn btn-success" onclick="approve()" id="approvebutton"
                style="display:none">Approve</button>
        </div>
    </div>
    <form action="/addticket" method="post" enctype="multipart/form-data" id="addform">
        @csrf
        <input type="hidden" name="id" class="ticket_id">
        <input type="hidden" name="updated_at" class="updated_at">
        <div id="input_field">
        </div>
    </form>
    <form action="/deleteticket" method="post" enctype="multipart/form-data" id="deleteform">
        @method('delete')
        @csrf
        <input type="hidden" name="code" class="ticket_code">
    </form>
@endsection
@section('local-js')
    <script src="/js/podetail.js?ver={{ now()->format('Ymd') }}"></script>
    @if (Request::is('po/*'))
        <script>
            $(document).ready(function() {
                $('#loading_modal').modal('show');
                let user = @json(Auth::user());
                let current_auth = @json($ticket->current_authorization());
                let ticket = @json($ticket);
                let ticket_items = @json($ticket->ticket_items_with_attachments());
                let ticket_vendors = @json($ticket->ticket_vendors_with_additional_data());
                let ticket_additional_attachments = @json($ticket->ticket_additional_attachment);

                $('.ticket_id').val(ticket["id"]);
                $('.ticket_code').val(ticket["code"]);
                $('.updated_at').val(ticket["updated_at"]);
                if (ticket["requirement_date"]) {
                    $('.requirement_date').val(ticket["requirement_date"]);
                }
                if (ticket['salespoint_id']) {
                    $('.salespoint_select2').val(ticket['salespoint_id']);
                    $('.salespoint_select2').trigger('change');
                }
                $('.updated_at').val(ticket["updated_at"]);
                $('.is_over_budget_hidden').val(ticket["is_over_budget"]);

                setTimeout(function() {
                    if (ticket['authorization_id']) {
                        $('.authorization_select2').val(ticket['authorization_id']);
                        $('.authorization_select2').trigger('change');
                    }
                    if (ticket['item_type'] != null || ticket['item_type'] == 'undefined') {
                        $('.item_type').val(ticket['item_type']);
                        $('.item_type').trigger('change');
                    }
                    if (ticket['request_type'] != null || ticket['request_type'] == 'undefined') {
                        $('.request_type').val(ticket['request_type']);
                        $('.request_type').trigger('change');
                    }
                    if (ticket['is_it'] != null || ticket['is_it'] == 'undefined') {
                        $('.is_it').val(ticket['is_it']);
                        $('.is_it').trigger('change');
                    }
                    if (ticket['budget_type'] != null || ticket['item_type'] == 'undefined') {
                        $('.budget_type').val(ticket['budget_type']);
                        $('.budget_type').trigger('change');
                    }
                    if (ticket['division'] != null || ticket['item_type'] == 'undefined') {
                        $('.division_select').val(ticket['division']);
                        $('.division_select').trigger('change');

                        if (ticket['division'] == 'Indirect' && ticket['indirect_salespoint_id'] != null) {
                            $('.indirect_salespoint').val(ticket['indirect_salespoint_id']);
                            $('.indirect_salespoint').trigger('change');
                        }
                    }
                    if (ticket_items.length > 0) {
                        $('.salespoint_select2').prop('disabled', true);
                        $('.request_type').prop('disabled', true);
                        $('.is_it').prop('disabled', true);
                        $('.item_type').prop('disabled', true);
                        $('.budget_type').prop('disabled', true);
                    }
                    $('#loading_modal').modal('hide');
                }, 2500);
                if (ticket_items.length > 0) {
                    $('.table_item tbody:eq(0)').empty();
                }
                ticket_items.forEach(function(item, index) {
                    let naming = item.name;
                    if (item.expired_date != null) {
                        naming = item.name + '<br>(expired : ' + item.expired_date + ')';
                    }
                    let attachments_link = '-';
                    item.attachments.forEach(function(attachment, i) {
                        if (i == 0) attachments_link = "";
                        attachments_link += '<a class="attachment" href="/storage' + attachment.path +
                            '" download="' + attachment.name + '">' + attachment.name + '</a><br>';
                    });
                    let files_data = [];
                    let other_attachment =
                        "<table class='other_attachments small table table-sm table-borderless'><tbody>";
                    item.files.forEach(function(file, i) {
                        let data;
                        data = {
                            id: file.id,
                            file_completement_id: file.file_completement_id,
                            file: '/storage/' + file.path,
                            filename: file.name,
                            name: file.name
                        };
                        other_attachment += "<tr><td>" + data.filename + "</td>"
                        other_attachment += "<td><a href='" + data.file + "' download='" + data.name +
                            "'>tampilkan</a></td></tr>";
                        files_data.push(data);
                    });
                    other_attachment += "</tbody></table>";
                    attachments_link += other_attachment;

                    // get is it and it alias
                    let stringtext = '<tr class="item_list" ';

                    stringtext += 'data-id="' + item.id + '" ';
                    stringtext += 'data-name="' + item.name + '" ';
                    stringtext += 'data-price="' + item.price + '" ';
                    stringtext += 'data-count="' + item.count + '" ';
                    stringtext += 'data-brand="' + item.brand + '" ';
                    stringtext += 'data-type="' + item.type + '" ';
                    if (item.budget_pricing_id != null) {
                        stringtext += 'data-budget_pricing_id="' + item.budget_pricing_id + '" ';
                        stringtext += 'data-is_it="' + item.budget_pricing.isIT + '" ';
                        stringtext += 'data-it_alias="' + item.budget_pricing.IT_alias + '" ';
                    }
                    if (item.ho_budget_id != null) {
                        stringtext += 'data-ho_budget_id="' + item.ho_budget_id + '" ';
                        stringtext += 'data-is_it="' + item.ho_budget.isIT + '" ';
                        stringtext += 'data-it_alias="' + item.ho_budget.IT_alias + '" ';
                    }
                    if (item.maintenance_budget_id != null) {
                        stringtext += 'data-maintenance_budget_id="' + item.maintenance_budget_id + '" ';
                        stringtext += 'data-is_it="' + item.maintenance_budget.isIT + '" ';
                        stringtext += 'data-it_alias="' + item.maintenance_budget.IT_alias + '" ';
                    }
                    stringtext += 'data-expired="' + item.expired_date + '">';
                    stringtext += '<td>' + naming + '</td>'
                    stringtext += '<td>' + (item.brand ?? "") + '</td>'
                    stringtext += '<td>' + (item.type ?? "") + '</td><td class="text-nowrap">' + setRupiah(item
                        .price) + '</td>'
                    stringtext += '<td>' + item.count + '</td><td class="text-nowrap">' + setRupiah(item.count *
                        item.price) + '</td>'
                    stringtext += '<td>' + attachments_link +
                        '</td><td class="text-nowrap"><i class="fa fa-trash text-danger remove_list mr-3" onclick="removeList(this)" aria-hidden="true"></i><button type="button" class="btn btn-primary btn-sm filesbutton">kelengkapan berkas</button></td></tr>';
                    $('.table_item tbody:eq(0)').append(stringtext);

                    $('.table_item tbody:eq(0) .item_list').last().data('files', files_data);
                });
                $('.reason').val(ticket.reason);
                if (ticket_vendors.length > 0) {
                    $('.table_vendor').find('tbody').empty();
                }
                ticket_vendors.forEach(function(vendor, index) {
                    let type = (vendor.type == 0) ? 'Terdaftar' : 'One Time Vendor';
                    let code = (vendor.code == null) ? '-' : vendor.code;
                    $('.table_vendor').find('tbody').append('<tr class="vendor_item_list" data-vendor_id="' +
                        vendor.vendor_id + '" data-id="' + vendor.id + '"><td>' + code + '</td><td>' +
                        vendor.name + '</td><td>' + vendor.salesperson + '</td><td>' + vendor.phone +
                        '</td><td>' + type +
                        '</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>'
                    );
                });
                if (ticket_vendors.length < 2) {
                    // need ba
                    $('.vendor_ba_field').show();
                    if (ticket.ba_vendor_filepath != null) {
                        $('#vendor_ba_preview').show();
                        $('#vendor_ba_preview').on('click', function() {
                            window.open("/storage" + (ticket.ba_vendor_filepath ?? "/"));
                        });
                    }
                } else {
                    // no need ba
                    $('.vendor_ba_field').hide();
                    $('.vendor_ba_file').val('');
                }
                $('#attachment_list').empty();
                ticket_additional_attachments.forEach(function(attachment, index) {
                    $('#attachment_list').append('<div><a class="opt_attachment" href="/storage' + attachment
                        .path + '" download="' + attachment.name +
                        '">tampilkan attachment</a><span class="remove_attachment">X</span></div>')
                });

            });

            function deleteTicket() {
                $('#deleteform').submit();
            }
        </script>
    @endif
    @yield('fri-js')
@endsection
