@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Dashboard Reminder Perpanjangan Barang Jasa Jenis IT<span
                            class="spinner-border text-sm text-danger ml-3" role="status" style="display:none">
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Dashboard Reminder Perpanjangan Barang Jasa Jenis IT</li>
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
    @if (Auth::user()->id == 1 ||
            Auth::user()->id == 115 ||
            Auth::user()->id == 116 ||
            Auth::user()->id == 117 ||
            Auth::user()->id == 197 ||
            Auth::user()->id == 483 ||
            Auth::user()->id == 548 ||
            Auth::user()->id == 484 ||
            Auth::user()->id == 120)
        <div class="content-body px-4">
            <div class="col-md-12">
                <table id="it_barangjasa_reminder" class="table table-bordered table-striped dataTable" role="grid">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Sales Point</th>
                            <th>Kode Pengadaan</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nama Pengaju</th>
                            <th>Reminder End Date</th>
                            <th>Keterangan</th>
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
            var table = $('#it_barangjasa_reminder').DataTable({
                ajax: function(d, cb) {
                    fetch("/itbarangjasareminder")
                        .then(response => response.json())
                        .then(data => cb(data));
                },
                columns: [{
                        data: 'nomor'
                    },
                    {
                        data: 'salespoint'
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'end_date'
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
