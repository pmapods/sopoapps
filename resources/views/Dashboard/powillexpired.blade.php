@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Dashboard PO Will Expired<span class="spinner-border text-sm text-danger ml-3"
                            role="status" style="display:none">
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Dashboard PO Will Expired</li>
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
    <div class="content-body px-4">
        <div class="col-md-12">
            <table id="po_will_expired_table" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sales Point</th>
                        <th>No PO</th>
                        <th>Jenis Transaksi</th>
                        <th>Nama Vendor</th>
                        <th width="15%">PO Start Date</th>
                        <th width="15%">PO End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        var csrf = "{{ csrf_token() }}";
        $(document).ready(function() {
            var table = $('#po_will_expired_table').DataTable({
                ajax: function(d, cb) {
                    fetch("/getPoWillExpired")
                        .then(response => response.json())
                        .then(data => cb(data));
                },
                columns: [{
                        data: 'nomor'
                    },
                    {
                        data: 'salespoint_name'
                    },
                    {
                        data: 'po_number'
                    },
                    {
                        data: 'transaction_type'
                    },
                    {
                        data: 'vendor_name'
                    },
                    {
                        data: 'start_date'
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
