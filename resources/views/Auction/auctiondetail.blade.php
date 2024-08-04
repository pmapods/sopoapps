@extends('Layout.auction')

@section('local-css')
<style>
    .box {
        box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
        border: 1px solid;
        border-color: gainsboro;
        border-radius: 0.5em;
    }

    .tdbreak {
        /* word-break : break-all; */
    }

    a {
        color: #0069D9 !important;
        cursor: pointer !important;
    }

    .status-open {
        color: green;
    }

    .status-close {
        color: red;
    }

    .button-group {
        margin-bottom: 5px;
        /* Jarak bawah */
    }

    .button-group .btn {
        margin-right: 10px;
        /* Jarak kanan antar tombol */
    }
</style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Auction Ticket Detail</h1>
            </div>
        </div>
        <div class="d-flex justify-content-end"></div>
    </div>
</div>
<div class="content-body px-4">
    <div class="row">
        <div class="col-md-4">
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr>
                        <td><b>Tanggal Pengajuan</b></td>
                        <td>{{ $ticket->updated_at->translatedFormat('d F Y (H:i)') }}</td>
                    </tr>
                    <tr>
                        <td><b>Tanggal Pengadaan</b></td>
                        <td>{{ \Carbon\Carbon::parse($ticket->requirement_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td><b>SalesPoint</b></td>
                        <td>{{ $ticket->salespoint->name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr>
                        <td><b>Status Auction</b></td>
                        <td
                            class="{{ $auctionHeader->status == 0 ? 'status-open' : ($auctionHeader->status == 1 ? 'status-close' : '') }}">
                            @if ($auctionHeader->status == 0)
                                Open
                            @elseif ($auctionHeader->status == 1)
                                Closed
                            @else
                                Unknown
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><b>Type Request</b></td>
                        <td>
                            @if ($auctionHeader->type == 'barangjasa')
                                Barang & Jasa
                            @elseif ($auctionHeader->type == 'armada')
                                Armada
                            @elseif ($auctionHeader->type == 'security')
                                Security
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($auctionHeader->type == 'barangjasa')
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold ">Daftar Barang</h5>
            <table class="table table-bordered table_item">
                <thead class="table-secondary">
                    <tr>
                        <th>Informasi Item</th>
                        <th width="15%"><center>Jumlah</center></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ticket->ticket_item as $item)
                    <tr class="@if ($item->isCancelled) table-danger @endif">
                        <td>
                            {{ $item->name }}
                            {{ $item->brand ? ' // ' . $item->brand : '' }}
                            {{ $item->type ? ' // ' . $item->type : '' }}
                        </td>
                        <td><center>{{ $item->count }} {{ $item->budget_pricing->uom ?? '' }}</center></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($auctionHeader->type == 'armada')
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold ">Daftar Barang</h5>
            <table class="table table-bordered table_item">
                <thead class="table-secondary">
                    <tr>
                        <th width="15%">Brand</th>
                        <th>Name</th>
                        <th width="15%">Alias</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $ticket->armada_type->brand_name }}</td>
                        <td>{{ $ticket->armada_type->name }}</td>
                        <td>{{ $ticket->armada_type->alias }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold">Alasan Pengadaan</h5>
            <span>{{ $ticket->reason ?? '-' }}</span>
        </div>
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold">Upload file Offering</h5>
            <form action="/auction/vendor-request-auction" method="post" id="auction_form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ticket_code" value="{{ $auctionHeader->ticket_code }}">
                <input type="hidden" name="type" value="{{ $auctionHeader->type }}">
                <input type="hidden" name="notes" value="{{ $auctionHeader->notes }}">
                <input type="hidden" name="created_at" value="{{ $auctionHeader->created_at }}">
                <input type="hidden" name="is_booked" value="{{ $auctionHeader->is_booked }}">
                <input type="file" class="form-control-file validatefilesize" id="offering_file" name="offering_file"
                    accept="image/*,application/pdf" required>
                <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                <br><br>
            </form>
        </div>
    </div>
    <center>
        <button type="button" class="btn btn-success mt-3" id="request_auction_button" onclick="requestBidding()" 
            data-status="{{ $auctionHeader->status }}">Request Auctions</button>
        <button type="button" class="btn btn-secondary mt-3" 
            onclick="window.location.href='{{ url('/auction/auctionTicket') }}'">Kembali</button>
    </center>
</div>
@endsection
@section('local-js')
<script>

    document.addEventListener('DOMContentLoaded', function () {
        const requestButton = document.getElementById('request_auction_button');
        const status = requestButton.getAttribute('data-status');

        if (status == 1) {
            requestButton.disabled = true;
        }
    });

    function requestBidding() {
        const requestButton = document.getElementById('request_auction_button');
        const status = requestButton.getAttribute('data-status');
        const fileInput = document.getElementById('offering_file');

        if (status == 1) {
            alert('The auction ticket is closed. You cannot submit.');
            return;
        }

        if (!fileInput.files.length) {
            alert('Please upload a file offering before submitting the auction.');
            return; // Prevent form submission
        }

        document.getElementById('auction_form').submit();
    }
</script>
@endsection