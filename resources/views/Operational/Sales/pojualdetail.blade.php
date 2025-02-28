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
                    <h1 class="m-0 text-dark">Pengadaan Barang Jasa @isset($ticket)
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
                        <li class="breadcrumb-item active">Pengadaan Barang Jasa @isset($ticket)
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
                    <label class="required_field">XXXXXXXXXXXX</label>
                    <input type="text" class="form-control is_over_budget_hidden" readonly>
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
                                        {{ $salespoint->name }} --
                                        {{ $salespoint->code }}</option>
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
            <div class="col-md-2 division_field d-none">
                <div class="form-group">
                    <label class="required_field">Divisi</label>
                    <select class="form-control division_select select2" name="division">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach (config('customvariable.division') as $division)
                            <option value="{{ $division }}">{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 indirect_salespoint_field d-none">
                <div class="form-group">
                    <label class="required_field">Indirect Salespoint</label>
                    <select class="form-control indirect_salespoint select2" name="indirect_salespoint" disabled>
                        <option value="">-- Pilih Salespoint Indirect --</option>
                        @foreach ($indirect_salespoints as $salespoint)
                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }} --
                                {{ $salespoint->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4 form-group">
                <label class="required_field">Pilih Matriks Approval</label>
                <select class="form-control select2 authorization_select2" disabled>
                    <option value="">-- Pilih Matriks Approval --</option>
                </select>
                <small class="text-danger">* Pilihan Matriks Approval yang muncul berdasarkan salespoint yang dipilih.
                    Untuk membuat Matriks Approval dapat melakukan request ke super admin</small>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label><br>
                <button type="button" class="btn btn-warning" id="oldbudget_button" data-toggle="modal"
                    data-target="#oldbudget_modal" style="display: none">
                    Tampilkan Budget Aktif
                </button>
            </div>
            <div class="col-md-12 box p-3 mb-3">
                <div class="font-weight-bold h5">Urutan Approval</div>
                <div class="authorization_list_field row row-cols-md-3 row-cols-2 p-3">
                    <div>Belum memilih Matriks Approval</div>
                </div>
            </div>

            {{-- PARTING --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Jenis Item</label>
                    <select class="form-control item_type">
                        <option value="">-- Pilih Jenis Item --</option>
                        <option value="0">Barang</option>
                        <option value="1">Jasa</option>
                        <option value="2">Maintenance</option>
                        <option value="3">HO</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Jenis Pengadaan</label>
                    <select class="form-control request_type" disabled>
                        <option value="">-- Pilih Jenis Pengadaan --</option>
                        <option value="0">Baru</option>
                        <option value="2">Repeat Order</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Jenis IT</label>
                    <select class="form-control is_it" disabled>
                        <option value="">-- Pilih Jenis IT --</option>
                        <option value="1">IT</option>
                        <option value="0">Non-IT</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="required_field">Jenis Budget</label>
                    <select class="form-control budget_type" disabled>
                        <option value="">-- Pilih Jenis Budget --</option>
                        <option value="0">Budget</option>
                        <option value="1">Non Budget</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12 box p-3 mt-3">
                <h5 class="font-weight-bold required_field">Daftar Barang</h5>
                <table class="table table-bordered table_item">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Merk</th>
                            <th>Tipe</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Attachment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="empty_row text-center">
                            <td colspan="8">Item belum dipilih</td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-none row justify-content-between budget_item_adder">
                    <div class="row col-md-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Pilih Item</label>
                                <select class="form-control select2 select_budget_item">
                                    <option value="">-- Pilih Item Budget --</option>
                                    @foreach ($budget_category_items as $item)
                                        <optgroup label="{{ $item->name }}">
                                            @foreach ($item->budget_pricing as $pricing)
                                                <option value="{{ $pricing->id }}"
                                                    data-brand="{{ $pricing->budget_brand }}"
                                                    data-type="{{ $pricing->budget_type }}"
                                                    data-categorycode="{{ $item->code }}"
                                                    data-is_it="{{ $pricing->isIT }}"
                                                    data-it_alias="{{ $pricing->IT_alias }}"
                                                    data-minjs="{{ $pricing->injs_min_price }}"
                                                    data-maxjs="{{ $pricing->injs_max_price }}"
                                                    data-minoutjs="{{ $pricing->outjs_min_price }}"
                                                    data-maxoutjs="{{ $pricing->outjs_max_price }}">{{ $pricing->name }}
                                                    ({{ $pricing->code }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 budget_expired_field form-group" style="display: none">
                            <label class="optional_field">Expired Date</label>
                            <input type="date" class="form-control form-control-file budget_expired_date">
                            <small class="text-danger">* Hanya untuk pengadaan APAR</small>
                        </div>
                        <div class="col-12 budget_olditem_field d-none">
                            <label class="required_field">Foto Item Lama</label>
                            <input type="file" class="form-control-file budget_olditem_file"
                                accept="image/*,application/pdf">
                            <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                        </div>
                    </div>
                    <div class="col-md-4 pl-1 row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Pilih Merk</label>
                                <select class="form-control select_budget_brand" disabled>
                                </select>
                            </div>
                            <div class="form-group input_budget_brand_field" style="display: none">
                                <label class="required_field">Nama Merk Lain</label>
                                <input class="form-control input_budget_brand">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Pilih Tipe</label>
                                <select class="form-control select_budget_type" disabled>
                                </select>
                            </div>
                            <div class="form-group input_budget_type_field" style="display: none">
                                <label class="required_field">Nama Tipe Lain</label>
                                <textarea class="form-control input_budget_type" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-12 budget_ba_field" style="display: none">
                            <label class="required_field">Berita Acara</label>
                            <input type="file" class="form-control-file budget_ba_file"
                                accept="application/pdf,application/vnd.ms-excel">
                            <small class="text-danger">*pdf, xls (MAX 5MB)</small>
                        </div>
                    </div>
                    <div class="form-group col-md-3 pl-1">
                        <label class="required_field">Harga Item</label>
                        <input class="form-control rupiah price_budget_item">
                        <small>
                            Area : <span class="font-weight-bold area_status">-</span><br>
                            Harga Minimum : <span class="font-weight-bold item_min_price">-</span><br>
                            Harga Maksimum : <span class="font-weight-bold item_max_price">-</span><br>
                        </small>
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label class="required_field">Jumlah</label>
                        <input type="number" class="form-control count_budget_item autonumber" min="1">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label>&nbsp</label>
                        <button type="button" class="btn btn-primary form-control add_button"
                            onclick="addBudgetItem(this)">Tambah</button>
                    </div>
                </div>

                <div class="d-none row justify-content-between nonbudget_item_adder">
                    <div class="row col-md-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Nama Item</label>
                                <input type="text" class="form-control input_nonbudget_name">
                            </div>
                        </div>
                        <div class="col-12 nonbudget_olditem_field d-none">
                            <label class="required_field">Foto Item Lama</label>
                            <input type="file" class="form-control-file nonbudget_olditem_file"
                                accept="image/*,application/pdf">
                            <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                        </div>
                    </div>
                    <div class="col-md-4 pl-1 row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Merk</label>
                                <input type="text" class="form-control input_nonbudget_brand">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Tipe</label>
                                <textarea class="form-control input_nonbudget_type" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3 pl-1">
                        <label class="required_field">Harga Item</label>
                        <input class="form-control rupiah price_nonbudget_item">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label class="required_field">Jumlah</label>
                        <input type="number" class="form-control count_nonbudget_item autonumber" min="1">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label>&nbsp</label>
                        <button type="button" class="btn btn-primary form-control add_button"
                            onclick="addNonBudgetItem()">Tambah</button>
                    </div>
                </div>

                <div class="d-none row justify-content-between maintenance_item_adder">
                    <div class="row col-md-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Pilih Item</label>
                                <select class="form-control select2" id="maintenance_item_select">
                                    <option value="">-- Pilih Item Maintenance --</option>
                                    @foreach ($maintenance_budgets as $category_name => $budget)
                                        <optgroup label="{{ $category_name }}">
                                            @foreach ($budget as $item)
                                                <option value="{{ $item->id }}" data-is_it="{{ $item->isIT }}"
                                                    data-it_alias="{{ $item->IT_alias }}">{{ $item->name }}
                                                    ({{ $item->code }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 pl-1 row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="optional_field">Nama Merk</label>
                                <input class="form-control" id="maintenance_brand_input">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="optional_field">Nama Tipe Lain</label>
                                <input class="form-control" id="maintenance_type_input">
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3 pl-1">
                        <label class="required_field">Harga Item</label>
                        <input class="form-control rupiah" id="maintenance_price_input">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label class="required_field">Jumlah</label>
                        <input type="number" class="form-control autonumber" min="1"
                            id="maintenance_count_input">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label>&nbsp</label>
                        <button type="button" class="btn btn-primary form-control add_button"
                            onclick="addMaintenanceItem(this)">Tambah</button>
                    </div>
                </div>

                <div class="d-none row justify-content-between ho_item_adder">
                    <div class="row col-md-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Pilih Item</label>
                                <select class="form-control select2" id="ho_item_select">
                                    <option value="">-- Pilih Item HO --</option>
                                    @foreach ($ho_budgets as $category_name => $budget)
                                        <optgroup label="{{ $category_name }}">
                                            @foreach ($budget as $item)
                                                <option value="{{ $item->id }}" data-is_it="{{ $item->isIT }}"
                                                    data-it_alias="{{ $item->IT_alias }}">{{ $item->name }}
                                                    ({{ $item->code }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 pl-1 row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="optional_field">Nama Merk</label>
                                <input class="form-control" id="ho_brand_input">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="optional_field">Nama Tipe Lain</label>
                                <input class="form-control" id="ho_type_input">
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3 pl-1">
                        <label class="required_field">Harga Item</label>
                        <input class="form-control rupiah" id="ho_price_input">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label class="required_field">Jumlah</label>
                        <input type="number" class="form-control autonumber" min="1" id="ho_count_input">
                    </div>
                    <div class="form-group col-md-1 pl-1">
                        <label>&nbsp</label>
                        <button type="button" class="btn btn-primary form-control add_button"
                            onclick="addHOItem(this)">Tambah</button>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-3">
                <div class="form-group">
                    <label class="required_field">Alasan Pengadaan Barang atau Jasa</label>
                    <textarea class="form-control reason" rows="3"></textarea>
                </div>
            </div>

            <div class="col-md-12 box p-3 mt-3">
                <h5 class="font-weight-bold required_field">Daftar Vendor</h5>
                <table class="table table-bordered table_vendor">
                    <thead>
                        <tr>
                            <th>Kode Vendor</th>
                            <th>Nama Vendor</th>
                            <th>Sales Person</th>
                            <th>Telfon</th>
                            <th>Tipe</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-4">
                        <h5>Vendor Terdaftar</h5>
                        <div class="form-group">
                            <label class="required_field">Pilih Vendor</label>
                            <select class="form-control select2 select_vendor">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" data-id="{{ $vendor->id }}"
                                        data-code="{{ $vendor->code }}" data-name="{{ $vendor->name }}"
                                        data-salesperson="{{ $vendor->salesperson }}">{{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="addVendor(this)">Tambah Vendor
                            Terdaftar</button>
                    </div>
                    <div class="col-md-8">
                        <h5>One Time Vendor</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Nama Vendor</label>
                                    <input type="text" class="form-control ot_vendor_name"
                                        placeholder="Masukan nama vendor">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Sales Person</label>
                                    <input type="text" class="form-control ot_vendor_sales"
                                        placeholder="Masukkan nama sales">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Telfon</label>
                                    <input type="text" class="form-control ot_vendor_phone"
                                        placeholder="Masukkan nomor telfon">
                                </div>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary" onclick="addOTVendor(this)">Tambah
                                    One Time Vendor</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group vendor_ba_field">
                            <label class="required_field">Berita Acara</label>
                            <input type="file" class="form-control-file vendor_ba_file"
                                accept="application/pdf,application/vnd.ms-excel">
                            <small class="text-danger">* Wajib menyertakan berita acara untuk pemilihan satu vendor
                                (.pdf/xls MAX 5MB)</small><br>
                            <a class="text-primary" id="vendor_ba_preview" style="display: none">tampilkan berita
                                acara</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 box p-3 mt-3 d-none" id="fri_form_field">
                @php
                    $isEditFRI = true;
                @endphp
                @include('Operational.fri_form')
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3 bottom_action">
            <button type="button" class="btn btn-info" onclick="addRequest(0)" id="draftbutton">Simpan Sebagai
                Draft</button>
            <button type="button" class="btn btn-primary" onclick="addRequest(1)" id="startauthorizationbutton">Mulai
                Approval</button>




            @isset($ticket->code)
                <button type="button" class="btn btn-danger" onclick="deleteTicket()" id="deleteticketbutton">Hapus
                    Form</button>
            @endisset
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

    <!-- Modal -->
    <div class="modal fade" id="filesmodal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <input type="hidden" class="itempos">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kelengkapan Berkas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col small text-danger">* file max 5MB</div>
                        <div class="col-4">
                            <input type="text" class="form-control form-control-sm" placeholder="filter"
                                id="file_row_filter">
                        </div>
                    </div>
                    <style>
                        #scrolling_div {
                            height: 60vh !important;
                            overflow-y: scroll;
                        }
                    </style>
                    <div id="scrolling_div" class="small">
                        @foreach ($filecategories as $filecategory)
                            <h5>{{ $filecategory->name }}<br></h5>
                            <table class="table table-sm table-striped tablefiles">
                                <thead>
                                    <tr>
                                        <th>Pilih</th>
                                        <th>Nama Kelengkapan</th>
                                        <th colspan="2">File terpilih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($filecategory->file_completements as $file_completement)
                                        <tr class="file_row" data-file_completement_id="{{ $file_completement->id }}"
                                            data-name="{{ $file_completement->name }}">
                                            <td class="align-middle">
                                                <input type="checkbox" class="file_check">
                                            </td>
                                            <td class="align-middle" width="350">{{ $file_completement->name }}</td>
                                            <td class="align-middle">
                                                <button class="btn btn-info file_button_upload btn-sm"
                                                    disabled>upload</button>
                                                <input class="inputFile" type="file" style="display:none;">
                                            </td>
                                            <td class="align-middle tdbreak">
                                                <a class="file_url">-</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary button_save_files">Save</button>
                </div>
            </div>
        </div>
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
                    <h4>Inventory Budget</h4>
                    <div class="row" id="inventory_budget">
                        <div class="col-4">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold">Status</td>
                                        <td class="status">-</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Periode</td>
                                        <td class="period">-</td>
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
                                        <th>Keterangan</th>
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
                    <hr>
                    <h4>Maintenance Budget</h4>
                    <div class="row" id="assumption_budget">
                        <div class="col-4">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold">Status</td>
                                        <td class="status">-</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Periode</td>
                                        <td class="period">-</td>
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
                    <h4>HO Budget</h4>
                    <div class="row" id="ho_budget">
                        <div class="col-12 row info">
                            <div class="col-3 font-weight-bold">Salespoint</div>
                            <div class="col-3 salespoint">-</div>
                            <div class="col-3 font-weight-bold">Divisi</div>
                            <div class="col-3 division">-</div>
                            <div class="col-3 font-weight-bold">Tahun</div>
                            <div class="col-3 year">-</div>
                            <div class="col-3 font-weight-bold">Nama Pengaju</div>
                            <div class="col-3 requested_by">-</div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Divisi</label>
                                <select class="form-control division_select_modal">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach (config('customvariable.division') as $division)
                                        <option value="{{ $division }}">{{ $division }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-column table-responsive">
                            <table class="table table-bordered list_table table-sm small">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Kode</th>
                                        <th rowspan="2">Kategori</th>
                                        <th rowspan="2">Nama</th>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <th colspan="5">{{ \Carbon\Carbon::create()->month($i)->format('F') }}</th>
                                        @endfor
                                    </tr>
                                    <tr>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <th>Qty</th>
                                            <th>Value</th>
                                            <th>Pending</th>
                                            <th>Terpakai</th>
                                            <th>sisa</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="d-none list_table_loading">
                                        <td colspan="63">
                                            Loading...
                                        </td>
                                    </tr>
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

    <div class="modal fade" id="notifOverBudgetModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Harga item melebih harga budget yang tersedia (over budget), apakah anda ingin
                        melanjutkan pengadaan ?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="overBudget()">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="overBudgetModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Over Budget</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <div id="modalOverBudgetCountainer">
                                    <div id="template_over_budget" style="display: none;">
                                        <label>Nama Barang : </label>
                                        <input class="form-control nama_barang" name="nama_barang_over_budget[]"
                                            readonly></input>
                                        <label>Nilai Budget : </label>
                                        <input class="form-control nilai_budget" readonly></input>
                                        <label>Nilai Ajuan : </label>
                                        <input class="form-control nilai_ajuan"readonly></input>
                                        <label>Selisih : </label>
                                        <input class="form-control selisih" readonly></input>
                                        <br>
                                        <hr>
                                    </div>
                                </div>
                                <label class="required_field">Reason (Wajib) :</label>
                                <input type="text-area" class="form-control reason_over_budget" name="reason_over_budget"
                                    required>
                                <br>

                                <label>Berikut line approval terbaru proses pengadaan over budget :</label>

                                <div class="col-md-12 box p-3 mb-3">
                                    <div class="font-weight-bold h5">Urutan Approval Pengadaan</div>
                                    <div class="authorization_list_field row row-cols-md-3 row-cols-2 p-3">
                                        <div>Belum memilih Matriks Approval</div>
                                    </div>
                                </div>

                                <div class="col-md-12 box p-3 mb-3">
                                    <div class="font-weight-bold h5">Urutan Approval Over Budget</div>
                                    <br>
                                    <div>
                                        <div class="mb-2" id="approval_over_budget_area">
                                            <span class="font-weight-bold">Yulita Adrianto -- National Finance & Admin
                                                Controller
                                            </span>
                                            <br>
                                            <span>
                                                Diperiksa Oleh
                                            </span>
                                        </div>

                                        <div class="mb-2">
                                            <span class="font-weight-bold">Wiwik Endang Wijaya -- Head
                                                Of Finance Accounting
                                            </span>
                                            <br>
                                            <span>
                                                Disetujui Oleh
                                            </span>
                                        </div>

                                        <div class="mb-2">
                                            <span class="font-weight-bold">Lidya Hartati -- Head Of
                                                Operation
                                            </span>
                                            <br>
                                            <span>
                                                Disetujui Oleh
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="addRequest(3)">Mulai Approval</button>
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

            $('#file_row_filter').change(function() {
                let filter_text = $(this).val();
                $('.file_row').each(function(index, item) {
                    let row_name = $(item).data('name').toLowerCase();
                    if (row_name.includes(filter_text)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
    <script src="/js/ticketingdetail.js?ver={{ now()->format('Ymd') }}"></script>
    <script>
        function deleteTicket() {
            $('#deleteform').submit();
        }
    </script>
    @yield('fri-js')
@endsection
