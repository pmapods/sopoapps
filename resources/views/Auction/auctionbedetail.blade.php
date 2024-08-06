@extends('Layout.app')
@section('local-css')
<style>
    .box {
        box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
        border: 1px solid;
        border-color: gainsboro;
        border-radius: 0.5em;
    }
</style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fal fa-arrow-left" aria-hidden="true" style="cursor: pointer;" onclick="window.location.href='/auctionbe'"></i>
                    Auction Detail ({{ $ticket->code }})
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Auction</li>
                    <li class="breadcrumb-item active">Auction Detail</li>
                </ol>
            </div>
        </div>
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
                        <td><b>Jenis Item</b></td>
                        <td> @if ($type == 'barangjasa')
                            {{ $ticket->item_type() }}
                            @endif
                            @if ($type == 'armada')
                            {{ $ticket->type() }}
                            @endif
                            @if ($type == 'security')
                            {{ $ticket->type() }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><b>Jenis Pengadaan</b></td>
                        <td>@if ($type == 'barangjasa')
                            {{ $ticket->request_type() }}
                            @endif
                            @if ($type == 'armada')
                            -
                            @endif
                            @if ($type == 'security')
                            -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><b>Jenis Budget</b></td>
                        <td>@if ($type == 'barangjasa')
                            {{ $ticket->budget_type() }}
                            @endif
                            @if ($type == 'armada')
                            -
                            @endif
                            @if ($type == 'security')
                            -
                            @endif
                    </tr>
                </tbody>
            </table>
        </div>
        @if($type == 'barangjasa')
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold ">Daftar Barang</h5>
            <table class="table table-bordered table_item">
                <thead class="table-secondary">
                    <tr>
                        <th width="15%">Informasi Item</th>
                        <th width="15%">Harga Satuan</th>
                        <th>Jumlah</th>
                        <th width="15%">Total</th>
                        <th width="">Attachment</th>
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
                        <td class="rupiah_text">{{ $item->price }}</td>
                        <td>{{ $item->count }} {{ $item->budget_pricing->uom ?? '' }}</td>
                        <td class="rupiah_text">{{ $item->price * $item->count }}</td>
                        <td>
                            @if ($item->ticket_item_attachment->count() == 0 && $item->ticket_item_file_requirement->count() == 0)
                            -
                            @endif
                            @if ($item->ticket_item_attachment->count() > 0)
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    @foreach ($item->ticket_item_attachment as $attachment)
                                    <tr>
                                        @php
                                        $naming = '';
                                        $filename = explode('.', $attachment->name)[0];
                                        switch ($filename) {
                                        case 'ba_file':
                                        $naming = 'berita acara merk/tipe lain';
                                        break;

                                        case 'old_item':
                                        $naming = 'foto barang lama untuk replace';
                                        break;

                                        default:
                                        $naming = $filename;
                                        break;
                                        }
                                        @endphp
                                        <td class="tdbreak text-primary cursor-pointer">
                                            <span onclick='window.open("/storage/{{ $attachment->path }}")' download="{{ $attachment->name }}">{{ $naming }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                            @if ($item->ticket_item_file_requirement->count() > 0)
                            <table class="table table-borderless table-sm small">
                                <tbody>
                                    @foreach ($item->ticket_item_file_requirement as $requirement)
                                    <tr>
                                        <td class="tdbreak text-primary cursor-pointer">
                                            <span onclick='window.open("/storage/{{ $requirement->path }}")'>{{ $requirement->file_completement->name }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <h5 class="font-weight-bold">Validasi Kelengkapan Berkas</h5>
            <div class="row px-2">
                @php
                $count_file = 0;
                foreach ($ticket->ticket_item as $titem) {
                $count_file += $titem->ticket_item_attachment->count();
                $count_file += $titem->ticket_item_file_requirement->count();
                }
                @endphp
                @if ($count_file == 0)
                Tidak ada berkas kelengkapan
                @else
                @foreach ($ticket->ticket_item as $item)
                <div class="col-md-6">
                    <h5>{{ $item->name }}</h5><br>
                </div>
                @endforeach
                @endif
            </div>
        </div>
        @endif

        @if($type == 'armada')
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold ">Daftar Barang</h5>
            <table class="table table-bordered table_item">
                <thead class="table-secondary">
                    <tr>
                        <th width="15%">Brand</th>
                        <th>Name</th>
                        <th width="15%">Alias</th>
                        <th width="15%">Niaga</th>
                        <th width="15%">SBH</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $ticket->armada_type->brand_name }}</td>
                        <td>{{ $ticket->armada_type->name }}</td>
                        <td>{{ $ticket->armada_type->alias }}</td>
                        <td>{{ $ticket->armada_type->isNiaga() }}</td>
                        <td>{{ $ticket->armada_type->isSBH() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold">Alasan Pengadaan</h5>
            <span>{{ $ticket->reason ?? '-' }}</span>
        </div>
        @if($type == 'barangjasa')
        <div class="col-md-12 box p-3 mt-3">
            <div class="d-flex justify-content-between">
                <h5 class="font-weight-bold">Daftar Vendor</h5>
            </div>
            <table class="table table-bordered table_vendor mt-2">
                <thead>
                    <tr>
                        <th>Kode Vendor</th>
                        <th>Nama Vendor</th>
                        <th>Sales Person</th>
                        <th>Telfon</th>
                        <th>Tipe</th>
                    </tr>
                </thead>
                <tbody>
                    @php $countVendor = 0; @endphp
                    @if ($ticket->ticket_vendor->count() > 0)
                    @foreach ($ticket->ticket_vendor as $vendor)
                    @php
                    $isAddedOnBidding = $vendor->added_on == 'bidding' ? true : false;
                    @endphp
                    <tr @if ($isAddedOnBidding) class="table-info" @endif>
                        <td>
                            @php $countVendor = $countVendor + 1; @endphp
                            @if ($vendor->vendor() != null)
                            {{ $vendor->vendor()->code }}
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $vendor->name }}</td>
                        <td>{{ $vendor->salesperson }}</td>
                        <td>{{ $vendor->phone }}</td>
                        <td>{{ $vendor->type() }}</td>
                    </tr>
                    @endforeach
                    @foreach ($trashed_ticket_vendors as $vendor)
                    <tr class="table-danger">
                        <td>
                            @if ($vendor->vendor() != null)
                            {{ $vendor->vendor()->code }}
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            {{ $vendor->name }}<br>
                            <b>Dihapus oleh : </b>{{ $vendor->deletedBy->name ?? '-' }}<br>
                            <b>Alasan : </b>{{ $vendor->delete_reason ?? '-' }}<br>
                        </td>
                        <td>{{ $vendor->salesperson }}</td>
                        <td>{{ $vendor->phone }}</td>
                        <td>{{ $vendor->type() }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="6" class="text-center">Vendor belum dipilih</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($ticket->auction_status == 0)
    <center>
        <button type="button" class="btn btn-success mt-3" onclick="publishBidding()">Publish Auction</button>
    </center>
    @endif
    @if($ticket->auction_status == 1)
    <center>
        <button type="button" class="btn btn-danger mt-3" onclick="unpublishBidding()">Stop Publish Auction</button>
    </center>
    @endif
</div>
<form action="/confirmticketfilerequirement" method="post" id="confirmform">
    @csrf
    @method('patch')
    <div class="input_list">
    </div>
</form>
<form action="/rejectticketfilerequirement" method="post" id="rejectform">
    @csrf
    @method('patch')
    <div class="input_list">
    </div>
</form>
<form action="/removeticketitem" method="post" id="removeitemform">
    @csrf
    @method('delete')
    <div class="input_list"></div>
</form>
<form action="/terminateticket" method="post" id="terminateform">
    @csrf
    @method('patch')
    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
</form>
<form id="submitform" method="post">
    @csrf
    <div></div>
</form>
<div class="modal fade" id="addVendorModal" tabindex="-1" data-backdrop="static" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Vendor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/bidding/addvendor" method="post">
                @csrf
                <input type="hidden" name="ticket_code" value="{{ $ticket->code }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Tipe Vendor</label>
                                <select class="form-control" name="vendor_type" id="vendor_type" required>
                                    <option value="">-- Pilih Vendor --</option>
                                    <option value="0">Vendor Terdaftar</option>
                                    <option value="1">One Time Vendor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 listed_vendor_field">
                            <div class="form-group">
                                <label class="required_field">Pilih Vendor</label>
                                <select class="form-control select2" name="vendor_id">
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 unlisted_vendor_field">
                            <div class="form-group">
                                <label class="required_field">Nama Vendor</label>
                                <input type="text" class="form-control" name="vendor_name">
                            </div>
                        </div>
                        <div class="col-12 unlisted_vendor_field">
                            <div class="form-group">
                                <label class="required_field">Nama Salesperson</label>
                                <input type="text" class="form-control" name="salesperson_name">
                            </div>
                        </div>
                        <div class="col-12 unlisted_vendor_field">
                            <div class="form-group">
                                <label class="required_field">Nomor telfon</label>
                                <input type="text" class="form-control" name="phone">
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
@endsection
@section('local-js')
@yield('fri-js')
<script>
    $(document).ready(function() {
        $('#vendor_type').change(function() {
            $('.listed_vendor_field').hide();
            $('.listed_vendor_field select').prop('required', false);
            $('.unlisted_vendor_field').hide();
            $('.unlisted_vendor_field input').prop('required', false);
            if ($(this).val() == "0") {
                $('.listed_vendor_field').show();
                $('.listed_vendor_field select').prop('required', true);
            }
            if ($(this).val() == "1") {
                $('.unlisted_vendor_field').show();
                $('.unlisted_vendor_field input').prop('required', true);
            }
        });
        $('#addVendorModal').on('show.bs.modal', function(event) {
            $('#vendor_type').trigger('change');
        });
    });

    function openselectionvendor(item_id) {
        window.location.href = window.location.href + '/' + item_id;
    }

    function confirmform(id, type) {
        $('#confirmform .input_list').empty();
        $('#confirmform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
        $('#confirmform .input_list').append('<input type="hidden" name="type" value="' + type + '">');
        $('#confirmform').submit();
    }

    function reject(id, type) {
        var reason = prompt("Harap memasukan alasan penolakan");
        $('#rejectform .input_list').empty();
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return;
            }
            $('#rejectform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
            $('#rejectform .input_list').append('<input type="hidden" name="type" value="' + type + '">');
            $('#rejectform .input_list').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#rejectform').submit();
        }
    }

    function removeitem(id) {
        var reason = prompt("Harap memasukan alasan penghapusan item");
        $('#removeitemform .input_list').empty();
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return;
            }
            $('#removeitemform .input_list').append('<input type="hidden" name="ticket_item_id" value="' + id + '">');
            $('#removeitemform .input_list').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#removeitemform').submit();
        }
    }

    function removeVendor(el, ticket_vendor_id, vendor_name) {
        var reason = prompt("Apakah anda yakin untuk menghapus vendor " + vendor_name +
            " dari pengadaan ? Harap memasukan alasan");
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return;
            }
            $('#submitform').prop('action', '/bidding/removevendor');
            $('#submitform').prop('method', 'post');
            $('#submitform div').empty();
            $('#submitform div').append("<input type='hidden' name='ticket_vendor_id' value='" + ticket_vendor_id +
                "'>");
            $('#submitform div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').submit();
        }
    }

    function terminateticket() {
        var reason = prompt(
            "PERHATIAN ! Dengan membatalkan pengadaan. Area tidak dapat melakukan revisi terhadap pengadaan ini. Masukkan alasan pembatalan pengadaan"
        );
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return
            }
            $('#terminateform').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#terminateform').submit();
        }
    }

    function publishAuction() {
        $('#submitform').prop('action', '/auctionbe/publish');
        $('#submitform').find('div').empty();
        $('#submitform').prop('method', 'POST');
        $('#submitform').append('<input type="hidden" name="id" value="{{ $ticket->id }}">');
        $('#submitform').submit();

    }

    function unpublishAuction() {
        $('#submitform').prop('action', '/auctionbe/unpublish');
        $('#submitform').find('div').empty();
        $('#submitform').prop('method', 'POST');
        $('#submitform').append('<input type="hidden" name="id" value="{{ $ticket->id }}">');
        $('#submitform').submit();
    }

    function reviseCustomBidding(custom_bidding_id) {
        var reason = prompt("PERHATIAN ! Dengan melakukan revisi, file akan dihapus. Isi alasan revisi");
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return
            }

            $('#submitform').prop('action', '/bidding/revisecustombidding');
            $('#submitform').find('div').empty();
            $('#submitform').prop('method', 'POST');
            $('#submitform div').append('<input type="hidden" name="custom_bidding_id" value="' + custom_bidding_id +
                '">');
            $('#submitform div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').submit();
        }
    }

    function reviseRequirement(requirement_data, type) {
        var reason = prompt(
            "File akan menjadi status reject dan area terkait harus upload file revisi. Isi alasan revisi confirm.");
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return
            }
            $('#submitform').prop('action', '/bidding/reviseconfirmedfilerequirement');
            $('#submitform').prop('method', 'POST');
            $('#submitform div').empty();
            $('#submitform div').append('<input type="hidden" name="id" value="' + requirement_data.id + '">');
            $('#submitform div').append('<input type="hidden" name="type" value="' + type + '">');
            $('#submitform div').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#submitform').submit();
        }
    }

    function rejectMissingFile() {
        $('#missingFileModal').modal('show');
    }

    function publishBidding() {
        $('#submitform').prop('action', '/auctionbe/publish');
        $('#submitform').find('div').empty();
        $('#submitform').prop('method', 'POST');
        $('#submitform').append('<input type="hidden" name="id" value="{{ $ticket->id }}">');
        $('#submitform').append('<input type="hidden" name="type" value="{{ $type }}">');
        $('#submitform').submit();

    }

    function unpublishBidding() {
        $('#submitform').prop('action', '/auctionbe/unpublish');
        $('#submitform').find('div').empty();
        $('#submitform').prop('method', 'POST');
        $('#submitform').append('<input type="hidden" name="id" value="{{ $ticket->id }}">');
        $('#submitform').append('<input type="hidden" name="type" value="{{ $type }}">');

        $('#submitform').submit();

    }
</script>
@endsection