@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex flex-column">
                    <h1 class="m-0 text-dark">Custom Ticketing</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Custom Ticketing</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="addCustomTicket()">Tambah Custom Ticketing</button>
        </div>
        <div class="table-responsive mt-2">
            <table class="table table-bordered dataTable" id="customTicketTable">
                <thead>
                    <tr>
                        <th>Nama Custom Ticket</th>
                        <th>Upload Ticket</th>
                        <th>Bidding / Seleksi Vendor</th>
                        <th>PR Manual</th>
                        <th>PO SAP</th>
                        <th>Berkas Penerimaan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($custom_ticketings as $custom_ticketing)
                        @php
                            $ticket = json_decode($custom_ticketing->settings);
                            $create_ticket_text = '';
                            if (in_array('create_ticket', $ticket->steps)) {
                                $create_ticket_text .= "Kelengkapan : \n";
                                $count = 1;
                                foreach ($ticket->create_ticket_file as $file) {
                                    $create_ticket_text .= $count . '. ' . $file . "\n";
                                    $count++;
                                }
                            } else {
                                $create_ticket_text .= 'X';
                            }
                            $received_file_text = '';
                            if (in_array('create_ticket', $ticket->steps)) {
                                $received_file_text .= "Kelengkapan : \n";
                                $count = 1;
                                foreach ($ticket->received_file_name as $file) {
                                    $received_file_text .= $count . '. ' . $file . "\n";
                                    $count++;
                                }
                            } else {
                                $received_file_text .= 'X';
                            }
                        @endphp
                        <tr data-data="{{ json_encode($custom_ticketing) }}">
                            <td>
                                {{ $ticket->ticket_name }}<br>
                                <small>
                                    <b>Jenis Item :</b> {{ $ticket->item_type }}
                                    @switch($ticket->item_type)
                                        @case('barang')
                                            (P01)
                                        @break

                                        @case('jasa')
                                            (P02)
                                        @break

                                        @case('disposal')
                                            (P03)
                                        @break

                                        @default
                                            (P00)
                                    @endswitch
                                    {{-- ({{ $ticket->item_type == 'barang' ? 'P01' : 'P02' }}) --}}
                                    <br>
                                    <b>Nama Item :</b> {{ $ticket->ticket_item_name }}<br>
                                    <b>Satuan :</b> {{ $ticket->uom }}<br>
                                </small>
                            </td>
                            <td style="white-space: pre-line;">{{ $create_ticket_text }}</td>
                            <td>{{ in_array('bidding', $ticket->steps) ? 'V' : 'X' }}</td>
                            <td>{{ in_array('pr_manual', $ticket->steps) ? 'V' : 'X' }}</td>
                            <td>{{ in_array('po_sap', $ticket->steps) ? 'V' : 'X' }}</td>
                            <td style="white-space: pre-line;">{{ $received_file_text }}</td>
                            <td>{{ $custom_ticketing->is_active == 1 ? 'Aktif' : 'Non-Aktif' }}</td>
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
                    <h5 class="modal-title">Tambah Custom Ticketing</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/customticketing/create" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Nama Custom Ticket</label>
                                    <input type="text" class="form-control" name="ticket_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field"l>Nama Item</label>
                                    <input type="text" class="form-control" name="ticket_item_name" required>
                                    <small class="text-info">* Nama item akan menjadi nama item pada PR Manual &
                                        ticketing</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Satuan</label>
                                    <input type="text" class="form-control" name="uom" required>
                                    <small class="text-info">* Satuan akan menjadi nama satuan pada PR Manual &
                                        ticketing</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Jenis Item</label>
                                    <select class="form-control" name="item_type" id="jenis_item" required>
                                        <option value="barang">Barang (P01)</option>
                                        <option value="jasa">Jasa (P02)</option>
                                        <option value="disposal">Disposal (P03)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="1">Aktif</option>
                                        <option value="0">Non-Aktif</option>
                                    </select>
                                    <small class="text-danger">* Jika Aktif pengadaan terkait dapat langsung
                                        dilakukan</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <h4 class="font-weight-bold">Pilihan Step</h4>
                                <hr>
                            </div>
                            <div class="col-12 d-flex flex-column">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="create_ticket" disabled checked> <b>Create Ticket / Upload
                                                    File</b>
                                                <input type="hidden" name="step[]" value="create_ticket">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        @for ($i = 1; $i < 5; $i++)
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-sm"
                                                    name="create_ticket_file[]"
                                                    placeholder="Upload File Name {{ $i }}">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="bidding" id="bidding_vendor">
                                                <b>Bidding / Seleksi Vendor</b>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8"></div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="pr_manual" id="pr_manual">
                                                <b>PR Manual</b>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8"></div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="form-check form-check-inline">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    id="pr_and_po" value="po_sap" disabled checked><b>PR & PO SAP</b>
                                                <input type="hidden" name="step[]" value="po_sap">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="received_file_upload" disabled checked
                                                    id="upload_berkas_penerimaan">
                                                <input type="hidden" name="step[]" value="received_file_upload">
                                                <b>Upload Berkas Penerimaan</b>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        @for ($i = 1; $i < 5; $i++)
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-sm"
                                                    name="received_file_name[]"
                                                    placeholder="Upload File Name {{ $i }}">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <hr>
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
                    <h5 class="modal-title">Update Custom Ticketing</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/customticketing/update" method="post">
                    @csrf
                    <input type="hidden" name="custom_ticketing_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Nama Custom Ticket</label>
                                    <input type="text" class="form-control" name="ticket_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field"l>Nama Item</label>
                                    <input type="text" class="form-control" name="ticket_item_name" required>
                                    <small class="text-info">* Nama item akan menjadi nama item pada PR Manual &
                                        ticketing</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required_field">Satuan</label>
                                    <input type="text" class="form-control" name="uom" required>
                                    <small class="text-info">* Satuan akan menjadi nama satuan pada PR Manual &
                                        ticketing</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Jenis Item</label>
                                    <select class="form-control" name="item_type" id="jenis_item_update" required>
                                        <option value="barang">Barang (P01)</option>
                                        <option value="jasa">Jasa (P02)</option>
                                        <option value="disposal">Disposal (P03)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="required_field">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="1">Aktif</option>
                                        <option value="0">Non-Aktif</option>
                                    </select>
                                    <small class="text-danger">* Jika Aktif pengadaan terkait dapat langsung
                                        dilakukan</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <h4 class="font-weight-bold">Pilihan Step</h4>
                                <hr>
                            </div>
                            <div class="col-12 d-flex flex-column">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="create_ticket" disabled checked> <b>Create Ticket / Upload
                                                    File</b>
                                                <input type="hidden" name="step[]" value="create_ticket">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        @for ($i = 1; $i < 5; $i++)
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-sm"
                                                    name="create_ticket_file[]"
                                                    placeholder="Upload File Name {{ $i }}">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="bidding" id="bidding_vendor_update">
                                                <b>Bidding / Seleksi Vendor</b>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8"></div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="pr_manual" id="pr_manual_update">
                                                <b>PR Manual</b>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8"></div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="form-check form-check-inline">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="po_sap" disabled checked id="pr_and_po_update"><b>PR & PO
                                                    SAP</b>
                                                <input type="hidden" name="step[]" value="po_sap" id="pr_and_po">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="step[]"
                                                    value="received_file_upload" disabled checked>
                                                <input type="hidden" name="step[]" value="received_file_upload">
                                                <b>Upload Berkas Penerimaan</b>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        @for ($i = 1; $i < 5; $i++)
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-sm"
                                                    name="received_file_name[]"
                                                    placeholder="Upload File Name {{ $i }}">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <hr>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-danger" onclick="deleteTicket(this)">Delete</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form method="post" action="" id="submitform">
        @csrf
        <div></div>
    </form>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $("#jenis_item").change(function() {
                let data = $(this).val();
                console.log(data);
                if (data == "disposal") {
                    $("#pr_and_po").prop("checked", false);
                    $("#pr_and_po").prop("disabled", true);
                    $("#bidding_vendor").prop("checked", true);
                    $("#pr_manual").prop("disabled", true);
                    $("#bidding_vendor").prop("disabled", true);
                } else {
                    $("#pr_and_po").prop("disabled", true);
                    $("#pr_and_po").prop("checked", true);
                    $("#bidding_vendor").prop("disabled", false);
                    $("#bidding_vendor").prop("checked", false);
                    $("#pr_manual").prop("disabled", false);
                }
            })

            $("#jenis_item_update").change(function() {
                let data = $(this).val();
                console.log(data);
                if (data == "disposal") {
                    $("#pr_and_po_update").prop("checked", false);
                    $("#bidding_vendor_update").prop("checked", true);
                    $("#bidding_vendor_update").prop("disabled", true);
                    $("#pr_manual_update").prop("disabled", true);
                    $("#pr_manual_update").prop("checked", false);

                } else {
                    $("#pr_and_po_update").prop("disabled", true);
                    $("#pr_and_po_update").prop("checked", true);
                    $("#bidding_vendor_update").prop("checked", false);
                    $("#bidding_vendor_update").prop("disabled", false);
                    $("#pr_manual_update").prop("disabled", false);
                }
            })

            $('#customTicketTable tbody').on('click', 'tr', function() {
                let data = $(this).data('data');
                console.log(data);
                let settings = JSON.parse(data.settings);
                console.log(settings);
                $('#updateModal input[name="custom_ticketing_id"]').val(data.id);
                $('#updateModal input[name="ticket_name"]').val(settings.ticket_name);
                $('#updateModal input[name="ticket_item_name"]').val(settings.ticket_item_name);
                $('#updateModal input[name="uom"]').val(settings.uom);
                $('#updateModal select[name="item_type"]').val(settings.item_type);
                $('#updateModal select[name="status"]').val(data.is_active);
                $('#updateModal select[name="item_type"]').trigger('change');
                $('#updateModal select[name="status"]').trigger('change');

                $('#updateModal input[name="step[]"]').prop('checked', false);
                settings.steps.forEach(function(step) {
                    $('#updateModal input[name="step[]"][value="' + step + '"]').prop(
                        'checked',
                        true);
                });

                $('#updateModal input[name="create_ticket_file[]"]').val("");
                settings.create_ticket_file.forEach(function(create_ticket_file, index) {
                    $('#updateModal input[name="create_ticket_file[]"]').eq(index).val(
                        create_ticket_file);
                });

                $('#updateModal input[name="received_file_name[]"]').val("");
                settings.received_file_name.forEach(function(received_file_name, index) {
                    $('#updateModal input[name="received_file_name[]"]').eq(index).val(
                        received_file_name);
                });
                $('#updateModal').modal('show');
            });
        });

        function addCustomTicket() {
            $('#addModal').modal('show');
        }

        function deleteTicket(el) {
            let custom_ticketing_id = $(el).closest('.modal').find('input[name="custom_ticketing_id"]').val();
            if (confirm(
                    'Custom ticket yang dihapus tidak akan membatalkan tiket sebelumnya. Lanjutkan menghapus data ?'
                )) {
                $('#submitform').prop('action', '/customticketing/delete');
                $('#submitform').find('div').empty();
                $('#submitform').prop('method', 'POST');
                $('#submitform').find('div').append('<input type="hidden" name="custom_ticketing_id" value="' +
                    custom_ticketing_id + '">');
                $('#submitform').submit();
            }
        }
    </script>
@endsection
