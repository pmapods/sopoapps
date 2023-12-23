@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Verifikasi Upload BA</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Ticketing</li>
                    <li class="breadcrumb-item active">Verifikasi Upload BA</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <table class="table table-bordered table-striped dataTable" id="verificationDT">
        <thead>
            <tr>
                <th>Kode Tiket</th>
                <th>Informasi Tiket</th>
                <th>Nomor PO Tiket</th>
                <th>Info Upload</th>
                <th>File</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ticketing_block_open_request as $req) 
            <tr>
                <td scope="row">{{ $req->ticket_code }}</td>
                <td class="small">
                    Nama Salespoint : {{ $req->ticket->salespoint->name }}<br>
                    Jenis Ticket : {{ $req->ticket_type_name() }}
                </td>
                <td>{{ $req->po_number }}</td>
                <td class="small">
                    Oleh : {{ $req->created_by_employee->name }}<br>
                    Waktu Upload : {{ $req->created_at->translatedFormat('d F Y (H:i)')}}
                </td>
                <td><a href="#" onclick="window.open('/storage/{{ $req->ba_file_path }}')">Tampilkan File BA</a></td>
                <td>{{ $req->status_name() }}</td>
                <td>
                    @if ($req->status == 0)
                        <button type="button" class="btn btn-danger" onclick="reject('{{ $req->id }}')">Reject</button>
                        <button type="button" class="btn btn-success" onclick="confirm('{{ $req->id }}')">Confirm</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<form action="/ticketing/BAVerification/confirm" method="post" id="confirmform">
    @csrf
    <div class="input_list">
    </div>
</form>
<form action="/ticketing/BAVerification/reject" method="post" id="rejectform">
    @csrf
    <div class="input_list">
    </div>
</form>
@endsection
@section('local-js')
<script>
    $(document).ready(function () {
        var table = $('#verificationDT').DataTable(datatable_settings);
    });

    function confirm(id) {
        $('#confirmform .input_list').empty();
        $('#confirmform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
        $('#confirmform').submit();
    }

    function reject(id) {
        var reason = prompt("Harap memasukan alasan reject BA");
        $('#rejectform .input_list').empty();
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return;
            }
            $('#rejectform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
            $('#rejectform .input_list').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#rejectform').submit();
        }
    }
</script>

@endsection
