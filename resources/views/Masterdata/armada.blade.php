@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Armada</h1>
                    {{-- <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                    <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                </div> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Armada</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary mr-2" data-toggle="modal"
                    data-target="#addJenisKendaraanModal">
                    List Jenis Kendaraan
                </button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addArmadaModal">
                    Tambah Armada
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="armadaDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint</th>
                        <th>Jenis Kendaraan</th>
                        <th>Nomor Kendaaraan</th>
                        <th>Tahun Kendaaraan</th>
                        <th>Tipe Niaga</th>
                        <th>Status</th>
                        <th>Di Booking Oleh</th>
                </thead>
                <tbody>
                    @foreach ($armadas as $key => $armada)
                        <tr data-armada="{{ $armada }}">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @if ($armada->salespoint() != null)
                                    {{ $armada->salespoint()->name }}
                                @endif
                            </td>
                            <td>{{ $armada->armada_type->name }}</td>
                            <td class="text-uppercase">{{ $armada->plate }}</td>
                            <td>{{ $armada->vehicle_year ? \Carbon\Carbon::parse($armada->vehicle_year)->format('Y') : null }}
                            </td>
                            <td>{{ $armada->armada_type->isNiaga() }}</td>
                            <td>{{ $armada->status() }}</td>
                            <td>{{ $armada->status == 1 ? $armada->booked_by : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addArmadaModal" tabindex="-1" data-backdrop="static" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Armada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/addarmada" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="optional_field">SalesPoint</label>
                                    <select class="form-control select2 salespoint" name="salespoint_id">
                                        <option value="">-- Pilih SalesPoint --</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Pilih Jenis Kendaraan</label>
                                    <select class="form-control select2 armada_type_id" name="armada_type_id" required>
                                        <option data-niaga="" value="">-- Pilih Jenis Kendaraan --</option>
                                        @foreach ($armada_types as $type)
                                            <option data-niaga="{{ $type->isNiaga }}" value="{{ $type->id }}">
                                                {{ $type->brand_name }} {{ $type->name }}
                                                ({{ $type->isNiaga() }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Nopol Kendaraan</label>
                                    <input type="text" class="form-control" name="plate"
                                        placeholder="Masukkan Nopol Kendaraan" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="required_field">Tahun Kendaraan</label>
                                    <input type="number" class="form-control autonumber" min="1900"
                                        max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                        name="vehicle_year" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Status</label>
                                    <select class="form-control status" name="status" disabled>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="0">Available</option>
                                        <option value="1">Booked</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 booked_by_field">
                                <div class="form-group">
                                    <label class="required_field">Di Booked Oleh</label>
                                    <input type="text" class="form-control booked_by" name="booked_by"
                                        placeholder="Masukan Nama yang melakukan Booking">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Tambah Armada Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailArmadaModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detil Armada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/updatearmada" method="post" enctype="multipart/form-data" id="detailarmadaform">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="armada_id" class="armada_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="optional_field">SalesPoint</label>
                                    <select class="form-control select2 salespoint" name="salespoint_id">
                                        <option value="">-- Pilih SalesPoint --</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Pilih Jenis Kendaraan</label>
                                    <select class="form-control select2 armada_type_id" name="armada_type_id" required>
                                        <option data-niaga="" value="">-- Pilih Jenis Kendaraan --</option>
                                        @foreach ($armada_types as $type)
                                            <option data-niaga="{{ $type->isNiaga }}" value="{{ $type->id }}">
                                                {{ $type->brand_name }} {{ $type->name }}
                                                ({{ $type->isNiaga() }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Nopol Kendaraan</label>
                                    <input type="text" class="form-control" name="plate"
                                        placeholder="Masukkan Nopol Kendaraan" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="required_field">Tahun Kendaraan</label>
                                    <input type="number" class="form-control autonumber" min="1900"
                                        max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                        name="vehicle_year" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Status</label>
                                    <select class="form-control status" name="status" disabled>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="0">Available</option>
                                        <option value="1">Booked</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 booked_by_field">
                                <div class="form-group">
                                    <label class="required_field">Di Booked Oleh</label>
                                    <input type="text" class="form-control booked_by" name="booked_by"
                                        placeholder="Masukan Nama yang melakukan Booking">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-danger" id="armada_delete_button">Hapus Armada</button>
                        <button type="submit" class="btn btn-info">Update Armada</button>
                    </div>
                </form>
                <form action="/deletearmada" id="deletearmadaform" method="post">
                    @csrf
                    @method('delete')
                    <input type="hidden" name="armada_id" class="armada_id">
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addJenisKendaraanModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">List Jenis Kendaraan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jenis</th>
                                <th>Nama Brand</th>
                                <th>Niaga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($armada_types as $key => $armada_type)
                                <tr>
                                    <td scope="row">{{ $key + 1 }}</td>
                                    <td>
                                        {{ $armada_type->name }}
                                        @if ($armada_type->alias)
                                            <span>({{ $armada_type->alias }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $armada_type->brand_name }}</td>
                                    <td>{{ $armada_type->isNiaga() }}
                                        @if ($armada_type->isNiaga() == 'Non Niaga' && $armada_type->isSBH() == 'SBH')
                                            {{ '(' . $armada_type->isSBH() . ')' }}
                                        @endif
                                    </td>
                                    <td class="text-danger"><i class="fas fa-trash delete_icon"
                                            data-id="{{ $armada_type->id }}" aria-hidden="true"></i></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <form action="/deletearmadatype" method="post" id="deletearmadatypeform">
                        @csrf
                        <input type="hidden" name="armada_type_id" class="armada_type_id">
                    </form>
                    <form action="/addarmadatype" method="post" id="armadatypeform">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="required_field">Nama Jenis Kendaraan</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="required_field">Nama Merk</label>
                                    <input type="text" class="form-control" name="brand_name" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="optional_field">Nama Alias</label>
                                    <input type="text" class="form-control" name="alias">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="required_field">Pilih Jenis Niaga</label>
                                    <select class="form-control isNiaga" name="isNiaga" required>
                                        <option value="">-- Pilih Jenis Niaga --</option>
                                        <option value="0">Non Niaga</option>
                                        <option value="1">Niaga</option>
                                        <option value="2">Non Niaga-COP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary form-control">Tambah</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 isSBH" style="display:none">

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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

            var table = $('#armadaDT').DataTable(datatable_settings);
            $('#armadaDT tbody').on('click', 'tr', function() {
                let modal = $('#detailArmadaModal');
                let data = $(this).data('armada');
                modal.find('input[name="armada_id"]').val(data['id']);
                modal.find('select[name="salespoint_id"]').val((data['salespoint_id'] == null) ? '' : data[
                    'salespoint_id']);
                modal.find('select[name="salespoint_id"]').trigger('change');
                modal.find('select[name="armada_type_id"]').val(data['armada_type_id']);
                modal.find('select[name="armada_type_id"]').trigger('change');
                modal.find('input[name="plate"]').val(data['plate']);
                modal.find('select[name="status"]').val(data['status']);
                modal.find('select[name="status"]').trigger('change');
                modal.find('input[name="booked_by"]').val(data['booked_by']);
                modal.modal('show');
            });
            $('.status').on('change', function() {
                let modal = $(this).closest('.modal');
                let status = $(this).val();
                modal.find('.booked_by').val("");
                modal.find('.booked_by_field').hide();
                modal.find('.booked_by').prop('required', false);
                if (status == 1) {
                    modal.find('.booked_by_field').show();
                    modal.find('.booked_by').prop('required', true);
                }
            });
            $('.armada_type_id').on('change', function() {
                let modal = $(this).closest('.modal');
                let value = $(this).val();
                modal.find('.status').prop("disabled", true);
                modal.find('.status').val("");
                modal.find('.status').trigger('change');
                if (value != "") {
                    modal.find('.status').prop("disabled", false);
                }
            })
            $('#addArmadaModal').on('show.bs.modal', function() {
                $('#addArmadaModal form').trigger('reset');
                $('#addArmadaModal .salespoint').trigger('change');
                $('#addArmadaModal .status').trigger('change');
            });
            $('#armada_delete_button').click(function() {
                let form = $('#deletearmadaform');
                if (confirm('Jenis Kendaraan yang dihapus tidak dapat dikembalikan. Lanjutkan?')) {
                    form.submit();
                }
            });
            $('.delete_icon').click(function() {
                let id = $(this).data('id');
                let form = $('#deletearmadatypeform');
                form.find('.armada_type_id').val(id);
                form.submit();
            })

            $('.isNiaga').change(function() {
                let isNiaga = $(this).val();
                $('.isSBH').hide();
                $('.isSBH').empty();
                if (isNiaga == 0) {
                    $('.isSBH').show();
                    $(".isSBH").append(
                        `<div class="form-group">
                            <label class="required_field">Pilih Jenis SBH</label>
                            <select class="form-control" name="isSBH" required>
                                <option value="">-- Pilih Jenis SBH --</option>
                                <option value="0">Non SBH</option>
                                <option value="1">SBH</option>
                            </select>
                        </div>`);
                }
            })

            let menu = @json(Session::get('menu'));
            if (menu == 'armadatypelist') {
                $('#addJenisKendaraanModal').modal('show')
            }
        })
    </script>
@endsection
