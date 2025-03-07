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
                    <h1 class="m-0 text-dark">Category</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Category</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal"
                    id="addbutton">
                    Tambah Category
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="categoryDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Category</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($category as $key => $category)
                                <tr data-category="{{ $category }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $category->category }}</td>
                                    <td>{{ $category->created_at }}</td>
                                    <td>{{ $category->updated_at }}</td>
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

    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="/addcategory" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Category</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Category</label>
                                    <input type="text" class="form-control" name="category"
                                        placeholder="Masukkan Category" required>
                                    <small class="form-text text-danger">
                                        Category bersifat unik / tidak boleh sama dengan
                                        Category lainnya
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Tambah Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="updateCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="/updatecategory" method="post">
                @csrf
                @method('patch')
                <input type="hidden" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Category</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Id Category</label>
                                    <input type="text" class="form-control" name="id_cat" readonly>
                                    <small class="form-text text-danger">Id Category bersifat unik / tidak boleh sama
                                        dengan
                                        Id Category lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Category</label>
                                    <input type="text" class="form-control" name="category"
                                        placeholder="Masukkan Category">
                                    <small class="form-text text-danger">
                                        Category bersifat unik / tidak boleh sama dengan
                                        Category lainnya
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger" onclick="deleteCategory(this)">Hapus Category</button>
                        <button type="submit" class="btn btn-primary">Perbarui Category</button>
                    </div>
                </div>
            </form>
            <form action="/deletecategory" method="post" id="deleteform">
                @csrf
                @method('delete')
                <input type="hidden" name="id">
                <div class="inputfield">
                </div>
            </form>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#categoryDT').DataTable(datatable_settings);
            $('#categoryDT tbody').on('click', 'tr', function() {
                let modal = $('#updateCategoryModal');
                let data = $(this).data('category');
                modal.find('input[name="id_cat"]').val(data['id']);
                modal.find('input[name="category"]').val(data['category']);
                modal.modal('show');
            });
        });

        function deleteCategory(el) {
            let modal = $('#updateCategoryModal');
            let id_cat = modal.find('input[name="id_cat"]').val();
            let form = $('#deleteform');
            let inputfield = form.find('.inputfield');

            inputfield.empty();
            inputfield.append('<input type="hidden" name="id_cat" value="' + id_cat + '">');

            if (confirm('Category akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                form.submit();
            }
        }
    </script>
@endsection
