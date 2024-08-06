@extends('Layout.app')
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
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Pengadaan Barang Jasa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Pengadaan</li>
                        <li class="breadcrumb-item active">{{ $ticket->code }}</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
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
                        <tr>
                            <td><b>Divisi</b></td>
                            <td>{{ $ticket->division ?? '-' }}</td>
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
                        @if (isset($ticket->is_it))
                            <tr>
                                <td><b>Jenis IT</b></td>
                                <td>{{ $ticket->is_it ? 'IT' : 'Non-IT' }}</td>
                            </tr>
                        @endif
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
            <div class="col-md-4 text-right">
                @if ($ticket->budget_upload != null)
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#budget_modal">
                        Tampilkan Budget Terkait
                    </button>
                    <div class="modal fade text-left" id="budget_modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $ticket->budget_upload->code }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 row mb-2">
                                            @if (in_array($ticket->item_type, [0, 1, 2]))
                                                <div class="col-3 font-weight-bold">Status</div>
                                                <div class="col-3">{{ $ticket->budget_upload->status() }}</div>
                                                <div class="col-3 font-weight-bold">Periode</div>
                                                <div class="col-3">
                                                    {{ $ticket->budget_upload->created_at->translatedFormat('F Y') }}
                                                </div>
                                            @else
                                                <div class="col-3 font-weight-bold">Salespoint</div>
                                                <div class="col-3">{{ $ticket->budget_upload->salespoint->name }}</div>
                                                <div class="col-3 font-weight-bold">Divisi</div>
                                                <div class="col-3">{{ $ticket->budget_upload->division }}</div>
                                                <div class="col-3 font-weight-bold">Tahun</div>
                                                <div class="col-3">{{ $ticket->budget_upload->year }}</div>
                                                <div class="col-3 font-weight-bold">Waktu Pengajuan</div>
                                                <div class="col-3">
                                                    {{ $ticket->budget_upload->created_at->translatedFormat('d F Y (H:i)') }}
                                                </div>
                                                <div class="col-3 font-weight-bold">Jenis Pengajuan</div>
                                                <div class="col-3">{{ $ticket->budget_upload->type }}</div>
                                                <div class="col-3 font-weight-bold">Nama Pengaju</div>
                                                <div class="col-3">
                                                    {{ $ticket->budget_upload->created_by_employee->name }}</div>
                                            @endif
                                        </div>
                                        <div class="col-12 table-responsive">
                                            <table class="table table-bordered table-sm small">
                                                <thead>
                                                    <tr>
                                                        @if ($ticket->budget_upload->type == 'inventory')
                                                            {{-- Inventory --}}
                                                            <th>Kode</th>
                                                            <th>Keterangan</th>
                                                            <th>Qty</th>
                                                            <th>Value</th>
                                                            <th>Amount</th>
                                                            <th>Pending</th>
                                                            <th>Terpakai</th>
                                                            <th>Sisa</th>
                                                        @elseif($ticket->budget_upload->type == 'assumption')
                                                            {{-- Assumption --}}
                                                            <th>Kode</th>
                                                            <th>Kategori</th>
                                                            <th>Nama</th>
                                                            <th>Qty</th>
                                                            <th>Value</th>
                                                            <th>Amount</th>
                                                            <th>Pending</th>
                                                            <th>Terpakai</th>
                                                            <th>Sisa</th>
                                                        @else
                                                            {{-- HO --}}
                                                    <tr>
                                                        <th rowspan="2">Kode</th>
                                                        <th rowspan="2">Kategori</th>
                                                        <th rowspan="2">Nama</th>
                                                        @for ($i = 1; $i <= 12; $i++)
                                                            <th colspan="5">
                                                                {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                                            </th>
                                                        @endfor
                                                    </tr>
                                                    <tr>
                                                        @for ($i = 1; $i <= 12; $i++)
                                                            <th>Qty</th>
                                                            <th>Value</th>
                                                            <th>Pending</th>
                                                            <th>Terpakai</th>
                                                            <th>Sisa</th>
                                                        @endfor
                                                    </tr>
                @endif
                </tr>
                </thead>
                <tbody>
                    @foreach ($ticket->budget_upload->budget_detail as $budget_detail)
                        <tr>
                            @if ($budget_detail->budget_upload->type == 'inventory')
                                {{-- Inventory --}}
                                <td>{{ $budget_detail->code }}</td>
                                <td>{{ $budget_detail->keterangan }}</td>
                                <td>{{ $budget_detail->qty }}</td>
                                <td class="rupiah">{{ $budget_detail->value }}</td>
                                <td class="rupiah">{{ $budget_detail->qty * $budget_detail->value }}</td>
                                <td>{{ $budget_detail->pending_quota }}</td>
                                <td>{{ $budget_detail->used_quota }}</td>
                                <td>{{ $budget_detail->qty - $budget_detail->pending_quota - $budget_detail->used_quota }}
                                </td>
                            @elseif ($budget_detail->budget_upload->type == 'assumption')
                                {{-- Assumption --}}
                                <td>{{ $budget_detail->code }}</td>
                                <td>{{ $budget_detail->group }}</td>
                                <td>{{ $budget_detail->name }}</td>
                                <td>{{ $budget_detail->qty }}</td>
                                <td class="rupiah">{{ $budget_detail->value }}</td>
                                <td class="rupiah">{{ $budget_detail->qty * $budget_detail->value }}</td>
                                <td>{{ $budget_detail->pending_quota }}</td>
                                <td>{{ $budget_detail->used_quota }}</td>
                                <td>{{ $budget_detail->qty - $budget_detail->pending_quota - $budget_detail->used_quota }}
                                </td>
                            @else
                                <td class="text-nowrap">{{ $budget_detail->code }}</td>
                                <td class="text-nowrap">{{ $budget_detail->category }}</td>
                                <td class="text-nowrap">{{ $budget_detail->name }}</td>
                                @for ($i = 1; $i <= 12; $i++)
                                    <td class="text-nowrap">{{ $budget_detail->getQty($i) }}</td>
                                    <td class="rupiah text-nowrap">{{ $budget_detail->getValue($i) }}</td>
                                    <td class="text-nowrap">{{ $budget_detail->getPendingQuota($i) }}</td>
                                    <td class="text-nowrap">{{ $budget_detail->getUsedQuota($i) }}</td>
                                    <td class="text-nowrap">
                                        {{ $budget_detail->getQty($i) - $budget_detail->getPendingQuota($i) - $budget_detail->getUsedQuota($i) }}
                                    </td>
                                @endfor
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
    </div>
    </div>
    </div>
    </div>
    @endif
    </div>
    @if ($ticket->request_type == 5 || $ticket->request_type == 6)
        <div class="col-sm-6">
            <h4 class="m-0 font-weight-bold">
                <a class="uploaded_file small"
                    onclick='window.open("/storage/{{ $ticket->ba_additional_ticketing }}")'>Tampilkan
                    BA Percepatan</a>
            </h4>
        </div>
    @endif
    <div class="col-md-12 box p-3 mt-3">
        <h5 class="font-weight-bold ">Daftar Barang</h5>
        <div class="table-responsive">
            <table class="table table-bordered table_item">
                <thead>
                    <tr>
                        <th>Nama Item</th>
                        <th>Merk</th>
                        <th>Type</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Attachment</th>
                        @if ($ticket->status != 7 && $ticket->status != -1)
                            <th>Status</th>
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ticket->ticket_item as $item)
                        <tr class="@if ($item->isCancelled) table-danger @endif">
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->brand }}</td>
                            <td>{{ $item->type }}</td>
                            <td class="rupiah_text">{{ $item->price }}</td>
                            <td>{{ $item->count }} {{ $item->budget_pricing->uom ?? '' }}</td>
                            <td class="rupiah_text">{{ $item->price * $item->count }}</td>
                            <td>
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
                                                    <td width="100%" class="tdbreak"><a
                                                            href="/storage/{{ $attachment->path }}"
                                                            download="{{ $attachment->name }}">{{ $naming }}</a>
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
                                                    <td width="100%" class="tdbreak">
                                                        <a
                                                            onclick='window.open("/storage/{{ $requirement->path }}")'>{{ $requirement->file_completement->name }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                                @if ($ticket->status > 5)
                                    @if ($item->lpb_filepath)
                                        <div class="form-check">
                                            <input class="form-check-input cheker" type="checkbox" id="lpb1"
                                                name="lpb1" value="something">
                                            <a class="uploaded_file small"
                                                onclick='window.open("/storage/{{ $item->lpb_filepath }}")'>Tampilkan
                                                dokumen LPB</a><br>
                                        </div>
                                    @endif
                                    @if ($item->invoice_filepath)
                                        <div class="form-check">
                                            <input class="form-check-input cheker" type="checkbox" id="invoice1"
                                                name="invoice1" value="something">
                                            <a class="uploaded_file small"
                                                onclick='window.open("/storage/{{ $item->invoice_filepath }}")'>Tampilkan
                                                dokumen Invoice</a><br>
                                        </div>
                                    @endif
                                    <br>
                                    <div>
                                        <button type="button" class="btn btn-primary" name="revision_lpb_invoice"
                                            id="revision_lpb_invoice" onclick="revisionDocument({{ $item->id }})"
                                            disabled>Revisi</button>
                                    </div>
                                @endif
                            </td>
                            @if ($ticket->status != 7 && $ticket->status != -1)
                                <td>
                                    @if ($item->isCancelled)
                                        Item telah dihapus oleh <b>{{ $item->cancelled_by_employee()->name }}</b><br>
                                        Alasan : {{ $item->cancel_reason }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $canReceive = true;
                                        $isCustomSetting = false;
                                        // pastikan status sudah melewati bidding dan pr / sudah pada proses nomor ke 6
                                        if ($ticket->status < 6 || count($ticket->po) == 0) {
                                            $canReceive = false;
                                        }

                                        if (($ticket->status == 6) & ($ticket->custom_settings != null) && $ticket->item_type == 4) {
                                            $canReceive = true;
                                        }

                                        if ($ticket->custom_settings != null) {
                                            $isCustomSetting = true;
                                            $custom_settings = json_decode($ticket->custom_settings);
                                            $received_files = $custom_settings->received_file_name;
                                        }
                                    @endphp
                                    @if ($canReceive)
                                        @if (!$item->isFinished && $item->isCancelled == false)
                                            <form action="/uploadconfirmationfile" method="post"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="ticket_item_id" value="{{ $item->id }}">
                                                @if ($isCustomSetting)
                                                    @foreach ($received_files as $file)
                                                        <input type="hidden" name="filename[]"
                                                            value="{{ $file }}">
                                                        <div class="form-group">
                                                            <label class="required_field">Pilih File
                                                                {{ $file }}</label>
                                                            <input type="file"
                                                                class="form-control-file form-control-sm validatefilesize"
                                                                name="file[]" required>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    @if ($item->lpb_filepath == '')
                                                        <div class="form-group">
                                                            <label>Pilih File LPB</label>
                                                            <input type="file"
                                                                class="form-control-file form-control-sm validatefilesize"
                                                                name="lpb">
                                                        </div>
                                                    @endif
                                                    @if ($item->invoice_filepath == '')
                                                        <div class="form-group">
                                                            <label>Pilih File Invoice</label>
                                                            <input type="file"
                                                                class="form-control-file form-control-sm validatefilesize"
                                                                name="invoice">
                                                        </div>
                                                    @endif
                                                @endif
                                                <div class="d-flex">
                                                    <button type="submit"
                                                        class="btn btn-success btn-sm mr-2">Confirm</button>
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $custom_settings = json_decode($ticket->custom_settings);
        @endphp

        <div class="row">
            <div class="col-md-3">
                <b>Bidding</b>
                @if ($ticket->bidding->count() < 1)
                    <div>-</div>
                @endif
                <div class="small">
                    @foreach ($ticket->bidding as $bidding)
                        <a onclick='window.open("/bidding/printview/{{ \Crypt::encryptString($bidding->id) }}")'>Seleksi
                            Vendor {{ $bidding->product_name }}</a>
                        @if ($bidding->signed_filepath)
                            <br>
                            <a onclick='window.open("/storage/{{ $bidding->signed_filepath }}")'>File Penawaran
                                {{ $bidding->product_name }}</a>
                        @endif
                        @if ($bidding->cop_filepath)
                            <br>
                            <a onclick='window.open("/storage/{{ $bidding->cop_filepath }}")'>Attachment File COP
                                {{ $bidding->product_name }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-md-3">
                <b>PR</b>
                <div class="small">
                    @if (in_array($ticket->status, [3, 4, 5]))
                        <a onclick="window.open('/pr/{{ $ticket->code }}')">Link PR Manual</a><br>
                    @endif
                    @if ($ticket->pr && $ticket->status > 4)
                        <a onclick="window.open('/printPR/{{ $ticket->code }}')">Printout PR Manual</a>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <b>PO</b>
                @if ($ticket->po->count() < 1)
                    <div>-</div>
                @endif
                <div>
                    @foreach ($ticket->po as $po)
                        @if ($po->external_signed_filepath != null || $po->internal_signed_filepath != null)
                            <a
                                onclick='window.open("/storage/{{ $po->external_signed_filepath ?? $po->internal_signed_filepath }}")'>
                                {{ $po->no_po_sap }} (last updated at : {{ $po->updated_at->format('d-m-Y H:i:s') }})
                            </a><br>
                        @endif
                    @endforeach
                </div>

                <div class="d-flex">
                    <button type="button" onclick="issuePO()" class="btn btn-warning btn-sm">Laporkan Kesalahan
                        PO</button>
                </div>
            </div>
            <div class="col-md-3">
                <b>Issue PO</b>
                @php
                    $issued_po = [];
                    foreach ($ticket->po as $po) {
                        if ($po->issue) {
                            array_push($issued_po, $po->issue);
                        }
                    }
                    $issued_po = collect($issued_po);
                @endphp
                @if ($issued_po->count() < 1)
                    <div>-</div>
                @endif
                <div>
                    @foreach ($issued_po as $issue)
                        <a onclick='window.open("/storage/{{ $issue->ba_file }}")'>
                            BA ISSUE PO {{ $issue->po_number }} (issued on : {{ $po->created_at->format('d-m-Y') }})
                        </a>
                        <br>
                    @endforeach
                </div>
            </div>
        </div>
        <hr>
        <h5 class="font-weight-bold">Validasi Kelengkapan Berkas</h5>
        <div class="row px-2">
            @php
                $count_file = 0;
                foreach ($ticket->ticket_item as $item) {
                    foreach ($item->ticket_item_file_requirement as $requirement) {
                        $count_file++;
                    }
                }
            @endphp

            @php
                $count_file2 = 0;
                foreach ($ticket->ticket_item as $item) {
                    foreach ($item->ticket_item_attachment as $attachment) {
                        $count_file2++;
                    }
                }
            @endphp
            @if ($count_file == 0 && $count_file2 == 0)
                Tidak ada berkas kelengkapan
            @else
                @foreach ($ticket->ticket_item as $item)
                    <div class="col-md-6">
                        <h5>{{ $item->name }}</h5><br>
                        <table class="table table-sm">
                            <tbody>
                                @foreach ($item->ticket_item_attachment as $attachment)
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
                                    <tr>
                                        @if ($ticket->salespoint_id == 251 || $ticket->salespoint_id == 252)
                                            <td width="20%">{{ str_replace('Area', 'HO', $naming) }}</td>
                                        @else
                                            <td width="20%">{{ $naming }}</td>
                                        @endif
                                        <td width="30%" class="tdbreak"><a
                                                onclick='window.open("/storage/{{ $attachment->path }}")'>tampilkan
                                                attachment</a></td>

                                        @if ($attachment->status == 0 && $ticket->status == 7 && $ticket->item_type == 4 && $ticket->custom_settings)
                                            <td colspan="2">
                                                <span class="text-success">
                                                    Pengadaan Selesai
                                                </span><br>
                                                @if ($attachment->revised_by != null)
                                                    Revised by : <b>{{ $attachment->revised_by_employee()->name }}</b>
                                                @endif
                                            </td>
                                        @elseif ($attachment->status == 0)
                                            <td colspan="2">
                                                <span class="text-warning">
                                                    Menunggu Proses Validasi Data
                                                </span><br>
                                                @if ($attachment->revised_by != null)
                                                    Revised by : <b>{{ $attachment->revised_by_employee()->name }}</b>
                                                @endif
                                            </td>
                                        @endif
                                        @if ($attachment->status == 1)
                                            <td colspan="2">
                                                <b class="text-success">Confirmed</b><br>
                                                {{ $attachment->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                Confirmed by <b>{{ $attachment->confirmed_by_employee()->name }}</b>
                                            </td>
                                        @endif
                                        @if ($attachment->status == -1)
                                            <td>
                                                <b class="text-danger">Rejected</b><br>
                                                {{ $attachment->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                by <b>{{ $attachment->rejected_by_employee()->name }}</b><br>
                                                Alasan : {{ $attachment->reject_notes }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm mt-2"
                                                    onclick="selectfile(this)">Pilih File Perbaikan</button>
                                                <input class="inputFile" type="file" style="display:none;">
                                                <div class="display_field mt-1"></div>
                                                <button type="button" class="btn btn-primary btn-sm mt-2"
                                                    onclick="uploadfile({{ $attachment->id }},'attachment',this)">Upload
                                                    File Perbaikan</button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @foreach ($item->ticket_item_file_requirement as $requirement)
                                    <tr>
                                        @if ($ticket->salespoint_id == 251 || $ticket->salespoint_id == 252)
                                            <td width="20%">
                                                {{ str_replace('Area', 'HO', $requirement->file_completement->name) }}</td>
                                        @else
                                            <td width="20%">{{ $requirement->file_completement->name }}</td>
                                        @endif
                                        <td width="30%" class="tdbreak">
                                            <a onclick='window.open("/storage/{{ $requirement->path }}")'>tampilkan
                                                attachment</a>
                                        </td>
                                        @if ($requirement->status == 0)
                                            <td colspan="2">
                                                <span class="text-warning">
                                                    Menunggu Proses Validasi Data
                                                </span><br>
                                                @if ($requirement->revised_by != null)
                                                    Revised by :
                                                    <b>{{ $requirement->revised_by_employee()->name }}</b>
                                                @endif
                                            </td>
                                        @endif
                                        @if ($requirement->status == 1)
                                            <td colspan="2">
                                                <b class="text-success">Confirmed</b><br>
                                                {{ $requirement->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                Confirmed by <b>{{ $requirement->confirmed_by_employee()->name }}</b>
                                            </td>
                                        @endif
                                        @if ($requirement->status == -1)
                                            <td>
                                                <b class="text-danger">Rejected</b><br>
                                                {{ $requirement->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                                by <b>{{ $requirement->rejected_by_employee()->name }}</b><br>
                                                Alasan : {{ $requirement->reject_notes }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm mt-2"
                                                    onclick="selectfile(this)">Pilih File Perbaikan</button>
                                                <input class="inputFile" type="file" style="display:none;">
                                                <div class="display_field mt-1"></div>
                                                <button type="button" class="btn btn-primary btn-sm mt-2"
                                                    onclick="uploadfile({{ $requirement->id }},'file',this)">Upload
                                                    File
                                                    Perbaikan</button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    @if (
        $item->nilai_budget_over_budget != null &&
            $item->nilai_ajuan_over_budget != null &&
            $item->selisih_over_budget != null &&
            $ticket->over_budget_reason != null)
        <div class="col-md-12 box p-3 mt-3">
            <h5 class="font-weight-bold ">Daftar Over Budget Barang</h5>
            <div class="table-responsive">
                <table class="table table-bordered table_item">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Merk</th>
                            <th>Type</th>
                            <th>Nilai Budget</th>
                            <th>Nilai Ajuan</th>
                            <th>Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket->ticket_item as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->brand }}</td>
                                <td>{{ $item->type }}</td>
                                <td class="rupiah_text">{{ $item->nilai_budget_over_budget }}</td>
                                <td class="rupiah_text">{{ $item->nilai_ajuan_over_budget }}</td>
                                <td class="rupiah_text">{{ $item->selisih_over_budget }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <td class="text-justify" colspan="6">
                    <h5>Reason : {{ $ticket->over_budget_reason }}</h5>
                </td>
            </div>
        </div>
    @endif

    @php
        $ticket->ticket_item = $item;
    @endphp

    @if ($ticket->status != 7 && $item->file_missing_filepath == null && $item->file_missing_status != 1
        && !str_contains($ticket->reason, 'End Kontrak PEST Control'))
        <div class="form-group">
            <form action="/uploadmissingfile/{{ $item->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-3">
                    <h5 class="font-weight-bold">Upload Kekurangan Berkas</h5>
                    <div class="form-group">
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="file_kekurangan_berkas" id="file_kekurangan_berkas" required>
                        <br>
                        <label class="required_field">Nama Berkas</label>
                        <input type="text-area" class="form-control" name="name_file" required>
                        <br>
                        <label class="required_field">Alasan</label>
                        <input type="text-area" class="form-control" name="reason" required>
                        <button type="submit" class="btn btn-primary mt-3" name="submit_file_kekurangan_berkas"
                            id="submit_file_kekurangan_berkas">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    @if ($item->file_missing_filepath)
        <div class="form-group">
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
                                <a onclick='window.open("/storage/{{ $item->file_missing_filepath }}")'>tampilkan
                                    attachment</a>
                            </td>
                            @if ($item->file_missing_status == 0)
                                <td>
                                    <label class="text-warning">
                                        Menunggu Proses Validasi Data
                                    </label>
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
                                <td>
                                    <form action="/revisionmissingfile/{{ $item->id }}" method="post"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label class="required_field">Pilih File Perbaikan</label>
                                            <input type="file"
                                                class="form-control-file form-control-sm validatefilesize"
                                                name="revision_missing_file"required>
                                            <button type="submit" class="btn btn-primary mt-3"
                                                name="submit_revision_missing_file">Upload file perbaikan</button>
                                        </div>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->agreement_filepath == null &&
            $ticket->tor_filepath == null &&
            $ticket->sph_filepath == null &&
            $ticket->status == 6 &&
            count($ticket->po) != 0)
        <div class="form-group">
            <form action="/uploadfilelegal/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-3">
                    <h5 class="font-weight-bold">Upload Legal</h5>
                    <div class="form-check mt-3">
                        <input type="hidden" name="over_platform" value="0">
                        <input class="form-check-input" type="checkbox" id="over_platform" name="over_platform"
                            value="1">
                        <label>Apakah Over Plafon ?</label>
                    </div>
                    <div class="form-group">
                        <label class="required_field">Pilih File Perjanjian</label>
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="file_perjanjian_legal" id="file_perjanjian_legal" required>
                    </div>
                    <div class="form-group">
                        <label class="required_field">Pilih File TOR</label>
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="file_tor_legal" id="file_tor_legal" required>
                    </div>
                    <div class="form-group">
                        <label class="required_field">Pilih File SPH</label>
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="file_sph_legal" id="file_sph_legal" required>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" name="submit_over_platform"
                        id="submit_over_platform" onclick="confirmationOverPlafon()">Submit</button>
                    <button type="submit" class="btn btn-primary mt-3" name="submit_no_over_platform"
                        id="submit_no_over_platform">Submit</button>
                </div>
            </form>
        </div>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->agreement_filepath &&
            $ticket->tor_filepath &&
            $ticket->sph_filepath)
        <div class="col-md-3 box p-3 mt-3 ml-4">
            <h5 class="font-weight-bold">File Legal</h5>
            <div class="form-group">
                <label>File Agreement</label>
                <div>
                    <a onclick='window.open("/storage/{{ $ticket->agreement_filepath }}")'>tampilkan attachment</a>
                    @if ($ticket->agreement_filepath_status == 0)
                        <div>
                            <b class="text-warning">
                                Menunggu Proses Validasi Data
                            </b>
                        </div>

                        <form action="/ticketing/approve-agreement-cop/{{ $ticket->id }}" method="post"
                            enctype="multipart/form-data" id="approve_agreement_filepath_button">
                            @csrf
                        </form>

                        @if (Auth::user()->id == 1 ||
                                Auth::user()->id == 115 ||
                                Auth::user()->id == 117 ||
                                Auth::user()->id == 197 ||
                                Auth::user()->id == 116)
                            <button type="button" class="btn btn-danger ml-2"
                                onclick="rejectAgreementCOP()">Reject</button>
                            <button type="submit" class="btn btn-primary ml-2" id="approve_agreement_filepath_button"
                                form="approve_agreement_filepath_button">Approve</button>
                        @endif
                    @endif
                    @if ($ticket->agreement_filepath_status == 1)
                        <div>
                            <b class="text-success">
                                Lanjut
                            </b>
                        </div>
                    @endif
                    @if ($ticket->agreement_filepath_status == -1)
                        <b class="text-danger">
                            Tidak Lanjut
                            <br>
                            Alasan : {{ $ticket->agreement_filepath_reject_notes }}
                        </b>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label>File TOR</label>
                <div>
                    <a onclick='window.open("/storage/{{ $ticket->tor_filepath }}")'>tampilkan attachment</a>
                    @if ($ticket->tor_filepath_status == 0)
                        <div>
                            <b class="text-warning">
                                Menunggu Proses Validasi Data
                            </b>
                        </div>

                        <form action="/ticketing/approve-tor-cop/{{ $ticket->id }}" method="post"
                            enctype="multipart/form-data" id="approve_tor_filepath_button">
                            @csrf
                        </form>

                        @if (Auth::user()->id == 1 ||
                                Auth::user()->id == 115 ||
                                Auth::user()->id == 117 ||
                                Auth::user()->id == 197 ||
                                Auth::user()->id == 116)
                            <button type="button" class="btn btn-danger ml-2" onclick="rejectTorCOP()">Reject</button>
                            <button type="submit" class="btn btn-primary ml-2" id="approve_tor_filepath_button"
                                form="approve_tor_filepath_button">Approve</button>
                        @endif
                    @endif
                    @if ($ticket->tor_filepath_status == 1)
                        <div>
                            <b class="text-success">
                                Lanjut
                            </b>
                        </div>
                    @endif
                    @if ($ticket->tor_filepath_status == -1)
                        <div>
                            <b class="text-danger">
                                Tidak Lanjut
                                <br>
                                Alasan : {{ $ticket->tor_filepath_reject_notes }}
                            </b>
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label>File SPH</label>
                <div>
                    <a onclick='window.open("/storage/{{ $ticket->sph_filepath }}")'>tampilkan attachment</a>
                    @if ($ticket->sph_filepath_status == 0)
                        <div>
                            <b class="text-warning">
                                Menunggu Proses Validasi Data
                            </b>
                        </div>

                        <form action="/ticketing/approve-sph-cop/{{ $ticket->id }}" method="post"
                            enctype="multipart/form-data" id="approve_sph_filepath_button">
                            @csrf
                        </form>

                        @if (Auth::user()->id == 1 ||
                                Auth::user()->id == 115 ||
                                Auth::user()->id == 117 ||
                                Auth::user()->id == 197 ||
                                Auth::user()->id == 116)
                            <button type="button" class="btn btn-danger ml-2" onclick="rejectSphCOP()">Reject</button>
                            <button type="submit" class="btn btn-primary ml-2" id="approve_sph_filepath_button"
                                form="approve_sph_filepath_button">Approve</button>
                        @endif
                    @endif
                    @if ($ticket->sph_filepath_status == 1)
                        <div>
                            <b class="text-success">
                                Lanjut
                            </b>
                        </div>
                    @endif
                    @if ($ticket->sph_filepath_status == -1)
                        <div>
                            <b class="text-danger">
                                Tidak Lanjut
                                <br>
                                Alasan : {{ $ticket->sph_filepath_reject_notes }}
                            </b>
                        </div>
                    @endif
                </div>
            </div>
            @if ($ticket->user_agreement_filepath)
                <div class="form-group">
                    <label>File Perjanjian User</label>
                    <div>
                        <a onclick='window.open("/storage/{{ $ticket->user_agreement_filepath }}")'>tampilkan
                            attachment</a>

                        @if ($ticket->user_agreement_filepath_status == 0)
                            <div>
                                <b class="text-warning">
                                    Menunggu Proses Validasi Data
                                </b>
                            </div>

                            <form action="/ticketing/approve-user-agreement-cop/{{ $ticket->id }}" method="post"
                                enctype="multipart/form-data" id="approve_user_agreement_filepath_button">
                                @csrf
                            </form>

                            @if (Auth::user()->id == 1 ||
                                    Auth::user()->id == 115 ||
                                    Auth::user()->id == 117 ||
                                    Auth::user()->id == 197 ||
                                    Auth::user()->id == 116)
                                <button type="button" class="btn btn-danger ml-2"
                                    onclick="rejectUserAgreementCOP()">Reject</button>
                                <button type="submit" class="btn btn-primary ml-2"
                                    id="approve_user_agreement_filepath_button"
                                    form="approve_user_agreement_filepath_button">Approve</button>
                            @endif
                        @endif
                        @if ($ticket->user_agreement_filepath_status == 1)
                            <div>
                                <b class="text-success">
                                    Lanjut
                                </b>
                            </div>
                        @endif
                        @if ($ticket->user_agreement_filepath_status == -1)
                            <div>
                                <b class="text-danger">
                                    Tidak Lanjut
                                    <br>
                                    Alasan : {{ $ticket->user_agreement_filepath_reject_notes }}
                                </b>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->over_plafon_filepath)
        <div class="col-md-2 box p-3 mt-3 ml-4">
            <h5 class="font-weight-bold">File Bukti Transfer Over Plafon</h5>
            <div class="form-group">
                <div>
                    <a onclick='window.open("/storage/{{ $ticket->over_plafon_filepath }}")'>tampilkan
                        attachment</a>
                    @if ($ticket->over_plafon_status == 0)
                        <span class="text-warning ml-2">
                            Menunggu Proses Validasi Data
                        </span>

                        <form action="/ticketing/approve-over-plafon/{{ $ticket->id }}" method="post"
                            enctype="multipart/form-data" id="approve_button">
                            @csrf
                        </form>

                        @if (Auth::user()->id == 1 ||
                                Auth::user()->id == 115 ||
                                Auth::user()->id == 117 ||
                                Auth::user()->id == 197 ||
                                Auth::user()->id == 116)
                            <br>
                            <button type="button" class="btn btn-danger ml-2"
                                onclick="rejectOverPlafon()">Reject</button>
                            <button type="submit" class="btn btn-primary ml-2" id="approve_button"
                                form="approve_button">Approve</button>
                        @endif
                    @elseif ($ticket->over_plafon_status == -1)
                        <span class="text-danger ml-2">
                            Rejected
                        </span>
                        <div>
                            Rejected Reason : {{ $ticket->over_plafon_reject_notes }}
                        </div>
                        <form action="/ticketing/revision-over-plafon/{{ $ticket->id }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            <br>
                            <div class="form-group">
                                <label class="required_field">Pilih File Perbaikan</label>
                                <input type="file" class="form-control-file form-control-sm validatefilesize"
                                    name="file_over_plafon_revision"required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3" name="submit_over_plafon_revision">Upload
                                file perbaikan</button>
                        </form>
                    @elseif ($ticket->over_plafon_status == 1)
                        <span class="text-success ml-2">
                            Approved
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <br>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->bastk_cop_filepath &&
            $ticket->cop_plate)
        <div class="col-md-2 box p-3 mt-3 ml-4">
            <h5 class="font-weight-bold">File BASTK</h5>
            <div class="form-group">
                <div>
                    <a onclick='window.open("/storage/{{ $ticket->bastk_cop_filepath }}")'>tampilkan
                        attachment</a>
                    <br><br><br><br><br><br>
                </div>
            </div>
            <form action="/reuploadbastkfilecop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <h6 class="font-weight-bold required_field">Upload Ulang File BASTK COP</h6>
                <div class="form-group">
                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                        name="upload_ulang_file_bastk_cop" id="upload_ulang_file_bastk_cop" required>
                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </div>
            </form>
        </div>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->agreement_filepath &&
            $ticket->tor_filepath &&
            $ticket->sph_filepath)
        <div class="col-md-4 box p-3 mt-3 ml-4">
            <h5 class="font-weight-bold">Revisi File Legal COP</h5>
            <h6>(File Dapat Di Upload Ulang ketika File Sudah di Approve / Reject)</h6>
            <br>

            @if ($ticket->agreement_filepath_status != 0)
                <form action="/reuploadagreementfilecop/{{ $ticket->id }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <h6 class="font-weight-bold required_field"> - Upload Ulang File Agreement</h6>
                    <div class="form-group">
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="upload_ulang_file_agreement_cop" id="upload_ulang_file_agreement_cop" required>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </div>
                </form>
            @endif

            @if ($ticket->tor_filepath_status != 0)
                <form action="/reuploadtorfilecop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <h6 class="font-weight-bold required_field"> - Upload Ulang File TOR</h6>
                    <div class="form-group">
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="upload_ulang_file_tor_cop" id="upload_ulang_file_tor_cop" required>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </div>
                </form>
            @endif

            @if ($ticket->sph_filepath_status != 0)
                <form action="/reuploadsphfilecop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <h6 class="font-weight-bold required_field"> - Upload Ulang File SPH</h6>
                    <div class="form-group">
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="upload_ulang_file_sph_cop" id="upload_ulang_file_sph_cop" required>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </div>
                </form>
            @endif

            @if ($ticket->user_agreement_filepath_status != 0)
                <form action="/reuploaduseragreementfilecop/{{ $ticket->id }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <h6 class="font-weight-bold required_field"> - Upload Ulang File Perjanjian User</h6>
                    <div class="form-group">
                        <input type="file" class="form-control-file form-control-sm validatefilesize"
                            name="upload_ulang_file_user_agreement_cop" id="upload_ulang_file_user_agreement_cop"
                            required>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->agreement_filepath_status == 1 &&
            $ticket->tor_filepath_status == 1 &&
            $ticket->sph_filepath_status == 1 &&
            $ticket->user_agreement_filepath == null)
        <form action="/useruploadfileaggrement/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-12 box p-3 mt-3 ml-4">
                <h5 class="font-weight-bold">Upload File Perjanjian User</h5>
                <div class="form-group">
                    <label class="required_field">Pilih File Perjanjian User</label>
                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                        name="file_perjanjian_user" id="file_perjanjian_user" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3" name="submit_file_perjanjian_user"
                    id="submit_file_perjanjian_user">Submit</button>
            </div>
        </form>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->agreement_filepath != null &&
            $ticket->tor_filepath != null &&
            $ticket->sph_filepath != null &&
            $ticket->user_agreement_filepath != null &&
            $ticket->is_over_plafon == 1 &&
            $ticket->over_plafon_filepath == null &&
            $ticket->status == 6)
        <form action="/uploadevidancetransferoverplatform/{{ $ticket->id }}" method="post"
            enctype="multipart/form-data">
            @csrf
            <div class="col-md-12 box p-3 mt-3 ml-4">
                <h6 class="font-weight-bold required_field">Upload Bukti Transfer Over Plafon</h6>
                <input type="file" class="form-control-file form-control-sm validatefilesize"
                    name="file_over_platform" id="file_over_platform" required>
                <button type="submit" class="btn btn-primary mt-3">Submit</button>
            </div>
        </form>
    @endif

    @if (
        $ticket->agreement_filepath_status == 1 &&
            $ticket->sph_filepath_status == 1 &&
            $ticket->tor_filepath_status == 1 &&
            $ticket->user_agreement_filepath_status == 1 &&
            $ticket->bastk_cop_filepath == null)

        @if ($ticket->is_over_plafon == 1 && $ticket->over_plafon_status != 1)
            <form action="/uploadbastkcop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-3">
                    <h6 class="font-weight-bold">Upload BASTK</h6>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="required_field">Pilih Jenis Kendaraan</label>
                                <select class="form-control select2 armada_type_id" name="armada_type_id" required>
                                    <option data-niaga="" value="">-- Pilih Jenis Kendaraan --</option>
                                    @foreach ($armada_types as $type)
                                        <option data-niaga="{{ $type->isNiaga }}" value="{{ $type->id }}">
                                            {{ $type->brand_name }} {{ $type->name }}
                                            ({{ $type->isNiaga() }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nopol</label>
                                <input type="text" class="form-control" name="nopol"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Tahun Kendaraan</label>
                                <input type="number" class="form-control autonumber" min="1970"
                                    value="{{ now()->format('Y') }}" name="vehicle_year"required>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2">
                            <div class="form-group">
                                <label class="required_field">Status</label>
                                <input type="text" class="form-control" value="Booked" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Di Booked Oleh</label>
                                <input type="text" class="form-control" name="booked_by"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Pilih File BASTK</label>
                                <input type="file" class="form-control-file form-control-sm validatefilesize"
                                    name="file_bastk"required>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2 mt-3">
                            <button type="submit" class="btn btn-primary mt-3" disabled>Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            <form action="/uploadarmadatypecop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-4">
                    <span class="h6 font-weight-bold">Tambah Jenis Kendaraan</span>
                    <span class="h6 font-weight-bold text-danger">(Jika Jenis Kendaraan Upload BASTK TIDAK
                        Tersedia)</span>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="required_field">Nama Jenis Kendaraan</label>
                                <input type="text" class="form-control" name="nama_jenis_kendaraan"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nama Merk</label>
                                <input type="text" class="form-control" name="nama_merk"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nama Alias</label>
                                <input type="text" class="form-control" name="nama_alias"required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="required_field">Jenis Niaga</label>
                                <input type="text" class="form-control" value="Non Niaga-COP" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 mt-3">
                            <button type="submit" class="btn btn-primary mt-3" disabled>Tambah Jenis Kendaraan</button>
                        </div>
                    </div>
                </div>
            </form>
            <br>
        @elseif ($ticket->is_over_plafon == 1 && $ticket->over_plafon_status == 1)
            <form action="/uploadbastkcop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-3">
                    <h6 class="font-weight-bold">Upload BASTK</h6>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="required_field">Pilih Jenis Kendaraan</label>
                                <select class="form-control select2 armada_type_id" name="armada_type_id" required>
                                    <option data-niaga="" value="">-- Pilih Jenis Kendaraan --</option>
                                    @foreach ($armada_types as $type)
                                        <option data-niaga="{{ $type->isNiaga }}" value="{{ $type->id }}">
                                            {{ $type->brand_name }} {{ $type->name }}
                                            ({{ $type->isNiaga() }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nopol</label>
                                <input type="text" class="form-control" name="nopol"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Tahun Kendaraan</label>
                                <input type="number" class="form-control autonumber" min="1970"
                                    value="{{ now()->format('Y') }}" name="vehicle_year"required>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2">
                            <div class="form-group">
                                <label class="required_field">Status</label>
                                <input type="text" class="form-control" value="Booked" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Di Booked Oleh</label>
                                <input type="text" class="form-control" name="booked_by"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Pilih File BASTK</label>
                                <input type="file" class="form-control-file form-control-sm validatefilesize"
                                    name="file_bastk"required>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2 mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            <form action="/uploadarmadatypecop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-4">
                    <span class="h6 font-weight-bold">Tambah Jenis Kendaraan</span>
                    <span class="h6 font-weight-bold text-danger">(Jika Jenis Kendaraan Upload BASTK TIDAK
                        Tersedia)</span>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="required_field">Nama Jenis Kendaraan</label>
                                <input type="text" class="form-control" name="nama_jenis_kendaraan"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nama Merk</label>
                                <input type="text" class="form-control" name="nama_merk"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nama Alias</label>
                                <input type="text" class="form-control" name="nama_alias"required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="required_field">Jenis Niaga</label>
                                <input type="text" class="form-control" value="Non Niaga-COP" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Tambah Jenis Kendaraan</button>
                        </div>
                    </div>
                </div>
            </form>
            <br>
        @else
            <form action="/uploadbastkcop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-3">
                    <h6 class="font-weight-bold">Upload BASTK</h6>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="required_field">Pilih Jenis Kendaraan</label>
                                <select class="form-control select2 armada_type_id" name="armada_type_id" required>
                                    <option data-niaga="" value="">-- Pilih Jenis Kendaraan --</option>
                                    @foreach ($armada_types as $type)
                                        <option data-niaga="{{ $type->isNiaga }}" value="{{ $type->id }}">
                                            {{ $type->brand_name }} {{ $type->name }}
                                            ({{ $type->isNiaga() }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nopol</label>
                                <input type="text" class="form-control" name="nopol"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Tahun Kendaraan</label>
                                <input type="number" class="form-control autonumber" min="1970"
                                    value="{{ now()->format('Y') }}" name="vehicle_year"required>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2">
                            <div class="form-group">
                                <label class="required_field">Status</label>
                                <input type="text" class="form-control" value="Booked" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Di Booked Oleh</label>
                                <input type="text" class="form-control" name="booked_by"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Pilih File BASTK</label>
                                <input type="file" class="form-control-file form-control-sm validatefilesize"
                                    name="file_bastk"required>
                            </div>
                        </div>
                        <div class="col-md-1 ml-2 mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            <form action="/uploadarmadatypecop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12 box p-3 mt-4">
                    <span class="h6 font-weight-bold">Tambah Jenis Kendaraan</span>
                    <span class="h6 font-weight-bold text-danger">(Jika Jenis Kendaraan Upload BASTK TIDAK
                        Tersedia)</span>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="required_field">Nama Jenis Kendaraan</label>
                                <input type="text" class="form-control" name="nama_jenis_kendaraan"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nama Merk</label>
                                <input type="text" class="form-control" name="nama_merk"required>
                            </div>
                        </div>
                        <div class="col-md-2 ml-2">
                            <div class="form-group">
                                <label class="required_field">Nama Alias</label>
                                <input type="text" class="form-control" name="nama_alias"required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="required_field">Jenis Niaga</label>
                                <input type="text" class="form-control" value="Non Niaga-COP" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Tambah Jenis Kendaraan</button>
                        </div>
                    </div>
                </div>
            </form>
            <br>
        @endif
    @endif

    @if (
        $ticket->custom_settings != null &&
            $ticket->status >= 6 &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->show_lpb_cop == 1)
        @php
            $ticket->ticket_item = $item;
        @endphp
        <form action="/ticketing/uploadlpbcop/{{ $item->id }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-12 box p-3 mt-3 ml-4">
                <h5 class="font-weight-bold">Upload LPB COP</h5>
                <div class="form-group ml-1">
                    <h6 class="font-weight-bold">Pilih File LPB COP</h6>
                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                        name="file_lpb_cop"required>
                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </div>
            </div>
        </form>
    @endif

    @if (
        $ticket->custom_settings != null &&
            $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' &&
            $ticket->status >= 6 &&
            $ticket->bastk_cop_filepath != null)
        @php
            $ticket->ticket_item = $item;
        @endphp
        <form action="/ticketing/uploadrevisionlpbcop/{{ $item->id }}" method="post"
            enctype="multipart/form-data">
            @csrf
            <div class="col-md-12 box p-3 mt-3 ml-4">
                <h5 class="font-weight-bold">Revisi LPB COP</h5>
                <div class="form-group ml-1">
                    <h6 class="font-weight-bold">Pilih File LPB COP</h6>
                    <input type="file" class="form-control-file form-control-sm validatefilesize"
                        name="file_revision_lpb_cop"required>
                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </div>
            </div>
        </form>
    @endif

    <div class="col-md-12 box p-3 mt-3">
        <h5 class="font-weight-bold">Daftar Vendor</h5>
        <table class="table table-bordered table_vendor">
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
                            <td>
                                {{ $vendor->name }}
                                @if ($isAddedOnBidding)
                                    <br><small class="text-secondary">*penambahan vendor pada saat proses
                                        bidding</small>
                                @endif
                            </td>
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
                        <td colspan="5" class="text-center">Vendor belum dipilih</td>
                    </tr>
                @endif

            </tbody>
        </table>
        @if ($ticket->ba_vendor_filename != null && $ticket->ba_vendor_filepath != null)
            <b> Berita Acara </b><br>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td width="20%">Berita acara untuk pengajuan dengan satu vendor</td>
                                <td width="30%" class="tdbreak"><a href="/storage/{{ $ticket->ba_vendor_filepath }}"
                                        download="{{ $ticket->ba_vendor_filename }}">tampilkan attachment</a>
                                </td>
                                @if ($ticket->ba_status == 0)
                                    <td colspan="2">
                                        <span class="text-warning">
                                            Menunggu Proses Validasi Data
                                        </span><br>
                                        @if ($ticket->ba_revised_by != null)
                                            Revised by : <b>{{ $ticket->ba_revised_by_employee()->name }}</b>
                                        @endif
                                    </td>
                                @endif
                                @if ($ticket->ba_status == 1)
                                    <td colspan="2">
                                        <b class="text-success">Confirmed</b><br>
                                        {{ $ticket->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                        Confirmed by <b>{{ $ticket->ba_confirmed_by_employee()->name }}</b>
                                    </td>
                                @endif
                                @if ($ticket->ba_status == -1)
                                    <td>
                                        <b class="text-danger">Rejected</b><br>
                                        {{ $ticket->updated_at->translatedFormat('d F Y (H:i)') }}<br>
                                        by <b>{{ $ticket->ba_rejected_by_employee()->name }}</b><br>
                                        Alasan : {{ $ticket->ba_reject_notes }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm mt-2"
                                            onclick="selectfile(this)">Pilih File Perbaikan</button>
                                        <input class="inputFile" type="file" style="display:none;">
                                        <div class="display_field mt-1"></div>
                                        <button type="button" class="btn btn-primary btn-sm mt-2"
                                            onclick="uploadfile({{ $ticket->id }},'vendor',this)">Upload File
                                            Perbaikan</button>
                                    </td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
        <h5 class="mt-3 font-weight-bold">FRI</h5>
        @php
            $isEditFRI = false;
        @endphp
        @include('Operational.fri_form')
    @endif


    @if ($ticket->is_over_budget == 1)
        <br>
        <h5 class="font-weight-bold text-center">Approval Pengadaan</h5>
    @endif

    @if ($ticket->is_cancel_end == 1)
        <div class="d-flex align-items-center justify-content-center text-center">
            @foreach ($ticket->cancel_authorization as $key => $authorization)
                <div class="mr-3">
                    <br>
                    <span class="font-weight-bold">{{ $authorization->employee_name }} --
                        {{ $authorization->employee_position }}</span><br>
                    @if ($authorization->status == 1)
                        <span class="text-success">Approved</span><br>
                        <span
                            class="text-success">{{ $authorization->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                    @endif
                    @if (($ticket->current_cancel_authorization()->id ?? -1) == $authorization->id)
                        <span class="text-warning">Menunggu Approval</span><br>
                    @endif
                    <span>{{ $authorization->as }}</span>
                </div>

                @if ($key != $ticket->cancel_authorization->count() - 1)
                    <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                @endif

                @if ($ticket->is_over_budget == 1)
                    @if ($loop->iteration > 2)
                        @break
                    @endif
                @endif
            @endforeach
        </div>
    @else
        <div class="d-flex align-items-center justify-content-center text-center">
            @foreach ($ticket->ticket_authorization as $key => $authorization)
                <div class="mr-3">
                    <br>
                    <span class="font-weight-bold">{{ $authorization->employee_name }} --
                        {{ $authorization->employee_position }}</span><br>
                    @if ($authorization->status == 1)
                        <span class="text-success">Approved</span><br>
                        <span
                            class="text-success">{{ $authorization->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                    @endif
                    @if (($ticket->current_authorization()->id ?? -1) == $authorization->id)
                        <span class="text-warning">Menunggu Approval</span><br>
                    @endif
                    <span>{{ $authorization->as }}</span>
                </div>

                @if ($key != $ticket->ticket_authorization->count() - 1)
                    <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                @endif

                @if ($ticket->is_over_budget == 1)
                    @if ($loop->iteration > 2)
                        @break
                    @endif
                @endif
            @endforeach
        </div>
    @endif

@if ($ticket->is_over_budget == 1)
    <br>
    <div class="col-md-12 box p-3 mt-3">
        <h5 class="font-weight-bold text-center">Approval Over Budget</h5>
        <br>
        <div class="d-flex align-items-center justify-content-center text-center">
            @foreach ($ticket->ticket_authorization as $key => $authorization)
                @if ($key > 2)
                    <div class="mr-3">
                        <span class="font-weight-bold">{{ $authorization->employee_name }} --
                            {{ $authorization->employee_position }}</span><br>
                        @if ($authorization->status == 1)
                            <span class="text-success">Approved</span><br>
                            <span
                                class="text-success">{{ $authorization->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                        @endif
                        @if (($ticket->current_authorization()->id ?? -1) == $authorization->id)
                            <span class="text-warning">Menunggu Approval</span><br>
                        @endif
                        <span>{{ $authorization->as }}</span>
                    </div>
                    @if ($key != $ticket->ticket_authorization->count() - 1)
                        <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
@endif

<div class="d-flex justify-content-center mt-3 bottom_action">
    @if ($ticket->status == 1)
        @if ($ticket->is_cancel_end == 1)
            @if ($ticket->current_cancel_authorization())
                @if (Auth::user()->id == $ticket->current_cancel_authorization()->employee->id)
                    <button type="button" class="btn btn-danger mr-2" onclick="reject()"
                        id="rejectbutton">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approve()"
                        id="approvebutton">Approve</button>
                @endif
            @endif
        @else
            @if ($ticket->current_authorization())
                @if (Auth::user()->id == $ticket->current_authorization()->employee->id)
                    <button type="button" class="btn btn-danger mr-2" onclick="reject()"
                        id="rejectbutton">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approve()"
                        id="approvebutton">Approve</button>
                @endif
            @endif
        @endif
    @endif
</div>
@if (str_contains($ticket->reason, 'End Kontrak PEST Control') && $ticket->is_cancel_end == 0)
    <center class="mt-2">
        <button type="button" class="btn btn-danger mr-2"
            onclick="cancelEndKontrak('{{ $ticket->salespoint_id }}', '17', 'Cancel End Kontrak')">Batalkan End Kontrak</button>
    </center>
@endif
@if (
    (Auth::user()->id == 1 && $ticket->status != -1 && $ticket->status != 7) ||
        (Auth::user()->id == 115 && $ticket->status != -1 && $ticket->status != 7) ||
        (Auth::user()->id == 117 && $ticket->status != -1 && $ticket->status != 7) ||
        (Auth::user()->id == 197 && $ticket->status != -1 && $ticket->status != 7) ||
        (Auth::user()->id == 116 && $ticket->status != -1 && $ticket->status != 7) ||
        (Auth::user()->id == 717 && $ticket->status != -1 && $ticket->status != 7) ||
        (Auth::user()->id == 118 && $ticket->status != -1 && $ticket->status != 7))

    {{-- ADMIN ONLY --}}
    <center>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#terminateModal">Terminate
            Ticket (Superadmin & Purchasing Only)</button>
    </center>
@endif

@if (
    (Auth::user()->id == 1 && $ticket->status != -1 && $ticket->status != 7 && $ticket->is_over_plafon == 1) ||
        (Auth::user()->id == 115 && $ticket->status != -1 && $ticket->status != 7 && $ticket->is_over_plafon == 1) ||
        (Auth::user()->id == 117 && $ticket->status != -1 && $ticket->status != 7 && $ticket->is_over_plafon == 1) ||
        (Auth::user()->id == 197 && $ticket->status != -1 && $ticket->status != 7 && $ticket->is_over_plafon == 1) ||
        (Auth::user()->id == 116 && $ticket->status != -1 && $ticket->status != 7 && $ticket->is_over_plafon == 1))
    <center>
        <br>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#changeoverplafonmodal">Ubah
            Over
            Plafon</button>
        <br>
    </center>
@endif

@if (Auth::user()->id == 1 && $ticket->status != -1 && $ticket->status != 7)
    {{-- ADMIN ONLY --}}
    <center>
        <form action="/ticketing/showlpbcop/{{ $ticket->id }}" method="post" enctype="multipart/form-data">
            @csrf
            <button type="submit" class="btn btn-primary mt-2">Show LPB COP</button>
        </form>
    </center>
@endif

</div>
<form action="/uploadticketfilerevision" method="post" enctype="multipart/form-data" id="uploadrevisionform">
    @method('patch')
    @csrf
    <div class="input_field"></div>
</form>
<form action="/approveticket" method="post" id="approveform">
    @method('patch')
    @csrf
    <input type="hidden" name="id" value="{{ $ticket->id }}">
    <input type="hidden" name="updated_at" value="{{ $ticket->updated_at }}">
</form>
<form action="/rejectticket" method="post" id="rejectform">
    @method('patch')
    @csrf
    <input type="hidden" name="id" value="{{ $ticket->id }}">
    <input type="hidden" name="updated_at" value="{{ $ticket->updated_at }}">
</form>
<form method="post" id="submitform">
    @csrf
    <div></div>
</form>

<div class="modal fade" id="cancelEndKontrakModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel End Kontrak Pest Control</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/cancelEndKontrakPEST/{{ $ticket->id }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Masukan Alasan Cancel: (Wajib)</label>
                                <input type="text-area" class="form-control" name="reason" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Pilih Matriks Approval</label>
                                <select class="form-control" id="authorization_cancel" name="authorization_id" disabled required>
                                    <option value="">-- Pilih Matriks Approval --</option>
                                </select>
                                <small class="text-danger">*Matriks Approval hanya muncul berdasarkan salespoint masing-masing user</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="issuePOmodal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Laporkan Kesalahan PO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/ticketing/issuePO" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="required_field">Pilih PO yang bermasalah</label>
                        <select class="form-control" name="po_number" id="issuePOselect" required>
                            <option value="">--Pilih PO --</option>
                            @foreach ($ticket->po as $po)
                                <option value="{{ $po->no_po_sap }}">{{ $po->no_po_sap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <div><label class="required_field">Jenis Kesalahan</label></div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="sumInvoice" value="bigger"
                                    required checked> Nilai Invoice > Nilai PO
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required_field">Lampiran BA</label>
                        <input type="file" class="form-control-file" name="ba_file"
                            accept="image/*,application/pdf" required>
                    </div>
                    <div class="form-group">
                        <label class="required_field">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Jelaskan letak kesalahan" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="terminateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/ticketing/terminate" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terminate Ticket (Superadmin & Purchasing Only)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column">
                    <b>Pengadaan tiket {{ $ticket->code }} akan dibatalkan dan tidak dapat dimulai kembali
                        !</b>
                    <div class="form-group mt-3">
                        <textarea class="form-control" name="reason" rows="5" style="resize: none"
                            placeholder="Masukan Alasan (wajib)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Terminate</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="changeoverplafonmodal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/ticketing/changeoverplafon/{{ $ticket->id }}" method="post"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Over Plafon</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column">
                    <b>Tindakan ini akan membuat status tiket {{ $ticket->code }} menjadi TIDAK over
                        plafon</b>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Change</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="revisionDocument" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Revisi Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/revisionconfirmationfile" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ticket_item_id" value="" id="upload_confirmation_file">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group" name="js_lpb" id="js_lpb" style="display: none;">
                                <label>Pilih File LPB</label>
                                <input type="file" class="form-control" name="lpb">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group" name="js_invoice" id="js_invoice" style="display: none;">
                                <label>Pilih File Invoice</label>
                                <input type="file" class="form-control" name="invoice">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="overPlafon" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Warning</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    onclick="changeButton()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <h5 class="font-weight-bold text-center">Pilihan yg anda pilih adalah OVER PLAFON</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectOverPlafonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Dokumen Over Plafon</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/ticketing/rejectoverplafon/{{ $ticket->id }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Masukan Alasan : (Wajib)</label>
                                <input type="text-area" class="form-control" name="reason" required>
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

<div class="modal fade" id="rejectAgreementCOPModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject File Agreement COP (Legal)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/ticketing/reject-agreement-cop/{{ $ticket->id }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Masukan Alasan : (Wajib)</label>
                                <input type="text-area" class="form-control" name="reason" required>
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

<div class="modal fade" id="rejectTorCOPModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject File TOR COP (Legal)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/ticketing/reject-tor-cop/{{ $ticket->id }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Masukan Alasan : (Wajib)</label>
                                <input type="text-area" class="form-control" name="reason" required>
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

<div class="modal fade" id="rejectSphCOPModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject File SPH COP (Legal)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/ticketing/reject-sph-cop/{{ $ticket->id }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Masukan Alasan : (Wajib)</label>
                                <input type="text-area" class="form-control" name="reason" required>
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

<div class="modal fade" id="rejectUserAgreementCOPModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject File Perjanjian User COP (Legal)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/ticketing/reject-user-agreement-cop/{{ $ticket->id }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">Masukan Alasan : (Wajib)</label>
                                <input type="text-area" class="form-control" name="reason" required>
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
<script>
    $('#over_platform').change(function() {
        if ($(this).is(':checked')) {
            $("#submit_no_over_platform").hide();
            $("#submit_over_platform").show();
        } else {
            $("#submit_no_over_platform").show();
            $("#submit_over_platform").hide();

        }
    })

    function confirmationOverPlafon() {
        if ($('#file_perjanjian_legal').get(0).files.length === 0) {
            alert("File Perjanjian belum di Pilih");
        } else if ($('#file_tor_legal').get(0).files.length === 0) {
            alert("File TOR belum di Pilih");
        } else if ($('#file_sph_legal').get(0).files.length === 0) {
            alert("File SPH belum di Pilih");
        } else if ($('#over_platform').is(':checked')) {
            $('#overPlafon').modal('show');
        }
    }

    function changeButton() {
        $("#submit_no_over_platform").show();
        $("#submit_over_platform").hide();
    }

    function rejectOverPlafon() {
        $('#rejectOverPlafonModal').modal('show');
    }

    function cancelEndKontrak(salespoint_id, form_type, notes) {
        $('#cancelEndKontrakModal').modal('show');
        $('#authorization_cancel').prop('disabled', true);
        $.ajax({
                type: "get",
                url: '/getAuthorization?salespoint_id=' + salespoint_id + '&form_type=' + form_type + '&notes=' +
                    notes,
                success: function(response) {
                    let data = response.data;
                    console.log(data);
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Cancel End Kontrak tidak tersedia untuk salespoint user berikut, silahkan mengajukan Matriks Approval ke admin'
                        );
                        return;
                    }
                    data.forEach(item => {
                        let namelist = item.list.map(a => a.employee_name);
                        let option_text = '<option value="' + item.id + '">' + namelist.join(" -> ") +
                            '</option>';
                        $('#authorization_cancel').append(option_text);
                    });
                    $('#authorization_cancel').prop('disabled', false);
                },
                error: function(response) {
                    alert('load data failed. Please refresh browser or contact admin');
                    $('#authorization_cancel').prop('disabled', true);
                },
                complete: function() {
                    $('#authorization_cancel').val("");
                    $('#authorization_cancel').trigger('change');
                }
        });
    }

    function rejectAgreementCOP() {
        $('#rejectAgreementCOPModal').modal('show');
    }

    function rejectTorCOP() {
        $('#rejectTorCOPModal').modal('show');
    }

    function rejectSphCOP() {
        $('#rejectSphCOPModal').modal('show');
    }

    function rejectUserAgreementCOP() {
        $('#rejectUserAgreementCOPModal').modal('show');
    }

    $('.cheker').change(function() {
        let data = [];
        $('.cheker').each(function() {
            data.push($(this).is(':checked'));
        })
        console.log(data);
        if (data.includes(true)) {
            $("#revision_lpb_invoice").prop("disabled", false);
        } else {
            $("#revision_lpb_invoice").prop("disabled", true);

        }
    })

    $('#lpb1').change(function() {
        if ($(this).is(':checked')) {
            $("#js_lpb").show();
        } else {
            $("#js_lpb").hide();
        }
    })

    $('#invoice1').change(function() {
        if ($(this).is(':checked')) {
            $("#js_invoice").show();
        } else {
            $("#js_invoice").hide();
        }
    })

    function revisionDocument(id) {
        console.log(id);
        $('#revisionDocument').modal('show');
        $('#upload_confirmation_file').val(id);
    }

    function approve() {
        $('#approveform').submit();
    }

    function reject() {
        var reason = prompt("Harap memasukan alasan penolakan");
        if (reason != null) {
            if (reason.trim() == '') {
                alert("Alasan Harus diisi");
                return;
            }
            $('#rejectform').append('<input type="hidden" name="reason" value="' + reason + '">');
            $('#rejectform').submit();
        }
    }

    function selectfile(el) {
        $(el).closest('td').find('.inputFile').click();
    }

    function uploadfile(id, type, el) {
        let linkfile = $(el).closest('td').find('.revision_file');
        if (linkfile.length == 0) {
            alert('Silahkan pilih file revisi untuk di upload terlebih dahulu');
        } else {
            let inputfield = $('#uploadrevisionform').find('.input_field');
            let file = linkfile.prop('href');
            let filename = linkfile.text().trim();
            inputfield.empty();
            inputfield.append('<input type="hidden" name="id" value="' + id + '">');
            inputfield.append('<input type="hidden" name="type" value="' + type + '">');
            inputfield.append('<input type="hidden" name="file" value="' + file + '">');
            inputfield.append('<input type="hidden" name="filename" value="' + filename + '">');
            $('#uploadrevisionform').submit();
        }
    }

    function issuePO() {
        $('#issuePOmodal').modal('show');
    }
    $(document).ready(function() {
        $(this).on('change', '.inputFile', function(event) {
            var reader = new FileReader();
            let value = $(this).val();
            let display_field = $(this).closest('td').find('.display_field');
            if (validatefilesize(event)) {
                reader.onload = function(e) {
                    display_field.empty();
                    let name = value.split('\\').pop().toLowerCase();
                    display_field.append('<a class="revision_file" href="' + e.target
                        .result +
                        '" download="' + name + '">' + name + '</a>');
                }
                reader.readAsDataURL(event.target.files[0]);
            } else {
                $(this).val('');
            }
        });
        $('.validatefilesize').change(function(event) {
            if (!validatefilesize(event)) {
                $(this).val('');
            }
        });
        $('#over_platform').prop('checked', true);
        $("#submit_no_over_platform").hide();
    });
</script>
@endsection
