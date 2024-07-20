@extends('Layout.auction')
@section('local-css')
    <style>
        #pills-tab .nav-link {
            background-color: #a01e2b48;
            color: black !important;
        }

        #pills-tab .nav-link.active {
            background-color: #A01E2A;
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Auction Ticket</h1>
                </div>
                <!-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Auction</li>
                    </ol>
                </div> -->
            </div>
            <!-- <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAuctionModal"
                    id="addbutton">
                    Tambah Auction
                </button>
            </div> -->
        </div>
    </div>
    <div class="content-body px-4">
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-barangjasa-tab" data-toggle="pill" href="#pills-barangjasa"
                    role="tab" aria-controls="pills-barangjasa" aria-selected="true">Barang Jasa</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-armada-tab" data-toggle="pill" href="#pills-armada" role="tab"
                    aria-controls="pills-armada" aria-selected="false">Armada</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-security-tab" data-toggle="pill" href="#pills-security" role="tab"
                    aria-controls="pills-security" aria-selected="false">Security</a>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="auctionDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Area</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($auctions->where('type', 'barangjasa') as $key => $auction)
                                <tr data-auction="{{ $auction }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $auction->ticket_code }}</td>
                                    <td>{{ $auction->salespoint->name }}</td>
                                    <td>{{ $auction->notes }}</td>
                                    <td>{{ $auction->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-armada" role="tabpanel" aria-labelledby="pills-armada-tab">
                <table id="auctionArmadaDT" class="table table-bordered table-striped dataTable" role="grid">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Area</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1 @endphp
                        @foreach ($auctions->where('type', 'armada') as $key => $auction)
                            <tr data-auction="{{ $auction }}">
                                <td>{{ $count++ }}</td>
                                <td>{{ $auction->ticket_code }}</td>
                                <td>{{ $auction->salespoint->name }}</td>
                                <td>{{ $auction->notes }}</td>
                                <td>{{ $auction->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                <table id="auctionSecurityDT" class="table table-bordered table-striped dataTable" role="grid">
                    <thead>
                        <tr role="row">
                            <th>#</th>
                            <th>Kode</th>
                            <th>Area</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1 @endphp
                        @foreach ($auctions->where('type', 'security') as $key => $auction)
                            <tr data-auction="{{ $auction }}">
                                <td>{{ $count++ }}</td>
                                <td>{{ $auction->ticket_code }}</td>
                                <td>{{ $auction->salespoint->name }}</td>
                                <td>{{ $auction->notes }}</td>
                                <td>{{ $auction->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#auctionDT').DataTable(datatable_settings);
            var armadatable = $('#auctionArmadaDT').DataTable(datatable_settings);
            var securitytable = $('#auctionSecurityDT').DataTable(datatable_settings);
            $('#auctionDT,#auctionArmadaDT,#auctionSecurityDT tbody').on('click', 'tr', function() {
                let data = $(this).data('auction_header');
                modal.find('input[name="id"]').val(data['id']);
                modal.find('input[name="ticket_code"]').val(data['ticket_code']);
                modal.find('select[name="type"]').val(data['type']);
                modal.find('select[name="salespoint_id"]').val(data['salespoint_id']);
                modal.find('select[name="salespoint_id"]').trigger('change');
                modal.find('input[name="notes"]').val(data['notes']);
                modal.find('input[name="status"]').val(data['status']);
                modal.modal('show');
            });
        });
    </script>
@endsection
