@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex flex-column">
                    <h1 class="m-0 text-dark">Email CC</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Email CC</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content-body px-4">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary ml-3" onclick="addTicketingBlock()">Tambah Email CC</button>
        </div>
        <div class="table-responsive mt-4">
            <table id="emailcc" class="table table-bordered table-striped dataTable" role="grid"
                id="ticketingBlockTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Jabatan</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Pembuatan</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($email_ccs as $email_cc)
                        <tr data-employee_position_id="{{ $email_cc->employee_position }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $email_cc->employee_positions->name }}</td>
                            <td>{{ $email_cc->created_by_employee->name }}</td>
                            <td>{{ $email_cc->created_at->translatedFormat('d F Y (H:i:s)') }}</td>
                            <td>
                                <a class="btn btn-danger" href="/emailcc/delete/{{ $email_cc->id }}"
                                    value="{{ $email_cc->id }}">Hapus</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Email CC</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/emailcc/create" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nama Jabatan</label>
                                    <select class="form-control select2" name="employee_position" required>
                                        <option value="">-- Pilih Nama Jabatan --</option>
                                        @foreach ($employee_positions as $employee_position)
                                            <option value="{{ $employee_position->id }}">
                                                {{ $employee_position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Jabatan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body col-sm-12">
                    <table class="table table-bordered table-striped dataTable col-sm-12" role="grid" id="data_detail">
                        <thead width="100%" class="col-sm-12">
                            <tr>
                                <th width="50%">Nama Employee</th>
                                <th width="50%">Email Employee</th>
                            </tr>
                        </thead>
                    </table>
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
            let employee_position_id = 0;

            var table = $('#data_detail').DataTable({
                ajax: {
                    url: "/api/emailcc/detail",
                    data: function(d) {
                        d.employee_position_id = employee_position_id;
                    },
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'email'
                    }
                ],
                // paging: false,
                // searching: false,
            });

            $('#emailcc tbody').on('click', 'tr', function(e) {
                employee_position_id = $(this).data().employee_position_id;
                table.ajax.reload();
                console.log($(this).data().employee_position_id);
                $('#detailModal').modal('show');
            });
        });

        function addTicketingBlock() {
            $('#addModal').modal('show');
        }
    </script>
@endsection
