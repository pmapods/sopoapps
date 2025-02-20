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
                    <h1 class="m-0 text-dark">Material</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Material</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMaterialModal"
                    id="addbutton">
                    Tambah Material
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="materialDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Total Stock</th>
                                <th>Cabang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($material as $key => $material)
                                <tr data-material="{{ $material }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $material->code }}</td>
                                    <td>{{ $material->material }}</td>
                                    <td>{{ $material->tot_stock }}</td>
                                    <td>{{ $material->regency->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMaterialModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Material</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Kode</label>
                                    <input type="text" class="form-control code" name="code"
                                        placeholder="Masukkan kode material" required>
                                    <small class="form-text text-danger">Kode material bersifat unik / tidak boleh sama
                                        dengan
                                        kode material lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Nama Material</label>
                                    <input type="text" class="form-control name" name="name"
                                        placeholder="Masukkan nama material" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Alias</label>
                                    <input type="text" class="form-control alias" name="alias"
                                        placeholder="Masukkan alias" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Dimesi (M<sup>2</sup>)</label>
                                    <div class="input-group">
                                        <input type="number" min="1" class="form-control panjang" name="panjang"
                                        placeholder="Panjang" required>
                                        <label class="pt-2">&nbsp;X&nbsp;</label>
                                        <input type="number" min="1" class="form-control lebar" name="lebar"
                                        placeholder="Lebar" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">SalesPoint Stock</label>
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
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary if_edit_disable" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary if_edit_disable" onclick="addMaterial()">Tambah
                            Material</button>
                    </div>
                </div>
        </div>
        <form action="/addmaterial" method="post" id="#addform">
            @csrf
            <div class="inputfield">
            </div>
        </form>
    </div>
    
    <div class="modal fade" id="detailMaterialModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Material</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Kode</label>
                                <input type="text" class="form-control code" name="code"
                                    placeholder="Masukkan kode material" readonly>
                                <small class="form-text text-danger">Kode material bersifat unik / tidak boleh sama
                                    dengan
                                    kode material lainnya</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Nama Material</label>
                                <input type="text" class="form-control name" name="name"
                                    placeholder="Masukkan nama material" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Alias</label>
                                <input type="text" class="form-control alias" name="alias"
                                    placeholder="Masukkan alias" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Dimesi (M<sup>2</sup>)</label>
                                <div class="input-group">
                                    <input type="number" min="1" class="form-control panjang" name="panjang"
                                    placeholder="Panjang" required>
                                    <label class="pt-2">&nbsp;X&nbsp;</label>
                                    <input type="number" min="1" class="form-control lebar" name="lebar"
                                    placeholder="Lebar" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">SalesPoint Stock</label>
                                <select class="form-control select2" name="city_id" disabled>
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary if_edit_disable" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger if_edit_disable"
                        onclick="deleteMaterial()">Hapus</button>
                    <button type="button" class="btn btn-primary if_edit_disable" onclick="updateMaterial()">Update
                        Customer</button>
                </div>
            </div>
        </div>
        <form action="/updatematerial" method="post" id="updateform">
            @csrf
            @method('patch')
            <input type="hidden" name="material_id">
            <div class="inputfield">
            </div>
        </form>

        <form action="/deletematerial" method="post" id="deleteform">
            @csrf
            @method('delete')
            <input type="hidden" name="material_id">
            <div class="inputfield">
            </div>
        </form>
    </div>

@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#materialDT').DataTable(datatable_settings);
            $('#materialDT tbody').on('click', 'tr', function() {
                let modal = $('#detailMaterialModal');
                let data = $(this).data('material');
                
                let dimension = JSON.parse(data.dimension);
                let panjang = modal.find('.panjang');
                let lebar = modal.find('.lebar');
                
                dimension.forEach((item, index) => {
                    panjang.val(item.panjang);
                    lebar.val(item.lebar);
                });

                let kode = modal.find('.code');
                let name = modal.find('.name');
                let alias = modal.find('.alias');
                let city = modal.find('select[name="city_id"]');

                kode.val(data['code']);
                name.val(data['material']);
                alias.val(data['alias']);
                city.val(data['salespoint']);
                city.trigger('change');

                modal.modal('show');
            });
        });

        function addMaterial() {
            let modal = $('#addMaterialModal');
            let kode = modal.find('.code').val();
            let name = modal.find('.name').val();
            let alias = modal.find('.alias').val();
            let panjang = modal.find('.panjang').val();
            let lebar = modal.find('.lebar').val();
            let city = modal.find('select[name="city_id"]').val();

            let dimension = [];
            if (city == "") {
                alert('Harap memilih kota/salespoint');
                return;
            }
                    
            dimension.push({
                "panjang": panjang,
                "lebar": lebar,
                "UOM" : "M2"
            })
            
            // form filling
            let form = modal.find('form');
            let inputfield = form.find('.inputfield');
            
            inputfield.empty();
            inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
            inputfield.append('<input type="hidden" name="nama" value="' + name + '">');
            inputfield.append('<input type="hidden" name="alias" value="' + alias + '">');
            inputfield.append("<input type='hidden' name='dimension' value='" + JSON.stringify(dimension) + "'>");
            inputfield.append('<input type="hidden" name="city" value="' + city + '">');
            form.submit();       
        }

        function updateMaterial() {
            let modal = $('#detailMaterialModal');
            let kode = modal.find('.code').val();
            let name = modal.find('.name').val();
            let alias = modal.find('.alias').val();
            let panjang = modal.find('.panjang').val();
            let lebar = modal.find('.lebar').val();

            let dimension = [];
                    
            dimension.push({
                "panjang": panjang,
                "lebar": lebar,
                "UOM" : "M2"
            })

            // form filling
            let form = $('#updateform');
            let inputfield = form.find('.inputfield');
            
            inputfield.empty();
            inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
            inputfield.append('<input type="hidden" name="nama" value="' + name + '">');
            inputfield.append('<input type="hidden" name="alias" value="' + alias + '">');
            inputfield.append("<input type='hidden' name='dimension' value='" + JSON.stringify(dimension) + "'>");
            form.submit();  
        }

        function deleteMaterial() {
            if (confirm('Data Material akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                let modal = $('#detailMaterialModal');
                let kode = modal.find('.code').val();
                let form = $('#deleteform');
                let inputfield = form.find('.inputfield');
                
                inputfield.empty();
                inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
                form.submit();
            } else {

            }
        }

    </script>
@endsection
