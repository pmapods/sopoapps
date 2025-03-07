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
                    <h1 class="m-0 text-dark">Product</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Product</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal"
                    id="addbutton">
                    Tambah Product
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="productDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Category</th>
                                <th>Total Stock</th>
                                <th>Book Stock</th>
                                <th>Available Stock</th>
                                <th>UOM</th>
                                <th>Cabang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($product as $key => $product)
                                <tr data-product="{{ $product }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->nama_barang }}</td>
                                    <td>{{ $product->category->category }}</td>
                                    <td>{{ $product->jml_stock }}</td>
                                    <td>{{ $product->book_stock }}</td>
                                    <td>{{ $product->avail_stock }}</td>
                                    <td>{{ $product->uom->uom }}</td>
                                    <td>{{ $product->regency->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Product</h5>
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
                                        placeholder="Masukkan kode product" required>
                                    <small class="form-text text-danger">Kode product bersifat unik / tidak boleh sama
                                        dengan
                                        kode product lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Nama Product</label>
                                    <input type="text" class="form-control name" name="name"
                                        placeholder="Masukkan nama product" required>
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
                                    <label class="required_field">Category</label>
                                    <select class="form-control select2 category_select" name="category">
                                        <option value="">-- Pilih Category --</option>
                                        @foreach ($category as $category)
                                                <option value="{{ $category->id }}">{{ $category->category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">UOM</label>
                                    <select class="form-control select2 uom_select" name="uom">
                                        <option value="">-- Pilih UOM --</option>
                                        @foreach ($uom as $uom)
                                                <option value="{{ $uom->id }}">{{ $uom->uom }}</option>
                                        @endforeach
                                    </select>
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
                                        <label class="pt-2">&nbsp;X&nbsp;</label>
                                        <input type="number" min="1" class="form-control tinggi" name="tinggi"
                                        placeholder="tinggi" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Harga Jual</label>
                                    <input type="number" class="form-control harga_jual" name="harga_jual"
                                        placeholder="Masukkan harga jual product" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Harga Sewa (Harian)</label>
                                    <input type="number" class="form-control harga_sewa" name="harga_sewa"
                                        placeholder="Masukkan harga sewa product" required>
                                </div>
                            </div>
                            <div class="col-6">
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
                        <button type="button" class="btn btn-primary if_edit_disable" onclick="addProduct()">Tambah
                            Product</button>
                    </div>
                </div>
        </div>
        <form action="/addproduct" method="post" id="#addform">
            @csrf
            <div class="inputfield">
            </div>
        </form>
    </div>
    
    <div class="modal fade" id="detailProductModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Product</h5>
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
                                    placeholder="Masukkan kode product" readonly>
                                <small class="form-text text-danger">Kode product bersifat unik / tidak boleh sama
                                    dengan
                                    kode product lainnya</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Nama Product</label>
                                <input type="text" class="form-control name" name="name"
                                    placeholder="Masukkan nama customer" required>
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
                                <label class="required_field">Category</label>
                                <select class="form-control select2 category_select" name="category">
                                    <option value="">-- Pilih Category --</option>
                                    @foreach ($category2 as $category)
                                            <option value="{{ $category->id }}">{{ $category->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">UOM</label>
                                <select class="form-control select2 uom_select" name="uom">
                                    <option value="">-- Pilih UOM --</option>
                                    @foreach ($uom2 as $uom)
                                            <option value="{{ $uom->id }}">{{ $uom->uom }}</option>
                                    @endforeach
                                </select>
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
                                    <label class="pt-2">&nbsp;X&nbsp;</label>
                                    <input type="number" min="1" class="form-control tinggi" name="tinggi"
                                    placeholder="tinggi" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Harga Jual</label>
                                <input type="number" class="form-control harga_jual" name="harga_jual"
                                    placeholder="Masukkan harga jual product" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Harga Sewa (Harian)</label>
                                <input type="number" class="form-control harga_sewa" name="harga_sewa"
                                    placeholder="Masukkan harga sewa product" required>
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
                        onclick="deleteProduct()">Hapus</button>
                    <button type="button" class="btn btn-primary if_edit_disable" onclick="updateProduct()">Update
                        Customer</button>
                </div>
            </div>
        </div>
        <form action="/updateproduct" method="post" id="updateform">
            @csrf
            @method('patch')
            <input type="hidden" name="product_id">
            <div class="inputfield">
            </div>
        </form>

        <form action="/deleteproduct" method="post" id="deleteform">
            @csrf
            @method('delete')
            <input type="hidden" name="product_id">
            <div class="inputfield">
            </div>
        </form>
    </div>

@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#productDT').DataTable(datatable_settings);
            $('#productDT tbody').on('click', 'tr', function() {
                let modal = $('#detailProductModal');
                let data = $(this).data('product');
                
                let dimension = JSON.parse(data.dimension);
                let panjang = modal.find('.panjang');
                let lebar = modal.find('.lebar');
                let tinggi = modal.find('.tinggi');
                
                dimension.forEach((item, index) => {
                    panjang.val(item.panjang);
                    lebar.val(item.lebar);
                    tinggi.val(item.tinggi);
                });

                let kode = modal.find('.code');
                let name = modal.find('.name');
                let alias = modal.find('.alias');
                let category = modal.find('select[name="category"]');
                let uom = modal.find('select[name="uom"]');
                let harga_jual = modal.find('.harga_jual');
                let harga_sewa = modal.find('.harga_sewa');
                let city = modal.find('select[name="city_id"]');

                kode.val(data['code']);
                name.val(data['nama_barang']);
                alias.val(data['alias']);
                category.val(data['category_id']);
                category.trigger('change');
                uom.val(data['uom_id']);
                uom.trigger('change');
                city.val(data['salespoint']);
                city.trigger('change');
                harga_jual.val(data['harga_jual']);
                harga_sewa.val(data['harga_sewa_harian']);

                modal.modal('show');
            });
        });

        function addProduct() {
            let modal = $('#addProductModal');
            let kode = modal.find('.code').val();
            let name = modal.find('.name').val();
            let alias = modal.find('.alias').val();
            let panjang = modal.find('.panjang').val();
            let lebar = modal.find('.lebar').val();
            let tinggi = modal.find('.tinggi').val();
            let harga_jual = modal.find('.harga_jual').val();
            let harga_sewa = modal.find('.harga_sewa').val();
            let city = modal.find('select[name="city_id"]').val();
            let category = modal.find('select[name="category"]').val();
            let uom = modal.find('select[name="uom"]').val();

            let dimension = [];
            if (category == "") {
                alert('Harap memilih category');
                return;
            }
            if (uom == "") {
                alert('Harap memilih uom');
                return;
            }
            if (city == "") {
                alert('Harap memilih kota/salespoint');
                return;
            }
                    
            dimension.push({
                "panjang": panjang,
                "lebar": lebar,
                "tinggi": tinggi
            })
            
            // form filling
            let form = modal.find('form');
            let inputfield = form.find('.inputfield');
            
            inputfield.empty();
            inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
            inputfield.append('<input type="hidden" name="nama" value="' + name + '">');
            inputfield.append('<input type="hidden" name="alias" value="' + alias + '">');
            inputfield.append("<input type='hidden' name='dimension' value='" + JSON.stringify(dimension) + "'>");
            inputfield.append('<input type="hidden" name="harga_jual" value="' + harga_jual + '">');
            inputfield.append('<input type="hidden" name="harga_sewa" value="' + harga_sewa + '">');
            inputfield.append('<input type="hidden" name="city" value="' + city + '">');
            inputfield.append('<input type="hidden" name="category" value="' + category + '">');
            inputfield.append('<input type="hidden" name="uom" value="' + uom + '">');
            form.submit();       
        }

        function updateProduct() {
            let modal = $('#detailProductModal');
            let kode = modal.find('.code').val();
            let name = modal.find('.name').val();
            let alias = modal.find('.alias').val();
            let panjang = modal.find('.panjang').val();
            let lebar = modal.find('.lebar').val();
            let tinggi = modal.find('.tinggi').val();
            let harga_jual = modal.find('.harga_jual').val();
            let harga_sewa = modal.find('.harga_sewa').val();
            let city = modal.find('select[name="city_id"]').val();
            let category = modal.find('select[name="category"]').val();
            let uom = modal.find('select[name="uom"]').val();

            let dimension = [];
            if (city == "") {
                alert('Harap memilih kota/salespoint');
                return;
            }
                    
            dimension.push({
                "panjang": panjang,
                "lebar": lebar,
                "tinggi": tinggi
            })

            // form filling
            let form = $('#updateform');
            let inputfield = form.find('.inputfield');
            
            inputfield.empty();
            inputfield.append('<input type="hidden" name="kode" value="' + kode + '">');
            inputfield.append('<input type="hidden" name="nama" value="' + name + '">');
            inputfield.append('<input type="hidden" name="alias" value="' + alias + '">');
            inputfield.append("<input type='hidden' name='dimension' value='" + JSON.stringify(dimension) + "'>");
            inputfield.append('<input type="hidden" name="harga_jual" value="' + harga_jual + '">');
            inputfield.append('<input type="hidden" name="harga_sewa" value="' + harga_sewa + '">');
            inputfield.append('<input type="hidden" name="city" value="' + city + '">');
            inputfield.append('<input type="hidden" name="category" value="' + category + '">');
            inputfield.append('<input type="hidden" name="uom" value="' + uom + '">');
            form.submit();  
        }

        function deleteProduct() {
            if (confirm('Data Product akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                let modal = $('#detailProductModal');
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
