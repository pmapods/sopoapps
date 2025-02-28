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
                    <small class="text-danger">*Tanggal estimasi barang ready untuk di kirim</small>
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
                                        <option value="{{ $customers->id }}" 
                                            data-id="{{ $customers->id }}"
                                            data-code="{{ $customers->code }}" 
                                            data-name="{{ $customers->name }}"
                                            data-salesperson="{{ $customers->store_staff }}" 
                                            data-type="{{ $customers->type }}">
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
    <form action="/addpo" method="post" enctype="multipart/form-data" id="addform">
        @csrf
        <input type="hidden" name="id" class="ticket_id">
        <input type="hidden" name="updated_at" class="updated_at" value="{{ now()->translatedFormat('Y-m-d') }}">
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
    <script>
        function deleteTicket() {
            $('#deleteform').submit();
        }
    </script>
    @yield('fri-js')
@endsection
