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

<div class="content-body">
    <div class="row">
        <div class="col-md-4">
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr>
                        <td><b>Auction Code</b></td>
                        <td>{{ $tickets->ticket_code }}</td>
                    </tr>
                    <tr>
                        <td><b>Type Request</b></td>
                        <td>
                            @if ($tickets->type == 'barangjasa')
                                Barang & Jasa
                            @elseif ($tickets->type == 'armada')
                                Armada
                            @elseif ($tickets->type == 'security')
                                Security
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><b>Status Auction</b></td>
                        <td
                            class="{{ $tickets->status == 0 ? 'status-open' : ($tickets->status == 1 ? 'status-close' : '') }}">
                            @if ($tickets->status == 0)
                                Open
                            @elseif ($tickets->status == 1)
                                Closed
                            @else
                                Unknown
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-12 box p-3 mt-3">
        <h5 class="font-weight-bold">Lelang Details</h5>
        <table class="table table-bordered table_vendor">
            <thead>
                <tr>
                    <th>Keterangan Request</th>
                    <th>Tanggal Mulai Lelang</th>
                    <th>Qty Vendor Apply</th>
                </tr>
                <tr>
                    <td>{{ $tickets->notes }}</td>
                    <td>{{ $tickets->created_at->translatedFormat('d F Y (H:i)') }}</td>
                    <td>{{ $tickets->is_booked }}</td>
                </tr>
            </thead>
        </table>
    </div>

    <div class="col-md-12 box p-3 mt-3">
        <h5 class="font-weight-bold">Upload file Offering</h5>
        <form action="/auction/vendor-request-auction" method="post" id="auction_form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_code" value="{{ $tickets->ticket_code }}">
            <input type="hidden" name="type" value="{{ $tickets->type }}">
            <input type="hidden" name="notes" value="{{ $tickets->notes }}">
            <input type="hidden" name="created_at" value="{{ $tickets->created_at }}">
            <input type="hidden" name="is_booked" value="{{ $tickets->is_booked }}">
            <input type="file" class="form-control-file validatefilesize" id="offering_file" name="offering_file"
                accept="image/*,application/pdf" required>
            <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
            <br><br>
            <center class="button-group">
                <button type="submit" class="btn btn-success">Request Auctions</button>
            </center>
        </form>
        <center class="button-group">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ url('/auction/auctionTicket') }}'">Kembali</button>
        </center>
    </div>
</div>
@endsection

@section('local-js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('auction_form');
        const fileInput = document.getElementById('offering_file');

        form.addEventListener('submit', function (event) {
            if (!fileInput.files.length) {
                event.preventDefault();
                alert('Please upload file offering before submit the auction.');
                window.location.reload();
            }
        });
    });
</script>
@endsection