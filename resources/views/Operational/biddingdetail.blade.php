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
                        <i class="fal fa-arrow-left" aria-hidden="true" style="cursor: pointer;"
                            onclick="window.location.href='/bidding'"></i>
                        Bidding Detail <a href="#"
                            onclick="window.open('/ticketing/{{ $ticket->code }}')">({{ $ticket->code }})</a>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item active">Bidding Detail</li>
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
                            <td>{{ $ticket->item_type() }}</td>
                        </tr>
                        <tr>
                            <td><b>Jenis Pengadaan</b></td>
                            <td>{{ $ticket->request_type() }}</td>
                        </tr>
                        <tr>
                            @if ($ticket->budget_type == '' && $ticket->item_type == 4)
                                <td><b></b></td>
                                <td></td>
                            @else
                                <td><b>Jenis Budget</b></td>
                                <td>{{ $ticket->budget_type() }}</td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            @if ($ticket->request_type == 5 || $ticket->request_type == 6)
                <div class="col-sm-6">
                    <h4 class="m-0 font-weight-bold">
                        <a class="uploaded_file small text-primary"
                            onclick='window.open("/storage/{{ $ticket->ba_additional_ticketing }}")'>Tampilkan
                            BA Percepatan</a>
                    </h4>
                </div>
            @endif
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
                            <th width="">Status</th>
                            <th width="">Action</th>
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
                                                            <span onclick='window.open("/storage/{{ $attachment->path }}")'
                                                                download="{{ $attachment->name }}">{{ $naming }}</span>
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
                                                            <span
                                                                onclick='window.open("/storage/{{ $requirement->path }}")'>{{ $requirement->file_completement->name }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </td>
                                <td>
                                    @if (!$item->isCancelled)
                                        @if ($item->bidding)
                                            @if ($item->bidding->status == 0 || $item->bidding->status == 1)
                                                <b>Status Approval</b><br>
                                                @if ($item->bidding->status == 0)
                                                    Menunggu Approval oleh
                                                    <b>{{ $item->bidding->current_authorization()->employee->name }}</b>
                                                @endif
                                                @if ($item->bidding->status == 1)
                                                    Approval selesai --
                                                    {{ $item->bidding->updated_at->translatedFormat('d F Y (H:i)') }}
                                                @endif
                                            @endif

                                            @if ($item->bidding->status == -1)
                                                Approval ditolak oleh
                                                <b>{{ $item->bidding->rejected_by_employee()->name }}</b><br>
                                                Alasan : {{ $item->bidding->reject_notes }}
                                            @endif
                                        @elseif($item->custom_bidding)
                                            <small>File Seleksi Vendor telah di upload oleh
                                                <b>{{ $item->custom_bidding->createdBy->name }}</b></small>
                                        @endif
                                    @else
                                        Dibatalkan oleh <b>{{ $item->cancelled_by_employee()->name }}</b><br>
                                        Alasan : {{ $item->cancel_reason }}
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if (!$item->isCancelled)
                                            @if ($item->bidding)
                                                @if ($item->bidding->status == 0 || $item->bidding->status == 1)
                                                    <button type="button" class="btn btn-primary btn-sm mr-auto"
                                                        onclick="openselectionvendor({{ $item->id }})">Tampilkan form
                                                        seleksi</button>
                                                @endif

                                                @if ($item->bidding->status == -1)
                                                    <button type="button" class="btn btn-primary btn-sm mr-auto"
                                                        onclick="openselectionvendor({{ $item->id }})">Revisi form
                                                        seleksi</button><br>
                                                @endif
                                            @elseif($item->custom_bidding)
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="window.open('/storage/{{ $item->custom_bidding->filepath }}')">Tampilkan
                                                    File Seleksi Vendor</button>
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="reviseCustomBidding({{ $item->custom_bidding->id }})">Revisi
                                                    File Seleksi Vendor</button>
                                            @else
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="openselectionvendor({{ $item->id }})"
                                                    @if (!$item->isFilesChecked()) disabled @endif>Seleksi
                                                    Vendor</button>
                                            @endif
                                            @if (($item->bidding->status ?? 0) != 1 && $item->custom_bidding == null)
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="removeitem({{ $item->id }})">Hapus Item</button>
                                            @endif
                                        @endif
                                    </div>
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
                                <table class="table table-sm">
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
                                                <td>{{ $naming }}</td>
                                                <td><a href="/storage/{{ $attachment->path }}"
                                                        download="{{ $attachment->name }}">tampilkan attachment</a></td>
                                                @if ($attachment->status == 0)
                                                    <td class="align-middle d-flex">
                                                        <button type="button" class="btn btn-success btn-sm mr-2"
                                                            onclick="confirmform({{ $attachment->id }},'attachment')">Confirm</button>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="reject({{ $attachment->id }},'attachment')">Reject</button>
                                                    </td>
                                                @endif
                                                @if ($attachment->status == 1)
                                                    <td colspan="2">
                                                        <b class="text-success">Confirmed</b>
                                                        @if (Auth::user()->id == 1 ||
                                                            Auth::user()->id == 115 ||
                                                            Auth::user()->id == 116 ||
                                                            Auth::user()->id == 117 ||
                                                            Auth::user()->id == 120)
                                                            {{-- superadmin only --}}
                                                            <b class="text-secondary ml-3" style="cursor: pointer"
                                                                onclick="reviseRequirement({{ $requirement ?? '' }},'attachment')">
                                                                <u>Revisi Ulang</u>
                                                            </b>
                                                        @endif
                                                        <br>
                                                        {{ $attachment->updated_at->translatedFormat('d F Y (H:i)') }}
                                                        confirmed by
                                                        <b>{{ $attachment->confirmed_by_employee()->name }}</b>
                                                    </td>
                                                @endif
                                                @if ($attachment->status == -1)
                                                    <td colspan="2">
                                                        <b class="text-danger">Rejected</b><br>
                                                        {{ $attachment->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                        by <b>{{ $attachment->rejected_by_employee()->name }}</b><br>
                                                        Alasan : {{ $attachment->reject_notes }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @foreach ($item->ticket_item_file_requirement as $requirement)
                                            <tr>
                                                <td>{{ $requirement->file_completement->name }}</td>
                                                <td><a href="/storage/{{ $requirement->path }}"
                                                        download="{{ $requirement->name }}">tampilkan attachment</a></td>
                                                @if ($requirement->status == 0)
                                                    <td class="align-middle d-flex">
                                                        <button type="button" class="btn btn-success btn-sm mr-2 ml-2"
                                                            onclick="confirmform({{ $requirement->id }},'file')">Confirm</button>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="reject({{ $requirement->id }},'file')">Reject</button>
                                                    </td>
                                                @endif
                                                @if ($requirement->status == 1)
                                                    <td colspan="2">
                                                        <b class="text-success">Confirmed</b>
                                                        @if (Auth::user()->id == 1 ||
                                                            Auth::user()->id == 115 ||
                                                            Auth::user()->id == 116 ||
                                                            Auth::user()->id == 117 ||
                                                            Auth::user()->id == 120)
                                                            {{-- superadmin only --}}
                                                            <b class="text-secondary ml-3" style="cursor: pointer"
                                                                onclick="reviseRequirement({{ $requirement ?? '' }},'file')">
                                                                <u>Revisi Ulang</u>
                                                            </b>
                                                        @endif
                                                        <br>
                                                        {{ $requirement->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                        confirmed by
                                                        <b>{{ $requirement->confirmed_by_employee()->name }}</b>
                                                    </td>
                                                @endif
                                                @if ($requirement->status == -1)
                                                    <td colspan="2">
                                                        <b class="text-danger">Rejected</b><br>
                                                        {{ $requirement->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                        by <b>{{ $requirement->rejected_by_employee()->name }}</b><br>
                                                        Alasan : {{ $requirement->reject_notes }}
                                                    </td>
                                                @endif
                                                </trcol-md-12>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            @php
                $ticket->ticket_item = $item;
            @endphp

            @if ($item->file_missing_filepath)
                <div class="col-md-12 box p-3 mt-3">
                    <table class="table table-sm">
                        <tbody>
                            <h5 class="font-weight-bold">File Kekurangan Berkas</h5>
                            <tr>
                                <td>
                                    {{ $item->file_missing_name_file }}
                                </td>
                                <td>
                                    Alasan : {{ $item->file_missing_reason }}
                                </td>
                                <td>
                                    <p class="text-primary">
                                        <a onclick='window.open("/storage/{{ $item->file_missing_filepath }}")'>tampilkan
                                            attachment</a>
                                    </p>
                                </td>
                                @if ($item->file_missing_status == 0)
                                    @if ($item->file_missing_revised_by)
                                        <td>
                                            Di Revisi Oleh : {{ $item->revised_by_employee()->name }}
                                        </td>
                                    @endif
                                    <td>
                                        <label class="text-warning">
                                            Menunggu Proses Validasi Data
                                        </label>
                                    <td class="align-middle d-flex">
                                        <form action="/confirm-missingfile/{{ $item->id }}" method="post"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm mr-2"
                                                name="confirm_file_kekurangan_berkas">Confirm</button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="rejectMissingFile()">Reject</button>
                                    </td>
                                    </td>
                                @elseif ($item->file_missing_status == 1)
                                    <td>
                                        <label class="text-success">
                                            Confirmed
                                        </label><br>
                                        {{ $item->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                        Confirmed by <b>{{ $item->confirmed_by_employee()->name }}</b>
                                    </td>
                                @elseif ($item->file_missing_status == -1)
                                    <td>
                                        <label class="text-danger">
                                            Rejected
                                        </label>
                                        {{ $item->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                        Rejected by <b>{{ $item->rejected_by_employee()->name }}</b><br>
                                        Reason : {{ $item->file_missing_reject_notes }}
                                    </td>
                                @endif
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
                <div class="d-flex justify-content-between">
                    <h5 class="font-weight-bold">Daftar Vendor</h5>
                    @if ($isVendorEditAvailable)
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#addVendorModal">+ Tambah Vendor</button>
                    @endif
                </div>
                <table class="table table-bordered table_vendor mt-2">
                    <thead>
                        <tr>
                            <th>Kode Vendor</th>
                            <th>Nama Vendor</th>
                            <th>Sales Person</th>
                            <th>Telfon</th>
                            <th>Tipe</th>
                            @if ($isVendorEditAvailable)
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if ($ticket->ticket_vendor->count() > 0)
                            @foreach ($ticket->ticket_vendor as $vendor)
                                @php
                                    $isAddedOnBidding = $vendor->added_on == 'bidding' ? true : false;
                                @endphp
                                <tr @if ($isAddedOnBidding) class="table-info" @endif>
                                    <td>
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
                                    @if ($isVendorEditAvailable)
                                        <td>
                                            <i class="fa fa-times text-danger" aria-hidden="true"
                                                onclick="removeVendor(this,'{{ $vendor->id }}','{{ $vendor->name }}')"></i>
                                        </td>
                                    @endif
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
                                    @if ($isVendorEditAvailable)
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">Vendor belum dipilih</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @if ($ticket->ba_vendor_filename != null && $ticket->ba_vendor_filepath != null)
                    <b> Berita Acara </b><br>
                    <a href="#" onclick='window.open("/storage/{{ $ticket->ba_vendor_filepath }}")'>tampilkan
                        berita acara</a><br>
                    @if ($ticket->ba_status == 0)
                        <div class="d-flex">
                            <button type="button" class="btn btn-success btn-sm mr-2"
                                onclick="confirmform({{ $ticket->id }},'vendor')">Confirm</button>
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="reject({{ $ticket->id }},'vendor')">Reject</button>
                        </div>
                    @endif
                    @if ($ticket->ba_status == 1)
                        <div>
                            <b class="text-success">Confirmed</b>
                            @if (Auth::user()->id == 1 ||
                                Auth::user()->id == 115 ||
                                Auth::user()->id == 116 ||
                                Auth::user()->id == 117 ||
                                Auth::user()->id == 120)
                                {{-- superadmin only --}}
                                <b class="text-secondary ml-3" style="cursor: pointer"
                                    onclick="reviseRequirement({{ $requirement ?? '' }},'vendor')">
                                    <u>Revisi Ulang</u>
                                </b>
                            @endif
                            <br>
                            {{ $ticket->updated_at->translatedFormat('d F Y (H:i)') }}
                            confirmed by <b>{{ $ticket->ba_confirmed_by_employee()->name }}</b>
                        </div>
                    @endif
                    @if ($ticket->ba_status == -1)
                        <div>
                            <b class="text-danger">Rejected</b><br>
                            {{ $ticket->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                            rejected by <b>{{ $ticket->ba_rejected_by_employee()->name }}</b><br>
                            Alasan : {{ $ticket->ba_reject_notes }}
                        </div>
                    @endif
                @endif
            </div>
            @if ($ticket->ticket_additional_attachment->count() > 0)
                <div class="col-md-12">
                    <h5>Attachment Tambahan</h5>
                    @foreach ($ticket->ticket_additional_attachment as $attachment)
                        <a href="/storage/{{ $attachment->path }}"
                            download="{{ $attachment->name }}">{{ $attachment->name }}</a><br>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($ticket->fri_forms->count() > 0)
            <h5 class="mt-3">Form Request Infrastruktur</h5>
            @php
                $isEditFRI = false;
            @endphp
            @include('Operational.fri_form')
        @endif
        <center>
            @if ($ticket->status == 2)
                <button type="button" class="btn btn-danger mt-3" onclick="terminateticket()">Batalkan
                    Pengadaan</button>
            @endif
        </center>
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
    <div class="modal fade" id="addVendorModal" tabindex="-1" data-backdrop="static" role="dialog"
        aria-hidden="true">
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
    <div class="modal fade" id="missingFileModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject File Kekurangan Berkas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/reject-missingfile/{{ $item->id }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Masukan Alasan : (Wajib)</label>
                                    <input type="text-area" class="form-control" name="file_missing_reject_notes"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Reject</button>
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
    </script>
@endsection
