@extends('Layout.app')
@section('local-css')
    <style>
        #pills-tab .nav-link {
            background-color: #a01e2b48;
            color: black !important;
        }

        #pills-tab .nav-link.active {
            background-color: #A01E2A;
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Vendor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Vendor</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addVendorModal"
                    id="addbutton">
                    Tambah Vendor
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="vendorDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Alias/Brand</th>
                                <th>Alamat</th>
                                <th>Kota</th>
                                <th>Sales Person</th>
                                <th>Telfon</th>
                                <th>Email</th>
                                <th>Rekening</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($vendors as $key => $vendor)
                                <tr data-vendor="{{ $vendor }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $vendor->code }}</td>
                                    <td>{{ $vendor->name }}</td>
                                    <td>{{ $vendor->alias }}</td>
                                    <td>{{ $vendor->address }}</td>
                                    <td>{{ $vendor->regency->name }}</td>
                                    <td>{{ $vendor->salesperson }}</td>
                                    <td>{{ $vendor->phone }}</td>
                                    <td>{{ implode(",\n", $vendor->emails()) }}</td>
                                    <td>{{ $vendor->bank }} - {{ $vendor->no_rekening }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <form method="post" id="submitform">
        @csrf
        <div></div>
    </form>

    <div class="modal fade" id="addVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="/addvendor" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Vendor</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Kode</label>
                                    <input type="text" class="form-control" name="code"
                                        placeholder="Masukkan Kode Vendor" required>
                                    <small class="form-text text-danger">Kode vendor bersifat unik / tidak boleh sama
                                        dengan
                                        kode vendor lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Nama</label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="Masukkan Nama Vendor" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Alias</label>
                                    <input type="text" class="form-control" name="alias"
                                        placeholder="Masukkan Alias" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Alamat</label>
                                    <input type="text" class="form-control" name="address"
                                        placeholder="Masukkan Alamat Vendor" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Kota Lokasi Vendor</label>
                                    <select class="form-control select2" name="city_id">
                                        <option value="">-- Pilih Kota --</option>
                                        @foreach ($provinces as $province)
                                            <optgroup label="{{ $province->name }}">
                                                @foreach ($province->regencies as $regency)
                                                    <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="optional_field">Sales Person</label>
                                    <input type="text" class="form-control" name="salesperson"
                                        placeholder="Masukkan nama sales vendor">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="optional_field">Telfon</label>
                                    <input type="text" class="form-control" name="phone"
                                        placeholder="Masukkan no telfon vendor">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="optional_field">Email</label>
                                    <textarea class="form-control" name="email" placeholder="Masukkan email vendor"></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Bank</label>
                                    <select class="form-control select2" name="bank">
                                        <option value="">-- Pilih Bank --</option>
                                        <option value="mandiri">Mandiri</option>
                                        <option value="bca">BCA</option>
                                        <option value="bri">BRI</option>
                                        <option value="bni">BNI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Rekening</label>
                                    <input type="text" class="form-control rekening" name="rekening"
                                        placeholder="Masukkan no rekening">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Tambah Vendor</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="updateVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="/updatevendor" method="post">
                @csrf
                @method('patch')
                <input type="hidden" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Vendor</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Kode</label>
                                    <input type="text" class="form-control" name="code"
                                        placeholder="Masukkan Kode Vendor" readonly>
                                    <small class="form-text text-danger">Kode vendor bersifat unik / tidak boleh sama
                                        dengan
                                        kode vendor lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Nama</label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="Masukkan Nama Vendor" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Alias/Brand</label>
                                    <input type="text" class="form-control" name="alias"
                                        placeholder="Masukkan Alias" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Alamat</label>
                                    <input type="text" class="form-control" name="address"
                                        placeholder="Masukkan Alamat Vendor" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Kota Lokasi Vendor</label>
                                    <select class="form-control select2" name="city_id">
                                        <option value="">-- Pilih Kota --</option>
                                        @foreach ($provinces as $province)
                                            <optgroup label="{{ $province->name }}">
                                                @foreach ($province->regencies as $regency)
                                                    <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="optional_field">Sales Person</label>
                                    <input type="text" class="form-control" name="salesperson"
                                        placeholder="Masukkan nama sales vendor">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="optional_field">Telfon</label>
                                    <input type="text" class="form-control" name="phone"
                                        placeholder="Masukkan no telfon vendor">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="optional_field">Email</label>
                                    <textarea class="form-control" name="email" placeholder="Masukkan email vendor"></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Bank</label>
                                    <select class="form-control select2" name="bank">
                                        <option value="">-- Pilih Bank --</option>
                                        <option value="mandiri">Mandiri</option>
                                        <option value="bca">BCA</option>
                                        <option value="bri">BRI</option>
                                        <option value="bni">BNI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Rekening</label>
                                    <input type="text" class="form-control rekening" name="rekening"
                                        placeholder="Masukkan no rekening">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger" onclick="deleteVendor(this)">Hapus Vendor</button>
                        <button type="submit" class="btn btn-primary">Perbarui Vendor</button>
                    </div>
                </div>
            </form>
            <form action="/deletevendor" method="post" id="deleteform">
                @csrf
                @method('delete')
                <input type="hidden" name="id">
            </form>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#vendorDT').DataTable(datatable_settings);
            var armadatable = $('#vendorArmadaDT').DataTable(datatable_settings);
            var securitytable = $('#vendorSecurityDT').DataTable(datatable_settings);
            $('#vendorDT,#vendorArmadaDT,#vendorSecurityDT tbody').on('click', 'tr', function() {
                let modal = $('#updateVendorModal');
                let data = $(this).data('vendor');
                modal.find('input[name="id"]').val(data['id']);
                modal.find('input[name="code"]').val(data['code']);
                modal.find('select[name="type"]').val(data['type']);
                modal.find('input[name="name"]').val(data['name']);
                modal.find('input[name="alias"]').val(data['alias']);
                modal.find('input[name="address"]').val(data['address']);
                modal.find('select[name="city_id"]').val(data['city_id']);
                modal.find('select[name="city_id"]').trigger('change');
                modal.find('input[name="salesperson"]').val(data['salesperson']);
                modal.find('input[name="phone"]').val(data['phone']);
                modal.find('select[name="bank"]').val(data['bank']);
                modal.find('select[name="bank"]').trigger('change');
                modal.find('input[name="rekening"]').val(data['no_rekening']);
                let emails = JSON.parse(data['email']);
                let email_text = emails.join(',\n');
                modal.find('textarea[name="email"]').val(email_text);
                modal.modal('show');
            });

            $('.rekening').on('keyup', function () { 
                this.value = this.value.replace(/[^0-9]/gi, '');
            });
        });

        function deleteVendor(el) {
            if (confirm('Vendor akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                $('#deleteform').submit();
            }
        }
    </script>
@endsection
