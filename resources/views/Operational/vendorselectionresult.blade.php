@extends('Layout.app')

@section('local-css')
    <style>
        #form_table thead {
            background-color: #76933C;
            border: 1px solid #000 !important;
        }

        #form_table td,
        #form_table th {
            vertical-align: middle !important;
        }

        textarea {
            resize: none !important;
        }
    </style>
@endsection

@section('content')

    @php
        $budget_item_data = null;
        $budget_item_code = null;
        if ($ticket_item->budget_pricing_id != null && $ticket_item->budget_pricing_id != -1) {
            $budget_item_code = $ticket_item->budget_pricing->code;
        }
        if ($ticket_item->maintenance_budget_id != null && $ticket_item->maintenance_budget_id != -1) {
            $budget_item_code = $ticket_item->maintenance_budget->code;
        }
        if ($ticket_item->ho_budget_id != null && $ticket_item->ho_budget_id != -1) {
            $budget_item_code = $ticket_item->ho_budget->code;
        }
        if ($budget_item_code != null) {
            try {
                $budget_item_data = $ticket_item->ticket->budget_upload->budget_detail->where('code', $budget_item_code)->first();
            } catch (\Throwable $th) {
            }
        }
    @endphp
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fal fa-arrow-left" aria-hidden="true" style="cursor: pointer;"
                            onclick="window.location.href='/bidding/{{ $ticket->code }}'"></i>
                        Form Seleksi Vendor <a href="#"
                            onclick="window.open('/ticketing/{{ $ticket->code }}')">({{ $ticket->code }})</a>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Bidding</li>
                        <li class="breadcrumb-item">{{ $ticket->code }}</li>
                        <li class="breadcrumb-item active">{{ $ticket_item->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="row">
            @if ($bidding->expired_date)
                <div class="col-md-12 text-danger text-right">
                    Tanggal Expired : <span
                        class="font-weight-bold">{{ \Carbon\Carbon::parse($bidding->expired_date)->translatedFormat('d F Y') }}</span><br>
                    <span class="text-secondary small">* tanggal expired merupakan 30 hari dari approval bidding
                        terakhir</span>
                </div>
            @endif

            <div class="col-md-2 mt-3">Jenis Produk</div>
            <div class="col-md-4 mt-3">
                <input type="text" class="form-control" value="{{ $ticket_item->name }}" readonly>
            </div>

            <div class="col-md-2 mt-3">Area / SalesPoint</div>
            <div class="col-md-4 mt-3">
                <input type="text" class="form-control" value="{{ $ticket->salespoint->name }}" readonly>
            </div>

            <div class="col-md-2 mt-3">Tanggal Seleksi</div>
            <div class="col-md-4 mt-3">
                <input type="text" class="form-control"
                    value="{{ $bidding->created_at->translatedFormat('d F Y (H:i)') }}" readonly>
            </div>

            <div class="col-md-2 mt-3">Kelompok</div>
            <div class="col-md-4 mt-3">
                <div class="form-group">
                    <select class="form-control" id="select_kelompok" name="group" value="{{ $bidding->group }}" disabled>
                        <option value="asset" @if ($bidding->group == 'asset') selected @endif>Asset</option>
                        <option value="inventory" @if ($bidding->group == 'inventory') selected @endif>Inventaris</option>
                        <option value="others" @if ($bidding->group == 'others') selected @endif>Lain-Lain</option>
                    </select>
                    @if ($bidding->group == 'others')
                        <input type="text" class="form-control mt-2" name="others_name" id="input_kelompok_lain"
                            value="{{ $bidding->other_name }}" disabled>
                    @endif
                </div>
            </div>
        </div>
        <table class="table table-bordered table-sm small" id="form_table">
            <thead>
                <tr>
                    <th class="text-center" rowspan="5" class="text-center">No</th>
                    <th class="text-center" rowspan="5" class="text-center">Penilaian</th>
                    <th class="text-center" rowspan="5" class="text-center">Bobot</th>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th colspan="3" class="text-center">
                            {{ $detail->ticket_vendor->name }}
                        </th>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <th colspan="3">-</th>
                    @endif
                    <th class="text-center" rowspan="5" class="text-center">Keterangan</th>
                </tr>
                <tr>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th>Alamat</th>
                        <th colspan="2">
                            {{ $detail->address }}
                        </th>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <th>Alamat</th>
                        <th colspan="2">-</th>
                    @endif
                </tr>
                <tr>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th>PIC</th>
                        <th colspan="2">
                            {{ $detail->ticket_vendor->salesperson }}
                        </th>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <th>PIC</th>
                        <th colspan="2">-</th>
                    @endif
                </tr>
                <tr>
                    @foreach ($bidding->bidding_detail as $detail)
                        <th>Telp/HP</th>
                        <th colspan="2">
                            @if ($detail->ticket_vendor->vendor_id == null)
                                {{ $detail->ticket_vendor->phone }}
                            @else
                                {{ $detail->ticket_vendor->vendor()->phone }}
                            @endif
                        </th>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <th>Telp/HP</th>
                        <th colspan="2">-</th>
                    @endif
                </tr>
                <tr>
                    @for ($i = 0; $i < $bidding->bidding_detail->count(); $i++)
                        <th>Proposal Awal</th>
                        <th>Proposal Akhir</th>
                        <th width="80">Nilai</th>
                    @endfor
                    @if ($bidding->bidding_detail->count() == 1)
                        <th>Proposal Awal</th>
                        <th>Proposal Akhir</th>
                        <th width="80">Nilai</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $title_colspan = 4 + 3 * ($bidding->bidding_detail->count() == 1 ? 2 : $bidding->bidding_detail->count());
                @endphp
                {{-- price --}}
                <tr class="table-success">
                    <td colspan="{{ $title_colspan }}"><b>Price</b></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Harga</td>
                    <td class="text-center" rowspan="4">5</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{ $detail->start_harga }}
                        </td>
                        <td class="rupiah">
                            {{ $detail->end_harga }}
                        </td>
                        <td class="text-center" rowspan="4">
                            {{ $detail->price_score }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                        <td class="text-center" rowspan="4">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="4">
                        {{ $bidding->price_notes }}
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>PPN</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{ $detail->start_ppn }}
                        </td>
                        <td class="rupiah">
                            {{ $detail->end_ppn }}
                        </td>
                    @endforeach

                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                <tr>
                    <td>3</td>
                    <td>Ongkos Kirim</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{ $detail->start_ongkir_price }}
                        </td>
                        <td class="rupiah">
                            {{ $detail->end_ongkir_price }}
                        </td>
                    @endforeach

                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                <tr>
                    <td>4</td>
                    <td>Ongkos Pasang</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td class="rupiah">
                            {{ $detail->start_pasang_price }}
                        </td>
                        <td class="rupiah">
                            {{ $detail->end_pasang_price }}
                        </td>
                    @endforeach

                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>

                {{-- Ketersediaan  Barang --}}
                <tr class="table-success">
                    <td colspan="{{ $title_colspan }}"><b>Ketersediaan Barang</b></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Spesifikasi (merk/type)</td>
                    <td class="text-center" rowspan="5">3</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->spesifikasi }}
                        </td>
                        <td class="text-center" rowspan="5">
                            {{ $detail->ketersediaan_barang_score }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                        <td rowspan="5">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="5">
                        {{ $bidding->ketersediaan_barang_notes }}
                    </td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Ready</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->ready }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
                <tr>
                    <td>7</td>
                    <td>Indent</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->indent }}
                        </td>
                    @endforeach

                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
                <tr>
                    <td>8</td>
                    <td>Barang bergaransi</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->garansi }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>
                <tr>
                    <td>9</td>
                    <td>Bonus</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->bonus }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>

                {{-- Ketentuan Pembayaran --}}
                <tr class="table-success">
                    <td colspan="{{ $title_colspan }}"><b>Ketentuan Pembayaran</b></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Credit / Cash</td>
                    <td class="text-center" rowspan="2">2</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->creditcash }}
                        </td>
                        <td class="text-center" rowspan="2">
                            {{ $detail->ketentuan_bayar_score }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                        <td rowspan="2">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="2">
                        {{ $bidding->ketentuan_bayar_notes }}
                    </td>
                </tr>
                <tr>
                    <td>11</td>
                    <td>Menerbitkan Faktur Pajak</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->menerbitkan_faktur_pajak ? 'Ya' : 'Tidak' }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                    @endif
                </tr>

                {{-- Informasi lain-Lain --}}
                <tr class="table-success">
                    <td colspan="{{ $title_colspan }}"><b>Informasi Lain-lain</b></td>
                </tr>
                <tr>
                    <td>12</td>
                    <td>Masa berlaku penawaran</td>
                    <td class="text-center" rowspan="4">2</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td colspan="2">
                            {{ $detail->masa_berlaku_penawaran }} Hari
                        </td>
                        <td class="text-center" rowspan="4">
                            {{ $detail->others_score }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td colspan="2">-</td>
                        <td rowspan="4">-</td>
                    @endif
                    <td style="white-space: pre-line; !important" rowspan="4">
                        {{ $bidding->others_notes }}
                    </td>
                </tr>
                <tr>
                    <td>13</td>
                    <td>Lama Pengerjaan</td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td>
                            {{ $detail->start_lama_pengerjaan }} Hari
                        </td>
                        <td>
                            {{ $detail->end_lama_pengerjaan }} Hari
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>

                <tr>
                    <td>14</td>
                    <td>
                        {{ $bidding->optional1_name }}
                    </td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td>
                            {{ $detail->optional1_start }}
                        </td>
                        <td>
                            {{ $detail->optional1_end }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>

                <tr>
                    <td>15</td>
                    <td>
                        {{ $bidding->optional2_name }}
                    </td>
                    @foreach ($bidding->bidding_detail as $detail)
                        <td>
                            {{ $detail->optional2_start }}
                        </td>
                        <td>
                            {{ $detail->optional2_end }}
                        </td>
                    @endforeach
                    @if ($bidding->bidding_detail->count() == 1)
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>
                <tr>
                    @php
                        $scores = [];
                        foreach ($bidding->bidding_detail as $key => $detail) {
                            $scores[$key] = 0;
                            $scores[$key] += $detail->price_score * 5;
                            $scores[$key] += $detail->ketersediaan_barang_score * 3;
                            $scores[$key] += $detail->ketentuan_bayar_score * 2;
                            $scores[$key] += $detail->others_score * 2;
                        }
                    @endphp
                    <td class="empty_column" colspan="3"></td>
                    @foreach ($scores as $score)
                        <td colspan="2" class="table-success">Total Nilai</td>
                        <td>{{ $score }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        <center>
            <h5>Rekomendasi Vendor Terpilih</h5>
            <h4 id="selected_vendor">{{ $bidding->selected_vendor()->ticket_vendor->name }}</h4>
        </center>
        <b>CATATAN</b><br>
        <ol>
            <li>VENDOR YANG DINYATAKAN LULUS ADALAH JIKA NILAI > 30</li>
            <li>SELEKSI VENDOR DIIKUTI OLEH MINIMAL 2 VENDOR SEJENIS</li>
            <li>VENDOR YANG DIPILIH ADALAH 1 VENDOR YANG LULUS SELEKSI DENGAN NILAI PALING TINGGI</li>
        </ol>
        <center>
            <h4>Otorisasi</h4>
        </center>
        <div class="d-flex align-items-center justify-content-center">
            @foreach ($bidding->bidding_authorization as $key => $author)
                <div class="mr-3">
                    <span class="font-weight-bold">{{ $author->employee->name }} --
                        {{ $author->employee_position }}</span><br>
                    <span>{{ $author->as }}</span>
                    <br>
                    @if ($bidding->current_authorization() != null)
                        @if ($bidding->current_authorization()->id == $author->id)
                            <span class="text-warning">
                                Menunggu Approval
                            </span>
                        @endif
                    @endif
                    @if ($author->status == 1)
                        <span class="text-success">
                            Approved -- {{ $author->updated_at->translatedFormat('d F Y (H:i)') }}
                        </span>
                    @endif
                </div>
                @if ($key < $bidding->bidding_authorization->count() - 1)
                    <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                @endif
            @endforeach
        </div>

        @if ($bidding->reason_revision)
            <div class="form-group">
                <label class="required_field">Alasan Revisi</label>
                <input type="text-area" class="form-control" value="{{ $bidding->reason_revision }}" readonly>
            </div>
        @endif

        <br>FRM-PCD-001 REV 00
        @php
            $custom_settings = json_decode($ticket->custom_settings);
            
            if (in_array($bidding->current_authorization()->level ?? -1, [2, 3]) && Auth::user()->id == $bidding->current_authorization()->employee->id) {
                $needUploadSigned = true;
            } else {
                $needUploadSigned = false;
            }
            
            if (in_array($bidding->current_authorization()->level ?? -1, [2]) && Auth::user()->id == $bidding->current_authorization()->employee->id && $ticket->custom_settings != null && $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP') {
                $needUploadCOP = true;
            } else {
                $needUploadCOP = false;
            }
        @endphp

        <div class="row mt-3">
            @if ($needUploadSigned)
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="required_field">UPLOAD FILE PENAWARAN</label>
                        <input type="file" class="form-control-file validatefilesize" name="file_penawaran"
                            id="file_penawaran" accept="image/*,application/pdf" required>
                        <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                    </div>
                    <input type="hidden" name="bidding_id" value="{{ $bidding->id }}">
                </div>
            @endif
            @if ($bidding->signed_filepath != null)
                <div class="col-md-7 d-flex flex-column">
                    <b>FILE PENAWARAN YANG SUDAH DI TANDA TANGAN</b>
                    <a href="/storage/{{ $bidding->signed_filepath }}"
                        target="_blank">{{ $bidding->signed_filename }}</a><br>
                </div>
            @endif
        </div>

        @if ($needUploadCOP)
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="required_field">UPLOAD FILE COP</label>
                        <input type="file" class="form-control-file form-control-sm validatefilesize" name="file_cop"
                            id="file_cop" required>
                        <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                    </div>
                    <input type="hidden" name="bidding_id" value="{{ $bidding->id }}">
                </div>
            </div>
        @endif
        @if ($bidding->cop_filepath != null)
            <div class="col-md-7 d-flex flex-column">
                <b>FILE COP</b>
                <a href="/storage/{{ $bidding->cop_filepath }}" target="_blank">{{ $bidding->cop_filename }}</a><br>
            </div>
        @endif

        <div class="d-flex justify-content-center align-items-center mt-3">
            @if ($bidding->current_authorization() != null)
                @if (Auth::user()->id == $bidding->current_authorization()->employee->id)
                    <button type="button" class="btn btn-danger mr-2" onclick="reject()">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approve()">Approve</button>
                @endif
            @else
                <button type="button" class="btn btn-info"
                    onclick="window.open('/bidding/printview/{{ \Crypt::encryptString($bidding->id) }}')">Cetak</button>
                @if ($bidding->expired_date != null && now() > \Carbon\Carbon::parse($bidding->expired_date))
                    <button type="button" class="btn btn-warning ml-2" onclick="extendExpiredDate()">Perpanjang Expired
                        Date</button>
                @endif
                <button type="button" class="btn btn-secondary ml-2" onclick="reviseSelectionVendor()">Revisi</button>
            @endif
        </div>
    </div>


    <b>ATTACHMENT</b><br>
    @if ($ticket_item->ticket_item_attachment->count() == 0 && $ticket_item->ticket_item_file_requirement->count() == 0)
        -
    @endif

    @if ($ticket_item->ticket_item_attachment->count() > 0)
        <table class="table table-borderless table-sm">
            <tbody>
                @foreach ($bidding->ticket_item->ticket_item_attachment as $attachment)
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
                        <td width="40%">{{ $naming }}</td>
                        <td width="60%" class="tdbreak">
                            <a class="text-primary" onclick='window.open("/storage/{{ $attachment->path }}")'>
                                tampilkan attachment</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($ticket_item->ticket_item_file_requirement->count() > 0)
        <table class="table table-borderless table-sm">
            <tbody>
                @foreach ($bidding->ticket_item->ticket_item_file_requirement as $requirement)
                    <tr>
                        <td width="40%">{{ $requirement->file_completement->name }}</td>
                        <td width="60%" class="tdbreak">
                            <a class="text-primary" onclick='window.open("/storage/{{ $requirement->path }}")'>
                                tampilkan attachment</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <form action="/approvebidding" method="post" id="approveform" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <input type="hidden" name="bidding_id" value="{{ $bidding->id }}">
        <input type="hidden" name="bidding_authorization_id" value="{{ $bidding->current_authorization()->id ?? '' }}">
        <div class="input_field">
        </div>
    </form>

    <form action="/rejectbidding" method="post" id="rejectform">
        @csrf
        @method('patch')
        <input type="hidden" name="bidding_id" value="{{ $bidding->id }}">
        <input type="hidden" name="bidding_authorization_id" value="{{ $bidding->current_authorization()->id ?? '' }}">
        <div class="input_field">
        </div>
    </form>

    <div class="modal fade" id="extendExpiredDateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Perpanjang Expired Date</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/{{ request()->path() }}/extendexpireddate" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bidding_id" value="{{ $bidding->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="old_expired_date">Expired Date saat ini</label>
                                    <input type="date" class="form-control" id="old_expired_date"
                                        value="{{ $bidding->expired_date }}" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="new_expired_date">Expired Date baru</label>
                                    <input type="date" class="form-control" id="new_expired_date"
                                        name="new_expired_date"
                                        min="{{ \Carbon\Carbon::parse($bidding->expired_date)->addDays(1)->format('Y-m-d') }}"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-warning">Perpanjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reviseSelectionVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Revisi Seleksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/{{ request()->path() }}/revise" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bidding_id" value="{{ $bidding->id }}">
                    <div class="modal-body">
                        Data Seleksi Vendor terkait item {{ $bidding->product_name }} harus diinput ulang secara
                        keseluruhan. Apakah anda akan melanjutkan revisi?<br>
                        <span class="text-danger small">*revisi data hanya dapat dilakukan jika bidding terkait tidak
                            sedang dalam proses approval PR Manual</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('#select_kelompok').change(function() {
                $('#input_kelompok_lain').val("");
                if ($(this).val() == 'others') {
                    $('#input_kelompok_lain').show();
                } else {
                    $('#input_kelompok_lain').hide();
                }
            })

            $(this).on('change', '.nilai', function() {
                // vendor 1
                let harga = $('#nilai_harga_0').val() * 5;
                let ketersediaan = $('#nilai_ketersediaan_0').val() * 3;
                let pembayaran = $('#nilai_pembayaran_0').val() * 2;
                let other = $('#nilai_other_0').val() * 2;
                let total_0 = parseInt(harga) + parseInt(ketersediaan) + parseInt(pembayaran) + parseInt(
                    other);
                $('#total_0').text(total_0);

                // vendor 2
                harga = $('#nilai_harga_1').val() * 5;
                ketersediaan = $('#nilai_ketersediaan_1').val() * 3;
                pembayaran = $('#nilai_pembayaran_1').val() * 2;
                other = $('#nilai_other_1').val() * 2;
                total_1 = parseInt(harga) + parseInt(ketersediaan) + parseInt(pembayaran) + parseInt(other);
                $('#total_1').text(total_1);

                let name_0 = $('#vendor_name_0').text().trim();
                let name_1 = $('#vendor_name_1').text().trim();

                if (total_0 > total_1) {
                    $('#selected_vendor').text(name_0);
                } else {
                    $('#selected_vendor').text(name_1);
                }
            });

            $(this).on('change', '.inputFile', function(event) {
                var reader = new FileReader();
                let value = $(this).val();
                let display_field = $('.display_field');
                if (validatefilesize(event)) {
                    reader.onload = function(e) {
                        display_field.empty();
                        let name = value.split('\\').pop().toLowerCase();
                        display_field.append('<a class="revision_file" href="' + e.target.result +
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
        });

        function approve() {
            // cek apakah file penawaran ada, jika ada tambahan di approve
            if ($("#file_penawaran").length != 0) {
                let inputfile = $('#file_penawaran').clone();
                if (!$('#file_penawaran').val().trim().length) {
                    alert('File belum dipilih');
                    return;
                }
                $('#approveform div').append(inputfile);
            }

            if ($("#file_cop").length != 0) {
                let inputfile = $('#file_cop').clone();
                if (!$('#file_cop').val().trim().length) {
                    alert('File COP belum dipilih');
                    return;
                }
                $('#approveform div').append(inputfile);
            }

            $('#approveform').submit();
        }

        function reject() {
            var reason = prompt("Harap memasukan alasan penolakan");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return
                }
                $('#rejectform .input_field').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#rejectform').submit();
            }
        }

        function selectfile() {
            $('.inputFile').click();
        }

        function extendExpiredDate() {
            $("#extendExpiredDateModal").modal("show");
        }

        function reviseSelectionVendor() {
            $("#reviseSelectionVendorModal").modal("show");
        }
    </script>
@endsection
