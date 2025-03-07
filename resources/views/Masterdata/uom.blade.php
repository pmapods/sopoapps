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
                    <h1 class="m-0 text-dark">Uom</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Uom</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUomModal"
                    id="addbutton">
                    Tambah Uom
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="uomDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>UOM</th>
                                <th>Create At</th> 
                                <th>Updated At</th>
                                <th>Deleted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($uom as $key => $uom)
                                <tr data-uom="{{ $uom }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $uom->uom }}</td>
                                    <td>{{ $uom->created_at }}</td>
                                    <td>{{ $uom->updated_at }}</td>
                                    <td>{{ $uom->deleted_at }}</td>
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

    <div class="modal fade" id="addUomModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="/adduom" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Uom</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Uom</label>
                                    <input type="text" class="form-control" name="uom"
                                        placeholder="Masukkan Uom" required>
                                    <small class="form-text text-danger">
                                        Uom bersifat unik / tidak boleh sama dengan
                                        Uom lainnya
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Tambah Uom</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="updateUomModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="/updateuom" method="post">
                @csrf
                @method('patch')
                <input type="hidden" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Uom</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Id</label>
                                    <input type="text" class="form-control" name="id_uom" readonly>
                                    <small class="form-text text-danger">Id bersifat unik / tidak boleh sama
                                        dengan
                                        id lainnya</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Uom</label>
                                    <input type="text" class="form-control" name="uom"
                                        placeholder="Masukkan Uom" readonly>
                                    <small class="form-text text-danger">
                                        Uom bersifat unik / tidak boleh sama dengan
                                        Uom lainnya
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger" onclick="deleteUom(this)">Hapus Uom</button>
                        <button type="submit" class="btn btn-primary">Perbarui Uom</button>
                    </div>
                </div>
            </form>
            <form action="/deleteuom" method="post" id="deleteform">
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
            var table = $('#uomDT').DataTable(datatable_settings);
            $('#uomDT tbody').on('click', 'tr', function() {
                let modal = $('#updateUomModal');
                let data = $(this).data('uom');
                modal.find('input[name="id_uom"]').val(data['id']);
                modal.find('input[name="uom"]').val(data['uom']);
                modal.modal('show');
            });
        });

        function deleteUom(el) {
            let modal = $('#updateUomModal');
            let id_uom = modal.find('input[name="id_uom"]').val();
            let form = $('#deleteform');
            let inputfield = form.find('.inputfield');

            inputfield.empty();
            inputfield.append('<input type="hidden" name="id_uom" value="' + id_uom + '">');

            if (confirm('Uom akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                form.submit();
            }
        }
    </script>
@endsection
