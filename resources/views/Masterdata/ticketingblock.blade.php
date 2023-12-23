@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex flex-column">
                    <h1 class="m-0 text-dark">Ticketing Block</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Ticketing Block</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="d-flex justify-content-end">

            <form action="/ticketingblocking/reset" method="post">
                @csrf
                {{-- <button type="button" class="btn btn-danger" onclick="resetTicketingBlock()">Reset Ticketing Block</button> --}}
                <button type="submit" class="btn btn-danger">Reset Ticketing Block</button>
            </form>


            <button type="button" class="btn btn-primary ml-3" onclick="addTicketingBlock()">Tambah Ticketing
                Block</button>
        </div>
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped dataTable" role="grid" id="ticketingBlockTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="display:none">ID</th>
                        <th>Nama Ticketing Block</th>
                        <th>Block Day</th>
                        <th>Max Block Day</th>
                        <th>Max Block Day PR SAP</th>
                        <th>Max Validation Reject Day</th>
                        <th>Dibuat Oleh</th>
                        <th>Terahkir Diubah Oleh</th>
                        <th>Tanggal Pembuatan</th>
                        <th>Tanggal Di Update</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ticketing_blocks as $ticketing_block)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="display:none">{{ $ticketing_block->id }}</td>
                            <td>{{ $ticketing_block->ticketing_type_name }}</td>
                            <td>{{ $ticketing_block->block_day }}</td>
                            <td>{{ $ticketing_block->max_block_day }}</td>
                            <td>{{ $ticketing_block->max_pr_sap_day }}</td>
                            <td>{{ $ticketing_block->max_validation_reject_day }}</td>
                            <td>{{ $ticketing_block->created_by_employee->name }}</td>
                            <td>{{ $ticketing_block->last_update_by_employee->name ?? '-' }}</td>
                            <td>{{ $ticketing_block->created_at }}</td>
                            <td>{{ $ticketing_block->updated_at ?? '-' }}</td>
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
                    <h5 class="modal-title">Tambah Ticketing Block</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/ticketingblocking/create" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="required_field"l>Nama Ticketing Block</label>
                                    <input type="text" class="form-control" name="ticketing_type_name" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="required_field">Block Day</label>
                                    <input type="number" class="form-control" name="block_day" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="required_field"l>Max Block Day</label>
                                    <input type="number" class="form-control" name="max_block_day" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required_field"l>Max Block Day PR SAP</label>
                                    <input type="number" class="form-control" name="max_pr_sap_day" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required_field"l>Max Validation Reject Day</label>
                                    <input type="number" class="form-control" name="max_validation_reject_day" required>
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

    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Ticketing Block</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/ticketingblocking/update" method="post">
                    @csrf
                    <input type="hidden" name="ticketing_block_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>ID</label>
                                    <input type="text" class="form-control" name="id" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="required_field"l>Nama Ticketing Block</label>
                                    <input type="text" class="form-control" name="ticketing_type_name" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="required_field">Block Day</label>
                                    <input type="number" class="form-control" name="block_day" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="required_field"l>Max Block Day</label>
                                    <input type="number" class="form-control" name="max_block_day" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required_field"l>Max Block Day PR SAP</label>
                                    <input type="number" class="form-control" name="max_pr_sap_day" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required_field"l>Max Validation Reject Day</label>
                                    <input type="number" class="form-control" name="max_validation_reject_day" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('#ticketingBlockTable tbody').on('click', 'tr', function() {
                let id = $(this).find('td:eq(1)').text().trim();
                let ticketing_type_name = $(this).find('td:eq(2)').text().trim();
                let block_day = $(this).find('td:eq(3)').text().trim();
                let max_block_day = $(this).find('td:eq(4)').text().trim();
                let max_pr_sap_day = $(this).find('td:eq(5)').text().trim();
                let max_validation_reject_day = $(this).find('td:eq(6)').text().trim();

                $('input[name="id"]').val(id);
                $('input[name="ticketing_type_name"]').val(ticketing_type_name);
                $('input[name="block_day"]').val(block_day);
                $('input[name="max_block_day"]').val(max_block_day);
                $('input[name="max_pr_sap_day"]').val(max_pr_sap_day);
                $('input[name="max_validation_reject_day"]').val(max_validation_reject_day);

                $('#updateModal').modal('show');
            });
        });

        function addTicketingBlock() {
            $('#addModal').modal('show');
        }
    </script>
@endsection
