@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Sales Point</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Sales Point</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSalesPoint">
                Tambah Point
            </button>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="table-responsive">
        <table id="salespointDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th width="5%">#</th>
                    <th width="7%">{{__('Kode')}}</th>
                    <th width="15%">{{__('Nama Area')}}</th>
                    <th width="10%">{{__('Region')}}</th>
                    <th width="8%">{{__('Status')}}</th>
                    <th width="8%">Jawa Sumatra</th>
                    <th width="20%">Alamat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salespoints as $key => $salespoint)
                <tr data-salespoint="{{$salespoint}}">
                    <td>{{$key+1}}</td>
                    <td>{{$salespoint->code}}</td>
                    <td>{{$salespoint->name}} (<span class="text-uppercase">{{$salespoint->initial}}</span>)</td>
                    <td>{{$salespoint->region_name()}} ({{ $salespoint->region_type }})</td>
                    <td>{{$salespoint->status_name()}}</td>
                    <td>{{$salespoint->jawasumatra()}}</td>
                    <td class="small">{{$salespoint->address}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- add modal --}}
<div class="modal fade" id="addSalesPoint" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Sales Point</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/addsalespoint" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Kode Sales Point</label>
                                <input type="text" class="form-control" name="code"
                                    placeholder="Masukkan Kode Sales Point" required>
                                <small class="form-text text-danger">* kode sales point bersifat unik / tidak bisa sama
                                    jika sudah terdaftar di sistem. kode tidak dapat diubah setelah didaftarkan</small>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Nama Area</label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan Nama Area" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Inisial Area</label>
                                <input type="text" class="form-control" 
                                    name="initial" maxlength="7" minlength="3"
                                    style="text-transform:uppercase"
                                    placeholder="Masukan initial area" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Pilih Region</label>
                                <select class="form-control select2" name="region" required>
                                    <option value="">-- Pilih Region --</option>
                                    <option value="0">MT CENTRAL 1</option>
                                    <option value="17">HO</option>
                                    <option value="1">SUMATERA 1</option>
                                    <option value="2">SUMATERA 2</option>
                                    <option value="3">SUMATERA 3</option>
                                    <option value="4">SUMATERA 4</option>
                                    <option value="5">BANTEN</option>
                                    <option value="6">DKI</option>
                                    <option value="7">JABAR 1</option>
                                    <option value="8">JABAR 2</option>
                                    <option value="9">JABAR 3</option>
                                    <option value="10">JATENG 1</option>
                                    <option value="11">JATENG 2</option>
                                    <option value="18">JATENG 3</option>
                                    <option value="12">JATIM 1</option>
                                    <option value="13">JATIM 2</option>
                                    <option value="14">BALINUSRA</option>
                                    <option value="15">KALIMANTAN</option>
                                    <option value="16">SULAWESI</option>
                                    <option value="19">INDIRECT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Status Point</label>
                                <select class="form-control" name="status" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="0">Depo</option>
                                    <option value="1">Cabang</option>
                                    <option value="2">Cellpoint</option>
                                    <option value="3">Subdist</option>
                                    <option value="4">Nasional</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Trade Type</label>
                                <select class="form-control" name="trade_type" required>
                                    <option value="">-- Pilih Tipe Trade --</option>
                                    <option value="0">General Trade</option>
                                    <option value="1">Modern Trade</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Apakah area Jawa Sumatra</label>
                                <select class="form-control" name="isJawaSumatra" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="1">Ya (Dalam Jawa Sumatra)</option>
                                    <option value="0">Tidak (Luar Jawa Sumatra)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <label for="optional_field">Alamat</label>
                              <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Tambah Sales Point</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- edit modal --}}
<div class="modal fade" id="editSalesPoint" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Sales Point</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/updatesalespoint" method="post">
                @csrf
                @method('patch')
                <input type="hidden" name="salespoint_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Kode Sales Point</label>
                                <input type="text" class="form-control" name="code"
                                    placeholder="Masukkan Kode Sales Point" readonly>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Nama Area</label>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan Nama Area"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Inisial Area</label>
                                <input type="text" class="form-control" 
                                    name="initial" maxlength="7" minlength="3"
                                    style="text-transform:uppercase"
                                    placeholder="Masukan initial area" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Pilih Region</label>
                                <select class="form-control select2" name="region" required>
                                    <option value="">-- Pilih Region --</option>
                                    <option value="0">MT CENTRAL 1</option>
                                    <option value="17">HO</option>
                                    <option value="1">SUMATERA 1</option>
                                    <option value="2">SUMATERA 2</option>
                                    <option value="3">SUMATERA 3</option>
                                    <option value="4">SUMATERA 4</option>
                                    <option value="5">BANTEN</option>
                                    <option value="6">DKI</option>
                                    <option value="7">JABAR 1</option>
                                    <option value="8">JABAR 2</option>
                                    <option value="9">JABAR 3</option>
                                    <option value="10">JATENG 1</option>
                                    <option value="11">JATENG 2</option>
                                    <option value="18">JATENG 3</option>
                                    <option value="12">JATIM 1</option>
                                    <option value="13">JATIM 2</option>
                                    <option value="14">BALINUSRA</option>
                                    <option value="15">KALIMANTAN</option>
                                    <option value="16">SULAWESI</option>
                                    <option value="19">INDIRECT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Status Point</label>
                                <select class="form-control" name="status" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="0">Depo</option>
                                    <option value="1">Cabang</option>
                                    <option value="2">Cellpoint</option>
                                    <option value="3">Subdist</option>
                                    <option value="4">Nasional</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Trade Type</label>
                                <select class="form-control" name="trade_type" required>
                                    <option value="">-- Pilih Tipe Trade --</option>
                                    <option value="0">General Trade</option>
                                    <option value="1">Modern Trade</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required_field">Apakah area Jawa Sumatra</label>
                                <select class="form-control" name="isJawaSumatra" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="1">Ya (Dalam Jawa Sumatra)</option>
                                    <option value="0">Tidak (Luar Jawa Sumatra)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <label for="optional_field">Alamat</label>
                              <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" onclick="deletesalespoint()">Hapus</button>
                    <button type="submit" class="btn btn-info">Update Sales Point</button>
                </div>
            </form>
            <form action="/deletesalespoint" method="post" id="deleteform">
                @csrf
                @method('delete')
                <input type="hidden" name="salespoint_id">
            </form>
        </div>
    </div>
</div>

@endsection
@section('local-js')
<script>
    $(document).ready(function () {
        var table = $('#salespointDT').DataTable(datatable_settings);
        $('#salespointDT tbody').on('click', 'tr', function () {
            let modal = $('#editSalesPoint');
            let data = $(this).data('salespoint');
            modal.find('input[name="salespoint_id"]').val(data['id']);
            modal.find('input[name="code"]').val(data['code']);
            modal.find('input[name="name"]').val(data['name']);
            modal.find('input[name="initial"]').val(data['initial']);
            modal.find('select[name="region"]').val(data['region']);
            modal.find('select[name="region"]').trigger('change');
            modal.find('select[name="status"]').val(data['status']);
            modal.find('select[name="trade_type"]').val(data['trade_type']);
            modal.find('select[name="isJawaSumatra"]').val(data['isJawaSumatra']);
            modal.find('textarea[name="address"]').val(data['address']);
            modal.modal('show');
        });
    })
    
    function deletesalespoint(){
        if (confirm('Menghapus sales point tidak menghilangkan transaksi terkait yang sedang berlangsung. Lanjutkan ?')) {
            $('#deleteform').submit();
        }
    }
</script>
@endsection
