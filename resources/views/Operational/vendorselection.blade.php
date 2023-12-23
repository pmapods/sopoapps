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
    @php
        for ($i = 0; $i < $ticket->ticket_vendor->count(); $i++) {
            $start_harga[$i] = $ticket_item->price;
        }
        if (isset($bidding)) {
            foreach ($bidding->bidding_detail as $key => $detail) {
                $address[$key] = $detail->address;
                $start_harga[$key] = $detail->start_harga;
                $end_harga[$key] = $detail->end_harga;
                $price_score[$key] = $detail->price_score;
                $start_ppn[$key] = $detail->start_ppn;
                $end_ppn[$key] = $detail->end_ppn;
                $start_ongkir_price[$key] = $detail->start_ongkir_price;
                $end_ongkir_price[$key] = $detail->end_ongkir_price;
                $start_pasang_price[$key] = $detail->start_pasang_price;
                $end_pasang_price[$key] = $detail->end_pasang_price;
                $spesifikasi[$key] = $detail->spesifikasi;
                $ketersediaan_barang_score[$key] = $detail->ketersediaan_barang_score;
                $ready[$key] = $detail->ready;
                $indent[$key] = $detail->indent;
                $garansi[$key] = $detail->garansi;
                $bonus[$key] = $detail->bonus;
                $creditcash[$key] = $detail->creditcash;
                $ketentuan_bayar_score[$key] = $detail->ketentuan_bayar_score;
                $menerbitkan_faktur_pajak[$key] = $detail->menerbitkan_faktur_pajak;
                $masa_berlaku_penawaran[$key] = $detail->masa_berlaku_penawaran;
                $others_score[$key] = $detail->others_score;
                $start_lama_pengerjaan[$key] = $detail->start_lama_pengerjaan;
                $end_lama_pengerjaan[$key] = $detail->end_lama_pengerjaan;
                $optional1_start[$key] = $detail->optional1_start;
                $optional1_end[$key] = $detail->optional1_end;
                $optional2_start[$key] = $detail->optional2_start;
                $optional2_end[$key] = $detail->optional2_end;
            }
            $price_notes = $bidding->price_notes;
            $ketersediaan_barang_notes = $bidding->ketersediaan_barang_notes;
            $ketentuan_bayar_notes = $bidding->ketentuan_bayar_notes;
            $keterangan_lain = $bidding->keterangan_lain;
            $optional1_name = $bidding->optional1_name;
            $optional2_name = $bidding->optional2_name;
        }
        
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
    <div class="row">
        <div class="col-md-3">
            @if ($budget_item_data != null && $budget_item_data->qty != null && $budget_item_data->value != null)
                <table class="table table-sm table-bordered small table-info">
                    <thead>
                        <tr>
                            <th colspan=2>Data Budget</th>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td scope="row">{{ $budget_item_data->qty }}</td>
                            <td class="rupiah_text">{{ $budget_item_data->value }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
        <div class="col-md-9">
            @if (!isset($bidding))
                <div class="modal fade" id="uploadmodal" tabindex="-1" role="dialog"
                    aria-labelledby="Upload Seleksi Vendor" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="/bidding/uploadbiddingfile" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="ticket_item_id" value="{{ $ticket_item->id }}">
                                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Upload Seleksi Vendor</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Berikut digunakan untuk upload form seleksi vendor yang memiliki format berbeda.</p>
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="required_field">Pilih File Seleksi Vendor</label>
                                            <input type="file" class="form-control-file validatefilesize"
                                                name="biddingfile" placeholder="Pilih file" aria-describedby="fileHelpId"
                                                accept="image/*,application/pdf" required>
                                            <small id="fileHelpId" class="form-text text-muted">*File yang dipilih merupakan
                                                file yang sudah full approval (jpg, jpeg, pdf (MAX 5MB))</small>
                                        </div>
                                        <div class="form-group">
                                            <label class="required_field">Vendor Terpilih</label>
                                            <select class="form-control" name="selected_ticket_vendor" required>
                                                <option value="">-- Pilih Vendor --</option>
                                                @foreach ($ticket->ticket_vendor as $ticket_vendor)
                                                    <option value="{{ $ticket_vendor->id }}">{{ $ticket_vendor->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="d-flex px-4 justify-content-end">
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#uploadmodal">Upload Form
                        Seleksi</button>
                </div>
            @endif
        </div>
    </div>
    <div class="content-body px-4">
        <form action="/addbiddingform" method="post" id="addbiddingform">
            @csrf
            <input type="hidden" name="ticket_item_id" value="{{ $ticket_item->id }}">
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <div class="row">
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
                    <input type="text" class="form-control" value="{{ now()->translatedFormat('d F Y') }}" readonly>
                </div>

                <div class="col-md-2 mt-3">Kelompok</div>
                <div class="col-md-4 mt-3">
                    <div class="form-group">
                        <select class="form-control" id="select_kelompok" name="group"
                            value="{{ $bidding->group ?? '' }}">
                            <option value="asset" @if (($bidding->group ?? '') == 'asset') selected @endif>Asset</option>
                            <option value="inventory" @if (($bidding->group ?? '') == 'inventory') selected @endif>Inventaris</option>
                            <option value="others" @if (($bidding->group ?? '') == 'others') selected @endif>Lain-Lain</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="others_name"
                            placeholder="isi nama Kelompok Lain" id="input_kelompok_lain"
                            @if (($bidding->group ?? '') != 'others') style="display: none" @endif
                            value="{{ $bidding->other_name ?? '' }}">
                    </div>
                </div>
            </div>
            @php
                $vendors = $ticket->ticket_vendor;
                $vendor_count = $ticket->ticket_vendor->count();
                if ($vendor_count < 2) {
                    $vendor_count = 2;
                }
            @endphp
            @for ($i = 0; $i < $vendor_count; $i++)
                <input type="hidden" name="vendor[{{ $i }}][ticket_vendor_id]"
                    value="{{ $vendors[$i]->id ?? null }}">
            @endfor
            <table class="table table-bordered table-sm small" id="form_table">
                <thead>
                    <tr>
                        <th class="text-center" rowspan="5" class="text-center">No</th>
                        <th class="text-center" rowspan="5" class="text-center">Penilaian</th>
                        <th class="text-center" rowspan="5" class="text-center">Bobot</th>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <th colspan="3" class="text-center" id="vendor_name_{{ $i }}">
                                {{ $vendors->get($i) ? $vendors->get($i)->name : '-' }}
                            </th>
                            <input type="hidden" name="vendor[{{ $i }}][nama]"
                                value="{{ $vendors->get($i) ? $vendors->get($i)->name : null }}">
                        @endfor
                        <th class="text-center" rowspan="5" class="text-center">Keterangan</th>
                    </tr>
                    <tr>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <th>Alamat</th>
                            <th colspan="2">
                                @if ($vendors->get($i))
                                    @if ($vendors->get($i)->type == 0)
                                        {{ $vendors->get($i)->vendor()->address }}
                                    @else
                                        <textarea class="form-control" name="vendor[{{ $i }}][address]" rows="3">{{ $address[$i] ?? '-' }}</textarea>
                                    @endif
                                @else
                                    -
                                @endif
                            </th>
                        @endfor
                    </tr>
                    <tr>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <th>PIC</th>
                            <th colspan="2">
                                @if ($vendors->get($i))
                                    {{ $vendors->get($i)->salesperson }}
                                @else
                                    -
                                @endif
                            </th>
                        @endfor
                    </tr>
                    <tr>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <th>Telp/HP</th>
                            <th colspan="2">
                                @if ($vendors->get($i))
                                    @if ($vendors->get($i)->type == 0)
                                        {{ $vendors->get($i)->vendor()->phone }}
                                    @else
                                        {{ $vendors->get($i)->phone }}
                                    @endif
                                @else
                                    -
                                @endif
                            </th>
                        @endfor
                    </tr>
                    <tr>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <th>Proposal Awal</th>
                            <th>Proposal Akhir</th>
                            <th width="80">Nilai</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    {{-- price --}}
                    <tr class="table-success">
                        <td colspan="{{ 4 + $vendor_count * 3 }}"><b>Price</b></td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Harga</td>
                        <td class="text-center" rowspan="4">5</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][harga_awal]"
                                        value="{{ $start_harga[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm rupiah"
                                        name="vendor[{{ $i }}][harga_akhir]"
                                        value="{{ $end_harga[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center" rowspan="4">
                                @if ($vendors->get($i))
                                    <input type="number" class="form-control form-control-sm nilai" min="0"
                                        max="5" name="vendor[{{ $i }}][nilai_harga]"
                                        id="nilai_harga_{{ $i }}" value="{{ $price_score[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                        <td class="text-center" rowspan="4">
                            <textarea class="form-control form-control-sm " name="keterangan_harga" rows="10">{{ $price_notes ?? '-' }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>PPN</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][ppn_awal]" value="{{ $start_ppn[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][ppn_akhir]" value="{{ $end_ppn[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Ongkos Kirim</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][send_fee_awal]"
                                        value="{{ $start_ongkir_price[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][send_fee_akhir]"
                                        value="{{ $end_ongkir_price[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Ongkos Pasang</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][apply_fee_awal]"
                                        value="{{ $start_pasang_price[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm  rupiah"
                                        name="vendor[{{ $i }}][apply_fee_akhir]"
                                        value="{{ $end_pasang_price[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>

                    {{-- Ketersediaan  Barang --}}
                    <tr class="table-success">
                        <td colspan="{{ 4 + $vendor_count * 3 }}"><b>Ketersediaan Barang</b></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Spesifikasi (merk/type)</td>
                        <td class="text-center" rowspan="5">3</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][specs]" value="{{ $spesifikasi[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center" rowspan="5">
                                @if ($vendors->get($i))
                                    <input type="number" class="form-control form-control-sm nilai" min="0"
                                        max="3" name="vendor[{{ $i }}][nilai_ketersediaan]"
                                        id="nilai_ketersediaan_{{ $i }}"
                                        value="{{ $ketersediaan_barang_score[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                        <td class="text-center" rowspan="5">
                            <textarea class="form-control form-control-sm " name="keterangan_ketersediaan" rows="12">{{ $ketersediaan_barang_notes ?? '-' }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Ready</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][ready]" value="{{ $ready[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Indent</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][indent]" value="{{ $indent[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Barang bergaransi</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][garansi]" value="{{ $garansi[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Bonus</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][bonus]" value="{{ $bonus[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>

                    {{-- Ketentuan Pembayaran --}}
                    <tr class="table-success">
                        <td colspan="{{ 4 + $vendor_count * 3 }}"><b>Ketentuan Pembayaran</b></td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>Credit / Cash</td>
                        <td class="text-center" rowspan="2">2</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <select class="form-control form-control-sm " name="vendor[{{ $i }}][cc]">
                                        <option @if ($creditcash[$i] ?? '' == 'credit') selected @endif value="credit">Credit
                                        </option>
                                        <option @if ($creditcash[$i] ?? '' == 'cash') selected @endif value="cash">Cash
                                        </option>
                                    </select>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center" rowspan="2">
                                @if ($vendors->get($i))
                                    <input type="number" class="form-control form-control-sm nilai" min="0"
                                        max="2" name="vendor[{{ $i }}][nilai_pembayaran]"
                                        id="nilai_pembayaran_{{ $i }}"
                                        value="{{ $ketentuan_bayar_score[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                        <td class="text-center" rowspan="2">
                            <textarea class="form-control form-control-sm " rows="5" name="keterangan_pembayaran">{{ $ketentuan_bayar_notes ?? '-' }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td>Menerbitkan Faktur Pajak</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <select class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][pajak]">
                                        <option @if ($menerbitkan_faktur_pajak[$i] ?? -1 == 0) selected @endif value="0">Tidak
                                        </option>
                                        <option @if ($menerbitkan_faktur_pajak[$i] ?? -1 == 0) selected @endif value="1">Ya
                                        </option>
                                    </select>
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>

                    {{-- Informasi lain-Lain --}}
                    <tr class="table-success">
                        <td colspan="{{ 4 + $vendor_count * 3 }}"><b>Informasi Lain-lain</b></td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td>Masa berlaku penawaran</td>
                        <td class="text-center" rowspan="4">2</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2">
                                @if ($vendors->get($i))
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control form-control-sm " min="0"
                                            name="vendor[{{ $i }}][period]"
                                            value="{{ $masa_berlaku_penawaran[$i] ?? 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center" rowspan="4">
                                @if ($vendors->get($i))
                                    <input type="number" class="form-control form-control-sm nilai" min="0"
                                        max="2" name="vendor[{{ $i }}][nilai_other]"
                                        id="nilai_other_{{ $i }}" value="{{ $others_score[$i] ?? 0 }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                        <td class="text-center" rowspan="4">
                            <textarea class="form-control form-control-sm " rows="10" name="keterangan_lain">{{ $keterangan_lain ?? '-' }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>13</td>
                        <td>Lama Pengerjaan</td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control form-control-sm " min="0"
                                            name="vendor[{{ $i }}][time_awal]"
                                            value="{{ $start_lama_pengerjaan[$i] ?? 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control form-control-sm " min="0"
                                            name="vendor[{{ $i }}][time_akhir]"
                                            value="{{ $end_lama_pengerjaan[$i] ?? 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>

                    <tr>
                        <td>14</td>
                        <td>
                            <input type="text" class="form-control form-control-sm " name="optional1_name"
                                value="{{ $optional1_name ?? '-' }}">
                        </td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][optional1_awal]"
                                        value="{{ $optional1_start[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][optional1_akhir]"
                                        value="{{ $optional1_end[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>

                    <tr>
                        <td>15</td>
                        <td>
                            <input type="text" class="form-control form-control-sm " name="optional2_name"
                                value="{{ $optional2_name ?? '-' }}">
                        </td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][optional2_awal]"
                                        value="{{ $optional2_start[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($vendors->get($i))
                                    <input type="text" class="form-control form-control-sm "
                                        name="vendor[{{ $i }}][optional2_akhir]"
                                        value="{{ $optional2_end[$i] ?? '-' }}">
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        <td class="empty_column" colspan="3"></td>
                        @for ($i = 0; $i < $vendor_count; $i++)
                            <td colspan="2" class="table-success">Total Nilai</td>
                            <td id="total_{{ $i }}">0</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
            <center>
                <h5>Rekomendasi Vendor Terpilih</h5>
                <h4 id="selected_vendor">-</h4>
            </center>
            <b>CATATAN</b><br>
            <ol>
                <li>VENDOR YANG DINYATAKAN LULUS ADALAH JIKA NILAI > 30</li>
                <li>SELEKSI VENDOR DIIKUTI OLEH MINIMAL 2 VENDOR SEJENIS</li>
                <li>VENDOR YANG DIPILIH ADALAH 1 VENDOR YANG LULUS SELEKSI DENGAN NILAI PALING TINGGI</li>
            </ol>

            <div class="form-group">
                <label class="required_field">Pilih Matriks Approval</label>
                <select class="form-control authorization_id" name="authorization_id" required>
                    <option value="">-- Pilih Matriks Approval --</option>
                    @foreach ($authorizations as $authorization)
                        @php
                            $list = $authorization->authorization_detail;
                            $string = '';
                            foreach ($list as $key => $author) {
                                $string = $string . $author->employee->name;
                                if (count($list) - 1 != $key) {
                                    $string = $string . ' -> ';
                                }
                            }
                        @endphp
                        <option value="{{ $authorization->id }}">{{ $string }}</option>
                    @endforeach
                </select>
            </div>

            @if ($bidding_revision_count > 0)
                <div class="form-group">
                    <label class="required_field">Alasan Revisi(Wajib)</label>
                    <input type="text-area" class="form-control" name="reason_revision" required>
                </div>
            @endif

            <br>FRM-PCD-001 REV 00
            <b>ATTACHMENT</b><br>
            @if ($ticket_item->ticket_item_attachment->count() > 0 || $ticket_item->ticket_item_file_requirement->count() > 0)
                @if ($ticket_item->ticket_item_attachment->count() > 0)
                    <table class="table table-borderless table-sm">
                        <tbody>
                            @foreach ($ticket_item->ticket_item_attachment as $attachment)
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
                                        <a class="text-primary"
                                            onclick='window.open("/storage/{{ $attachment->path }}")'>
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
                            @foreach ($ticket_item->ticket_item_file_requirement as $requirement)
                                <tr>
                                    <td width="40%">{{ $requirement->file_completement->name }}</td>
                                    <td width="60%" class="tdbreak">
                                        <a class="text-primary"
                                            onclick='window.open("/storage/{{ $requirement->path }}")'>
                                            tampilkan attachment</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                -
            @endif

            <center>
                <button type="submit" class="btn btn-primary" id="submit_button">Buat Form Bidding</button>
            </center>
        </form>
    </div>

@endsection
@section('local-js')
    <script>
        var isSelected = false;
        let vendor_count = {{ $vendor_count }};
        let data = $('#addbiddingform').serializeObject();

        $(document).ready(function() {
            $('.validatefilesize').change(function(event) {
                if (!validatefilesize(event)) {
                    $(this).val('');
                }
            });
            $('#submit_button').on('click', function(e) {
                data = $('#addbiddingform').serializeObject();
                if (!isSelected) {
                    alert('Harus terpilih satu vendor');
                    e.preventDefault();
                    return;
                }
                for (let i = 0; i < vendor_count; i++) {
                    let vendor_name = data['vendor'][i]['nama'];
                    let harga_akhir = AutoNumeric.unformat(data['vendor'][i]['harga_akhir'],
                        autonum_setting);
                    if (harga_akhir < 50 && harga_akhir != null) {
                        alert('Harga Proposal Akhir vendor ' + vendor_name +
                            ' belum diisi atau lebih besar dari Rp. 50');
                        e.preventDefault();
                        return;
                    }
                }

                if ($('.authorization_id').val() == "") {
                    alert('Harap memilih Matriks Approval terlebih dahulu');
                    e.preventDefault();
                    return;
                }

                if (!confirm('Pastikan nilai proposal akhir sudah sesuai. Lanjutkan?')) {
                    e.preventDefault();
                }
            });
            $('input[type="number"]').change(function() {
                autonumber($(this));
            });

            $('#select_kelompok').change(function() {
                $('#input_kelompok_lain').val("");
                if ($(this).val() == 'others') {
                    $('#input_kelompok_lain').show();
                } else {
                    $('#input_kelompok_lain').hide();
                }
            })
            $(this).on('change', '.nilai', function() {
                data = $('#addbiddingform').serializeObject();
                let highest_score = 0;
                let selected_vendor_name = "-";
                for (let i = 0; i < vendor_count; i++) {
                    console.log(data['vendor'][i]["nama"]);
                    let nama = data['vendor'][i]["nama"];
                    let nilai_harga = parseInt(data['vendor'][i]["nilai_harga"]) * 5;
                    let nilai_ketersediaan = parseInt(data['vendor'][i]["nilai_ketersediaan"]) * 3;
                    let nilai_pembayaran = parseInt(data['vendor'][i]["nilai_pembayaran"]) * 2;
                    let nilai_other = parseInt(data['vendor'][i]["nilai_other"]) * 2;
                    let total = nilai_harga + nilai_ketersediaan + nilai_pembayaran + nilai_other;
                    if (total > highest_score) {
                        selected_vendor_name = nama;
                        highest_score = total;
                    }
                    $('#total_' + i).text(total);
                }
                // check if there is two or more same highest scores
                $same = 0;
                for (let i = 0; i < vendor_count; i++) {
                    let nilai_harga = parseInt(data['vendor'][i]["nilai_harga"]) * 5;
                    let nilai_ketersediaan = parseInt(data['vendor'][i]["nilai_ketersediaan"]) * 3;
                    let nilai_pembayaran = parseInt(data['vendor'][i]["nilai_pembayaran"]) * 2;
                    let nilai_other = parseInt(data['vendor'][i]["nilai_other"]) * 2;
                    let total = nilai_harga + nilai_ketersediaan + nilai_pembayaran + nilai_other;
                    if (highest_score == total) {
                        $same++;
                    }
                }
                if ($same > 1) {
                    $('#selected_vendor').text("-");
                    isSelected = false;
                } else {
                    $('#selected_vendor').text(selected_vendor_name);
                    isSelected = true;
                }
                // if(highest_score < 1){
                //     $('#selected_vendor').text("-");
                //     isSelected = false;
                // }else{
                //     $('#selected_vendor').text(selected_vendor_name);
                //     isSelected = true;
                // }
                // if(isNaN(total_1)){
                //     $('#total_1').text('-');
                // }

                // let name_0 = $('#vendor_name_0').text().trim();
                // let name_1 = $('#vendor_name_1').text().trim();

                // if(total_0 > total_1){
                //     $('#selected_vendor').text(name_0);
                //     isSelected = true;
                // }else if(total_1 > total_0){
                //     $('#selected_vendor').text(name_1);
                //     isSelected = true;
                // }else{
                //     $('#selected_vendor').text('-');
                //     isSelected = false;
                //     if(isNaN(total_1)){
                //         $('#selected_vendor').text(name_0);
                //         isSelected = true;
                //     }
                // }
            });
            $('.nilai').eq(0).trigger('change');
        });
    </script>
@endsection
