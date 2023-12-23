@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Verifikasi Upload BA<span class="spinner-border text-sm text-danger ml-3"
                            role="status" style="display:none">
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Verifikasi Upload BA</li>
                    </ol>
                </div>
                <br>
                <br>
                <div class="col-sm-6">
                    <h5 class="m-0 text-info">
                        <a href="/dashboard"> Back To Dashboard </a>
                    </h5>
                </div>
            </div>
        </div>
    </div>
    <br>
    @if (Auth::user()->id == 115 || Auth::user()->id == 1 || Auth::user()->id == 163)
        {{-- superadmin & PCM only --}}
        <div class="content-body px-4">
            <div class="col-md-12">
                <table id="ba_verification" class="table table-bordered table-striped dataTable" role="grid">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Pengadaan</th>
                            <th>Nomor PO</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nama Pengaju</th>
                            <th>Jenis Transaksi</th>
                            <th>Status Akhir</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
@endsection
@section('local-js')
    <script>
        var csrf = "{{ csrf_token() }}";
        $(document).ready(function() {
            var table = $('#ba_verification').DataTable({
                ajax: function(d, cb) {
                    fetch("/getBAverification")
                        .then(response => response.json())
                        .then(data => cb(data));
                },
                columns: [{
                        data: 'nomor'
                    },
                    {
                        data: 'ticket_code'
                    },
                    {
                        data: 'po_number'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'transaction_type'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'link',
                        "render": function(data, type, row, meta) {
                            return '<a href="#" class="text-primary font-weight-bold" onclick="window.open(\'' +
                                data + '\')">Buka</a>';
                        }
                    }
                ]
            });
        });
    </script>
@endsection
