@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Jabatan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Jabatan</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPositionModal">
                Tambah Jabatan
            </button>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="table-responsive">
        <table id="employeeposDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th width="5%">
                        #
                    </th>
                    <th>
                        {{__('Nama Jabatan')}}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($positions as $key=>$position)
                    <tr data-position="{{$position}}">
                        <td>{{$key+1}}</td>
                        <td>{{$position->name}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Tambah Jabatan Modal -->
<div class="modal fade" id="addPositionModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/addPosition" method="post">
        {{csrf_field()}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jabatan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                          <label class="required_field">Nama Jabatan</label>
                          <input type="text" class="form-control" name="name" aria-describedby="helpId" placeholder="Masukkan nama Jabatan" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
        </form>
    </div>
</div>

{{-- Detail Jabatan Modal --}}
<div class="modal fade" id="detailPositionModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/updatePosition" method="post">
            @csrf
            @method('PATCH')
        <input type="hidden" name="position_id">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detil Jabatan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                          <label class="required_field">Nama Jabatan</label>
                          <input type="text" class="form-control" name="name" aria-describedby="helpId" placeholder="Masukkan nama Jabatan" required>
                        </div>
                    </div>
                </div>
                <small class="text-danger">*penghapusan jabatan hanya dapat dilakukan setelah tidak ada karyawan yang terkait dengan jabatan bersangkutan</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger delete_button" onclick="deletePosition()">Hapus</button>
                <button type="submit" class="btn btn-info">Update</button>
            </div>
        </div>
        </form>
        <form action="/deletePosition" method="post" id="deleteform">
            @csrf
            @method('DELETE')
            <input type="hidden" name="position_id">
        </form>
    </div>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var table = $('#employeeposDT').DataTable(datatable_settings);
        $('#employeeposDT tbody').on('click', 'tr', function () {
            let updatemodal = $('#detailPositionModal');
            let data = $(this).data('position');
            updatemodal.find('input[name="position_id"]').val(data['id']);
            updatemodal.find('input[name="name"]').val(data['name']);
            $('#detailPositionModal').modal('show');
        });
    })
    function deletePosition(){
        if (confirm('Jabatan akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
            $('#deleteform').submit();
        } else {
        }
    }
</script>
@endsection
