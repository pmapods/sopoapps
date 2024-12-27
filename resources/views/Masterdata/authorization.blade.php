@extends('Layout.app')
@section('local-css')
    <style>
        .remove_list {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex flex-column">
                    <h1 class="m-0 text-dark">Matriks Approval</h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Matriks Approval</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#multiReplaceModal">
                    Multi Replace
                </button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAuthorModal">
                    Tambah Matriks Approval Baru
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="authorDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr>
                        <th>SalesPoint</th>
                        <th>Region</th>
                        <th>Daftar Approver</th>
                        <th>Jenis Form</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addAuthorModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Matriks Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="required_field">SalesPoint</label>
                                <select class="form-control select2 salespoint_select2" name="salespoint">
                                    <option value="">-- Pilih SalesPoint --</option>
                                    <optgroup label="Multiple Salespoint">
                                        <option value="all">All</option>
                                        <option value="west">West</option>
                                        <option value="east">East</option>
                                        <option value="indirect">Indirect</option>
                                    </optgroup>
                                    @foreach ($regions as $region)
                                        <optgroup label="{{ $region->first()->region_name() }}">
                                            @foreach ($region as $salespoint)
                                                <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-center pt-3">
                            <span class="spinner-border text-danger loading_salespoint_select2" role="status"
                                style="display:none">
                                <span class="sr-only">Loading...</span>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Jenis Form</label>
                                <select class="form-control form_type" name="form_type" required>
                                    <option value="">-- Pilih Jenis Form --</option>
                                    <option value="0">Pengadaan Barang Jasa</option>
                                    <option value="7">Pengadaan Armada</option>
                                    <option value="8">Pengadaan Security</option>
                                    <option value="1">Form Bidding</option>
                                    <option value="4">Form Fasilitas</option>
                                    <option value="5">Form Mutasi</option>
                                    <option value="9">Form Evaluasi Security</option>
                                    <option value="6">Perpanjangan / Perhentian</option>
                                    <option value="2">PR</option>
                                    <option value="3">PO</option>
                                    <option value="10">Upload Budget (baru)</option>
                                    <option value="11">Upload Budget (revisi)</option>
                                    <option value="12">FRI (Form Request Infrastruktur)</option>
                                    <option value="13">Form Evaluasi Vendor</option>
                                    <option value="14">Form Over Budget (Area)</option>
                                    <option value="15">Form Over Budget (HO)</option>
                                    <option value="16">Form Peremajaan Armada</option>
                                    <option value="17">Cancel End Kontrak (Pest Control, Armada, Security)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 notes_field basic_notes note_field1">
                            <div class="form-group">
                                <label class="optional_field">Notes</label>
                                <input class="form-control" name="notes">
                            </div>
                        </div>
                        <div class="col-md-12 notes_field basic_notes note_field2" style="display: none">
                            <div class="form-group">
                                <label class="required_field">Notes</label>
                                <select class="form-control notes_select" name="notes_select" required>
                                    <option value="">-- Pilih Notes --</option>
                                    <option value="Pengadaan Security">Pengadaan Security</option>
                                    <option value="Pengadaan Lembur">Pengadaan Lembur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 notes_field niaga_notes d-none">
                            <div class="form-group">
                                <label class="required_field">Notes</label>
                                <select class="form-control" name="notes">
                                    <option value="Niaga">Niaga</option>
                                    <option value="Non-Niaga">Non-Niaga</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 notes_field budget_notes d-none">
                            <div class="form-group">
                                <label class="required_field">Notes</label>
                                <select class="form-control" name="notes">
                                    <option value="Budget">Budget</option>
                                    <option value="Non-Budget">Non-Budget</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5>Otorisasi Default (otomatis)</h5>
                            <table class="table table-bordered table_default_level">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Sebagai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <h5>Otorisasi Pilihan</h5>
                            <table class="table table-bordered table_level">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Sebagai</th>
                                        <th>Level</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="empty_row text-center">
                                        <td colspan="5">Otorasi belum dipilih</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pilih Karyawan</label>
                                <select class="form-control select2 employee_select2" name="employee_id" disabled>
                                    <option value="" class="initial-select">--Pilih Karyawan --</option>
                                </select>
                                <small class="text-danger">* Daftar karyawan yang muncul sesuai matriks approval area yang
                                    didaftarkan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Jabatan</label>
                                <select class="form-control select2 position_select2 position_text">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sebagai</label>
                                <select class="form-control as_text" disabled>
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp</label>
                                <button type="button"
                                    class="btn btn-info form-control if_edit_disable add_new_level">Tambah</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary if_edit_disable" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary if_edit_disable" onclick="addAuthorization()">Tambah
                        Matriks Approval</button>
                </div>
            </div>
        </div>
        <form action="/addauthorization" method="post" id="#addform">
            @csrf
            <div class="inputfield">
            </div>
        </form>
    </div>

    <div class="modal fade" id="detailAuthorModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Matriks Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="required_field">SalesPoint</label>
                                <select class="form-control select2 salespoint_select2" name="salespoint" disabled>
                                    <option value="">-- Pilih SalesPoint --</option>
                                    <optgroup label="Multiple Salespoint">
                                        <option value="all">All</option>
                                        <option value="west">West</option>
                                        <option value="east">East</option>
                                        <option value="indirect">Indirect</option>
                                    </optgroup>
                                    @foreach ($regions as $region)
                                        <optgroup label="{{ $region->first()->region_name() }}">
                                            @foreach ($region as $salespoint)
                                                <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-center pt-3">
                            <span class="spinner-border text-danger loading_salespoint_select2" role="status"
                                style="display:none">
                                <span class="sr-only">Loading...</span>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required_field">Jenis Form</label>
                                <select class="form-control form_type" name="form_type" required disabled>
                                    <option value="">-- Pilih Jenis Form --</option>
                                    <option value="0">Pengadaan Barang Jasa</option>
                                    <option value="7">Pengadaan Armada</option>
                                    <option value="8">Pengadaan Security</option>
                                    <option value="1">Form Bidding</option>
                                    <option value="4">Form Fasilitas</option>
                                    <option value="5">Form Mutasi</option>
                                    <option value="9">Form Evaluasi Security</option>
                                    <option value="6">Perpanjangan / Perhentian</option>
                                    <option value="2">PR</option>
                                    <option value="3">PO</option>
                                    <option value="10">Upload Budget (baru)</option>
                                    <option value="11">Upload Budget (revisi)</option>
                                    <option value="12">FRI (Form Request Infrastruktur)</option>
                                    <option value="13">Form Evaluasi Vendor</option>
                                    <option value="14">Form Over Budget (Area)</option>
                                    <option value="15">Form Over Budget (HO)</option>
                                    <option value="16">Form Peremajaan Armada</option>
                                    <option value="17">Cancel End Kontrak (Pest Control, Armada, Security)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 notes_field basic_notes">
                            <div class="form-group">
                                <label class="optional_field">Notes</label>
                                <input class="form-control" name="notes">
                            </div>
                        </div>
                        <div class="col-md-12 notes_field notes_select d-none">
                            <div class="form-group">
                                <label class="required_field">Notes</label>
                                <select class="form-control" name="notes">
                                    <option value="Pengadaan Security">Pengadaan Security</option>
                                    <option value="Pengadaan Lembur">Pengadaan Lembur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 notes_field niaga_notes d-none">
                            <div class="form-group">
                                <label class="required_field">Notes</label>
                                <select class="form-control" name="notes">
                                    <option value="Niaga">Niaga</option>
                                    <option value="Non-Niaga">Non-Niaga</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 notes_field budget_notes d-none">
                            <div class="form-group">
                                <label class="required_field">Notes</label>
                                <select class="form-control" name="notes">
                                    <option value="Budget">Budget</option>
                                    <option value="Non-Budget">Non-Budget</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5>Otorisasi Default (otomatis)</h5>
                            <table class="table table-bordered table_default_level">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Sebagai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <h5>Otorisasi pilihan</h5>
                            <table class="table table-bordered table_level">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Sebagai</th>
                                        <th>Level</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pilih Karyawan</label>
                                <select class="form-control select2 employee_select2" name="employee_id" disabled>
                                    <option value="" class="initial-select">--Pilih Karyawan --</option>
                                </select>
                                <small class="text-danger">* Daftar karyawan yang muncul sesuai matriks approval area yang
                                    didaftarkan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Jabatan</label>
                                <select class="form-control select2 position_select2 position_text">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sebagai</label>
                                <select class="form-control as_text" disabled>
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp</label>
                                <button type="button"
                                    class="btn btn-info form-control add_new_level if_edit_disable">Tambah</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary if_edit_disable" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger if_edit_disable"
                        onclick="deleteAuthorization()">Hapus</button>
                    <button type="button" class="btn btn-primary if_edit_disable" onclick="updateAuthorization()">Update
                        Matriks Approval</button>
                </div>
            </div>
        </div>
        <form action="/updateauthorization" method="post" id="updateform">
            @csrf
            @method('patch')
            <input type="hidden" name="authorization_id">
            <div class="inputfield">
            </div>
        </form>

        <form action="/deleteauthorization" method="post" id="deleteform">
            @csrf
            @method('delete')
            <input type="hidden" name="authorization_id">
        </form>

    </div>

    <div class="modal fade" id="multiReplaceModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Multi Replace</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/authorization/multireplace" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Pilih Salespoint</label>
                                    <select class="form-control select2" name="salespoint_id" required>
                                        <option value="">-- Pilih Salespoint --</option>
                                        @foreach ($regions as $region)
                                            <optgroup label="{{ $region->first()->region_name() }}">
                                                @foreach ($region as $salespoint)
                                                    <option value="{{ $salespoint->id }}">{{ $salespoint->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Pilih Jabatan</label>
                                    <select class="form-control select2" name="position_id" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Jenis Form</th>
                                            <th>Daftar Otorisasi</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Diubah menjadi Karyawan</label>
                                    <select class="form-control select2" name="to_employee_id" required>
                                        <option value="">-- Pilih Karyawan --</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        let niaga_notes_array = [5, 6];
        let budget_notes_array = [2];
        let note_select_array = [8];

        let formpengadaan = ['Pengaju', 'Atasan Langsung', 'Atasan Tidak Langsung'];
        let formbidding = ['Diajukan Oleh', 'Diperiksa Oleh', 'Disetujui Oleh'];
        let formpr = ['Dibuat Oleh', 'Diperiksa Oleh', 'Disetujui Oleh'];
        let formpo = ['Dibuat Oleh', 'Diperiksa dan disetujui oleh'];
        let formfasilitas = ['Pemohon', 'Menyetujui'];
        let formmutasi = ['Dibuat Oleh', 'Diperiksa Oleh', 'Disetujui Oleh'];
        let formperpanjangan = ['Yang Mengajukan', 'Diketahui Oleh', 'Disetujui'];
        let formpengadaanarmada = ['Pengaju', 'Atasan Langsung', 'Atasan Tidak Langsung'];
        let formpengadaansecurity = ['Pengaju', 'Atasan Langsung', 'Atasan Tidak Langsung', 'Dibuat Oleh', 'Diperiksa Oleh',
            'Disetujui Oleh'
        ];
        let formevaluasi = ['Disiapkan Oleh', 'Diperiksa Oleh', 'Disetujui Oleh'];
        let uploadbudget = ['Dibuat Oleh', 'Diketahui Oleh', 'Disetujui Oleh'];
        let formfri = ["Diketahui Oleh", "Diinput Oleh"];
        let formevaluasivendor = ["Menilai", "Mengetahui"];
        let formoverbudgetarea = ['Diperiksa Oleh', 'Disetujui Oleh', 'Disetujui Oleh'];
        let formoverbudgetho = ['Disetujui Oleh', 'Disetujui Oleh'];
        let formperemajaanarmada = ['Disetujui Oleh'];
        let formcancelendkontrak = ['Diperiksa Oleh', 'Disetujui Oleh', 'Disetujui Oleh', 'Disetujui Oleh'];

        $(document).ready(function() {
            // var table = $('#authorDT').DataTable(datatable_settings);
            var table = $('#authorDT').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 5000,
                "ajax": "/authorization/data",
                "createdRow": function(row, data, dataIndex) {
                    $(row).data('authorization', data[5]);
                    $(row).data('list', JSON.parse(data[6]));
                }
            });
            $("#authorDT_filter input").unbind();
            $("#authorDT_filter input").keyup(function(e) {
                if (e.keyCode == 13) {
                    table.search(this.value).draw();
                }
            });
            $('#authorDT tbody').on('click', 'tr', function() {
                let modal = $('#detailAuthorModal');
                let data = $(this).data('authorization');
                let list = $(this).data('list');
                let salespoint = modal.find('.salespoint_select2');
                let employee_select = modal.find('.employee_select2');
                let position_select = modal.find('.position_select2');
                let form_type = modal.find('select[name="form_type"]');
                form_type.val(data['form_type']);
                form_type.trigger('change');

                let notes = modal.find('.basic_notes input');
                modal.find('.notes_field').addClass('d-none');
                if (niaga_notes_array.includes(parseInt(data['form_type']))) {
                    // case perpanjangan/mutasi
                    notes = modal.find('.niaga_notes select');
                    modal.find('.niaga_notes').removeClass('d-none');
                } else if (budget_notes_array.includes(parseInt(data['form_type']))) {
                    // case PR
                    notes = modal.find('.budget_notes select');
                    modal.find('.budget_notes').removeClass('d-none');
                } else if (note_select_array.includes(parseInt(data['form_type']))) {
                    // case pengadaan security/pengadaan lembur
                    notes = modal.find('.notes_select select');
                    modal.find('.notes_select').removeClass('d-none');
                } else {
                    modal.find('.basic_notes').removeClass('d-none');
                }
                let table_level = modal.find('.table_level');
                modal.find('input[name="authorization_id"]').val(data.id);

                salespoint.val(data['salespoint_id']);
                salespoint.trigger('change');
                notes.val(data['notes']);

                table_level.find('tbody').empty();
                list.forEach((item, index) => {
                    let append_text = '<tr data-id="' + item.id + '" data-as="' + item.as_text +
                        '" data-position="' + item.position_id + '"><td>' + item.name +
                        '</td><td>' + item.position + '</td><td>' + item.as_text +
                        '</td><td class="level"></td>';
                    append_text += '<td>';
                    append_text +=
                        '<i class="fa fa-trash text-danger remove_list" onclick="removeList(this)" aria-hidden="true"></i>';
                    append_text +=
                        '<i class="fa fa-pen ml-2 text-info edit_list" onclick="editList(this)" aria-hidden="true"></i></i>';
                    append_text += '</td>';
                    table_level.find('tbody').append(append_text);
                })
                tableRefreshed(table_level);
                modal.modal('show');

            });
            $('.form_type').on('change', function() {
                let closestmodal = $(this).closest('.modal');
                let as_text = closestmodal.find('.as_text');
                as_text.prop('disabled', true);
                as_text.find('option').remove();
                as_text.append('<option value="">-- Pilih --</option>');
                let value_array = [];
                let default_array = [];
                closestmodal.find('.table_default_level tbody').empty();

                $('.notes_select').val("");
                $('.notes_select').trigger('change');

                $('.note_field2').css('display', 'none');
                $('.note_field1').css('display', 'block');

                switch ($(this).val()) {
                    case "0":
                        value_array = formpengadaan;
                        break;
                    case "1":
                        value_array = formbidding;
                        break;
                    case "2":
                        value_array = formpr;
                        default_array = [{
                                "nama": "Diisi oleh otorisasi kedua dari tiket",
                                "jabatan": "User (Min Gol 5A)",
                                "sebagai": "Dibuat Oleh"
                            },
                            {
                                "nama": "Diisi oleh otorisasi ketiga dari tiket",
                                "jabatan": "Atasan Berikutnya",
                                "sebagai": "Diperiksa Oleh"
                            }
                        ];
                        break;
                    case "3":
                        value_array = formpo;
                        default_array = [{
                            "nama": "Diisi saat pembuatan PO",
                            "jabatan": "Supplier PIC",
                            "sebagai": "Konfirmasi Supplier"
                        }];
                        break;
                    case "4":
                        value_array = formfasilitas;
                        break;
                    case "5":
                        value_array = formmutasi;
                        break;
                    case "6":
                        value_array = formperpanjangan;
                        break;
                    case "7":
                        value_array = formpengadaanarmada;
                        break;
                    case "8":
                        value_array = formpengadaansecurity;
                        $('.note_field2').css('display', 'block');
                        $('.note_field1').css('display', 'none');
                        $('.notes_select').prop('required', true);
                        break;
                    case "9":
                        value_array = formevaluasi;
                        break;
                    case "10":
                        value_array = uploadbudget;
                        break;
                    case "11":
                        value_array = uploadbudget;
                        break;
                    case "12":
                        value_array = formfri;
                        break;
                    case "13":
                        value_array = formevaluasivendor;
                        break;
                    case "14":
                        value_array = formoverbudgetarea;
                        break;
                    case "15":
                        value_array = formoverbudgetho;
                        break;
                    case "16":
                        value_array = formperemajaanarmada;
                        break;
                    case "17":
                        value_array = formcancelendkontrak;
                        break;
                    default:
                        return;
                        break;
                }
                value_array.forEach(item => {
                    as_text.append('<option value="' + item + '">' + item + '</option>');
                });
                default_array.forEach(item => {
                    closestmodal.find('.table_default_level tbody').append('<tr><td>' + item.nama +
                        '</td><td>' + item.jabatan + '</td><td>' + item.sebagai + '</td></tr>');
                });
                if (default_array.length == 0) {
                    closestmodal.find('.table_default_level tbody').append(
                        '<tr><td colspan="3" class="text-center">Tidak ada</td></tr>');
                }
                as_text.prop('disabled', false);

                closestmodal.find('.notes_field').addClass('d-none');
                if (niaga_notes_array.includes(parseInt($(this).val()))) {
                    // CASE FORM PERPANJANGAN/MUTASI
                    closestmodal.find('.niaga_notes').removeClass('d-none');
                } else if (budget_notes_array.includes(parseInt($(this).val()))) {
                    // case PR
                    closestmodal.find('.budget_notes').removeClass('d-none');
                } else {
                    closestmodal.find('.basic_notes').removeClass('d-none');
                }
            });
            $('.salespoint_select2').on('change', function() {
                let closestmodal = $(this).closest('.modal');
                let salespoint_id = $(this).find('option:selected').val();
                let employee_select = closestmodal.find('.employee_select2');
                let table_level = closestmodal.find('.table_level');
                let loading = closestmodal.find('.loading_salespoint_select2');

                // initial state
                employee_select.prop('disabled', true);
                employee_select.find('option').remove();
                var empty = new Option('-- Pilih Karyawan --', "", false, true);
                employee_select.append(empty);
                employee_select.trigger('change');

                if (salespoint_id == "") {
                    return;
                }
                loading.show();
                $.ajax({
                    type: "get",
                    url: "/getauthorizedemployeebysalesPoint/" + salespoint_id,
                    success: function(response) {
                        let selected_id = []
                        table_level.find('tbody tr').not('.empty_row').each((index, el) => {
                            let id = $(el).data('id');
                            selected_id.push(id);
                        });
                        let data = response.data;
                        employee_select.prop('disabled', false);
                        data.forEach(single_data => {
                            let option_text = single_data.name;
                            var newOption = new Option(option_text, single_data.id,
                                false, true);
                            employee_select.append(newOption);
                            // validasi gabisa pic yang sama dalam satu urutan otorisasi
                            // if (selected_id.includes(single_data.id)) {
                            //     employee_select.find('option:selected').prop('disabled', true);
                            // }
                        })
                        employee_select.val("");
                        employee_select.trigger('change');
                        loading.hide();

                    },
                    error: function(response) {
                        alert("error");
                        loading.hide();
                    }
                });
            })
            $('.add_new_level').on('click', function() {
                let closestmodal = $(this).closest('.modal');
                let employee_select = closestmodal.find('.employee_select2');
                let position_select = closestmodal.find('.position_select2');
                let as_text = closestmodal.find('.as_text');
                let table_level = closestmodal.find('.table_level');


                // check if all required field were selected
                if (employee_select.val() == "" || as_text.val() == "" || position_select.val() == "") {
                    alert('"Karyawan", "Jabatan" dan pilihan "Sebagai" harus dipilih');
                } else {
                    let id = employee_select.val();
                    let name = employee_select.find('option:selected').text().trim();
                    let position_id = position_select.val();
                    let position = position_select.find('option:selected').text().trim();

                    table_level.find('tbody').append('<tr data-id="' + id + '" data-as="' + as_text.val() +
                        '" data-position="' + position_id + '"><td>' + name + '</td><td>' + position +
                        '</td><td>' + as_text.val() +
                        '</td><td class="level"></td><td><i class="fa fa-trash text-danger remove_list" onclick="removeList(this)" aria-hidden="true"></i><i class="fa fa-pen ml-2 text-info edit_list" onclick="editList(this)" aria-hidden="true"></i></td></tr>'
                    );

                    // validasi gabisa pic yang sama dalam satu urutan otorisasi
                    // employee_select.find('option:selected').prop('disabled', true);
                    employee_select.val('');
                    employee_select.trigger('change');
                    position_select.val('');
                    position_select.trigger('change');
                    as_text.val('');
                    tableRefreshed($(this));
                }
            });

            $('#multiReplaceModal select[name="salespoint_id"], #multiReplaceModal select[name="position_id"]')
                .change(function() {
                    const salespoint_id = $('#multiReplaceModal select[name="salespoint_id"]').val();
                    const position_id = $('#multiReplaceModal select[name="position_id"]').val();
                    $('#multiReplaceModal table tbody').empty();
                    if (salespoint_id != "" && position_id != "") {
                        $.ajax({
                            type: "GET",
                            url: "/authorization/getdetails?salespoint_id=" + salespoint_id +
                                "&position_id=" + position_id,
                            success: function(response) {
                                if (!response.error) {
                                    let data = response.data;
                                    data.forEach(item => {
                                        let string_text = "<tr>";
                                        string_text += "<td>" + item.form_type_name +
                                            "</td>";
                                        string_text += "<td>";
                                        item.author_list.forEach((name, idx, arr) => {
                                            if (item.employee_name == name) {
                                                string_text += "<b>" + name +
                                                    "</b>";
                                            } else {
                                                string_text += name;
                                            }
                                            if (idx !== arr.length - 1) {
                                                string_text += ", ";
                                            }
                                        });
                                        string_text += "</td>";
                                        string_text += "<td>" + item.authorization_notes +
                                            "</td>";
                                        string_text += "</tr>";
                                        $('#multiReplaceModal table tbody').append(
                                            string_text);
                                    });
                                }
                            },
                            error: function(err) {
                                alert("Error get data : " + err.message);
                            }
                        });
                    }
                });
        });
        // remove button
        function removeList(el) {
            let closestmodal = $(el).closest('.modal');
            let table = closestmodal.find('table');
            let employee_select = closestmodal.find('.employee_select2');
            let tr = $(el).closest('tr');
            let employee_id = tr.data('id');
            employee_select.val(employee_id);
            // validasi gabisa pic yang sama dalam satu urutan otorisasi
            // employee_select.find('option:selected').prop('disabled', false);
            employee_select.val("");
            employee_select.trigger('change');
            tr.remove();
            tableRefreshed(table);
        }
        let old_employee_id = null;
        let old_employee_as = null;
        let old_employee_position = null;

        function editList(el) {
            let closestmodal = $(el).closest('.modal');
            let table = closestmodal.find('table');
            let employee_select = closestmodal.find('.employee_select2');
            let position_select = closestmodal.find('.position_select2');
            let as_select = closestmodal.find('.as_text');
            let tr = $(el).closest('tr');
            let employee_id = tr.data('id');
            let employee_as = tr.data('as');
            let employee_position = tr.data('position');
            old_employee_id = employee_id;
            old_employee_as = employee_as;
            old_employee_position = employee_position;
            employee_select.val(employee_id).trigger('change');
            position_select.val(old_employee_position).trigger('change');
            as_select.val(old_employee_as).trigger('change');

            table.find('.remove_list,.edit_list').addClass('d-none')
            closestmodal.find('.if_edit_disable').prop('disabled', true);
            let append_edit =
                '<i class="fa fa-check text-success save_edit cursor-pointer" onclick="saveEdit(this)" aria-hidden="true"></i>';
            append_edit +=
                '<i class="fa fa-times ml-2 text-danger cancel_edit cursor-pointer" onclick="cancelEdit(this)" aria-hidden="true"></i>';
            tr.find('td:eq(4)').append(append_edit);
        }

        function saveEdit(el) {
            let closestmodal = $(el).closest('.modal');
            let table = closestmodal.find('table');
            let employee_select = closestmodal.find('.employee_select2');
            let position_select = closestmodal.find('.position_select2');
            let as_select = closestmodal.find('.as_text');
            let tr = $(el).closest('tr');

            if (employee_select.val() == null || employee_select.val() == '') {
                alert('Karyawan belum dipilih');
                return;
            }
            if (position_select.val() == null || position_select.val() == '') {
                alert('Jabatan belum dipilih');
                return;
            }
            if (as_select.val() == null || as_select.val() == '') {
                alert('Sebagai belum dipilih');
                return;
            }

            tr.data('id', employee_select.val());
            tr.data('position', position_select.val());
            tr.data('as', as_select.val());
            tr.find('td:eq(0)').text(employee_select.find('option:selected').text());
            tr.find('td:eq(1)').text(position_select.find('option:selected').text());
            tr.find('td:eq(2)').text(as_select.find('option:selected').text());
            employee_select.val('').trigger('change');
            position_select.val('').trigger('change');
            as_select.val('').trigger('change');

            table.find('.remove_list,.edit_list').removeClass('d-none')
            table.find('.save_edit,.cancel_edit').remove()
            closestmodal.find('.if_edit_disable').prop('disabled', false);
        }

        function cancelEdit(el) {
            let closestmodal = $(el).closest('.modal');
            let table = closestmodal.find('table');
            let employee_select = closestmodal.find('.employee_select2');
            let position_select = closestmodal.find('.position_select2');
            let as_select = closestmodal.find('.as_text');
            employee_select.val('').trigger('change');
            position_select.val('').trigger('change');
            as_select.val('').trigger('change');
            table.find('.remove_list,.edit_list').removeClass('d-none')
            table.find('.save_edit,.cancel_edit').remove()
            closestmodal.find('.if_edit_disable').prop('disabled', false);
        }
        // table on refresh
        function tableRefreshed(current_element) {
            let closestmodal = $(current_element).closest('.modal');
            let table_level = closestmodal.find('.table_level');
            let salespoint_select = closestmodal.find('.salespoint_select2');
            let form_type = closestmodal.find('.form_type');
            // check table level if table has data / tr or not
            let row_count = 0;
            table_level.find('tbody tr').not('.empty_row').each(function() {
                row_count++;
            });
            if (row_count > 0) {
                salespoint_select.prop('disabled', true);
                form_type.prop('disabled', true);
                table_level.find('.empty_row').remove();
                table_level.find('.level').each(function(index, el) {
                    $(el).text(index + 1);
                });
            } else {
                salespoint_select.prop('disabled', false);
                form_type.prop('disabled', false);
                table_level.append('<tr class="empty_row text-center"><td colspan="5">Otorasi belum dipilih</td></tr>');
            }
        }

        function addAuthorization() {
            let modal = $('#addAuthorModal');
            let salespoint = modal.find('select[name="salespoint"]').val();
            let form_type = modal.find('select[name="form_type"]').val();
            let notes_select = modal.find('select[name="notes_select"]').val();
            let notes = modal.find('.basic_notes input').val();
            // case perpanjangan/mutasi
            if (niaga_notes_array.includes(parseInt(form_type))) {
                notes = modal.find('.niaga_notes select').val();
            }
            // case PR
            if (budget_notes_array.includes(parseInt(form_type))) {
                notes = modal.find('.budget_notes select').val();
            }
            let table_level = modal.find('.table_level');
            let authorizationlist = [];
            let list_count = 0;
            if (salespoint == "") {
                alert('Harap memilih salespoint');
                return;
            }
            if (form_type == "") {
                alert('Harap memilih jenis form');
                return;
            }
            table_level.find('tbody tr').not('.empty_row').each(function(index, el) {
                list_count++;
                let id = $(el).data('id');
                let as = $(el).data('as');
                let position = $(el).data('position');
                let level = parseInt($(el).find('.level').text().trim());
                authorizationlist.push({
                    "id": id,
                    "as": as,
                    "position": position,
                    "level": level
                })
            });
            if (list_count < 1) {
                alert('Minimal 1 otorisasi dipilih');
                return;
            }
            // form filling
            let form = $('#addAuthorModal').find('form');
            let inputfield = form.find('.inputfield');
            inputfield.empty();
            inputfield.append('<input type="hidden" name="salespoint" value="' + salespoint + '">');
            inputfield.append('<input type="hidden" name="form_type" value="' + form_type + '">');
            inputfield.append('<input type="hidden" name="notes" value="' + notes + '">');
            inputfield.append('<input type="hidden" name="notes_select" value="' + notes_select + '">');
            authorizationlist.forEach((item, index) => {
                inputfield.append('<input type="hidden" name="authorization[' + index + '][id]" value="' + item.id +
                    '">');
                inputfield.append('<input type="hidden" name="authorization[' + index + '][position]" value="' +
                    item.position + '">');
                inputfield.append('<input type="hidden" name="authorization[' + index + '][as]" value="' + item.as +
                    '">');
                inputfield.append('<input type="hidden" name="authorization[' + index + '][level]" value="' + item
                    .level + '">');
            });
            form.submit();
        }

        function updateAuthorization() {
            let modal = $('#detailAuthorModal');
            let salespoint = modal.find('select[name="salespoint"]').val();
            let form_type = modal.find('select[name="form_type"]').val();
            let notes = modal.find('.basic_notes input').val();
            // case perpanjangan/mutasi
            if (niaga_notes_array.includes(parseInt(form_type))) {
                notes = modal.find('.niaga_notes select').val();
            }
            // case PR
            if (budget_notes_array.includes(parseInt(form_type))) {
                notes = modal.find('.budget_notes select').val();
            }
            let table_level = modal.find('.table_level');
            let authorizationlist = [];
            let list_count = 0;
            if (salespoint == "") {
                alert('Harap memilih salespoint');
                return;
            }
            if (form_type == "") {
                alert('Harap memilih jenis form');
                return;
            }
            table_level.find('tbody tr').not('.empty_row').each(function(index, el) {
                list_count++;
                let id = $(el).data('id');
                let as = $(el).data('as');
                let position = $(el).data('position');
                let level = parseInt($(el).find('.level').text().trim());
                authorizationlist.push({
                    "id": id,
                    "as": as,
                    "position": position,
                    "level": level
                })
            });
            if (list_count < 1) {
                alert('Minimal 1 otorisasi dipilih');
                return;
            }
            // form filling
            let form = $('#updateform');
            let inputfield = form.find('.inputfield');
            inputfield.empty();
            inputfield.append('<input type="hidden" name="salespoint" value="' + salespoint + '">');
            inputfield.append('<input type="hidden" name="form_type" value="' + form_type + '">');
            inputfield.append('<input type="hidden" name="notes" value="' + notes + '">');
            authorizationlist.forEach((item, index) => {
                inputfield.append('<input type="hidden" name="authorization[' + index + '][id]" value="' + item.id +
                    '">');
                inputfield.append('<input type="hidden" name="authorization[' + index + '][position]" value="' +
                    item.position + '">');
                inputfield.append('<input type="hidden" name="authorization[' + index + '][as]" value="' + item.as +
                    '">');
                inputfield.append('<input type="hidden" name="authorization[' + index + '][level]" value="' + item
                    .level + '">');
            });
            form.submit();
        }

        function deleteAuthorization() {
            if (confirm('Matriks Approval akan dihapus dan tidak dapat dikembalikan. Lanjutkan?')) {
                $('#deleteform').submit();
            } else {

            }
        }
    </script>
@endsection
