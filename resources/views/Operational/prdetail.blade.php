@extends('Layout.app')
@section('local-css')
    <style>
        table tr,
        table td {
            border: 1px solid #000 !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">PR Manual <a href="#"
                            onclick="window.open('/ticketing/{{ $ticket->code }}')">({{ $ticket->code }})</a></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Purchase Requisition</li>
                        <li class="breadcrumb-item active">PR Manual ({{ $ticket->code }})</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    @php
        $isReadonly = 'readonly';
        if ($ticket->status != 3) {
            // cek jika saat ini otorisasi sesuai akun login dan yang bisa edit adalah last author
            $isCurrentAuthorization = ($ticket->pr->current_authorization()->employee_id ?? -1) == Auth::user()->id;
            $lastAuthor = $ticket->pr->pr_authorizations->sortByDesc('level')->first();
            $isLastAuthor = ($lastAuthor->employee_id ?? -1) == Auth::user()->id;
            if ($isCurrentAuthorization && $isLastAuthor) {
                $isReadonly = '';
            }
        } else {
            $isReadonly = '';
        }
        if ($ticket->pr == null) {
            $isBudget = $ticket->budget_type == 0 ? true : false;
        } else {
            $isBudget = $ticket->pr->isBudget();
        }
    @endphp
    <form action="">
        @csrf
        <input type="hidden" name="updated_at" value="{{ $ticket->updated_at }}">
        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
        <input type="hidden" name="pr_id" value="{{ $ticket->pr->id ?? -1 }}">
        <input type="hidden" name="_method">
        <div class="content-body border border-dark p-2">
            <div class="d-flex flex-column">
                <span>PT. PINUS MERAH ABADI</span>
                <span>CABANG / DEPO : {{ $ticket->salespoint->name }}</span>
                <h4 class="align-self-center font-weight-bold">PURCHASE REQUISITION (PR) - MANUAL</h4>
                <div class="align-self-end">
                    <i class="fal @if ($isBudget) fa-check-square @else fa-square @endif mr-1"
                        aria-hidden="true"></i>Budget
                    <i class="fal @if (!$isBudget) fa-check-square @else fa-square @endif ml-5 mr-1"
                        aria-hidden="true"></i>Non Budget
                </div>
                <span>Tanggal :
                    {{ $ticket->pr ? $ticket->pr->created_at->format('Y-m-d') : now()->translatedFormat('Y-m-d') }}</span>
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <td class="font-weight-bold">No</td>
                            <td class="font-weight-bold" width="15%">Nama Barang</td>
                            <td class="font-weight-bold required_field" width='10%'>Satuan</td>
                            <td class="font-weight-bold required_field" width="8%">Qty</td>
                            <td class="font-weight-bold required_field">Harga Satuan (Rp)</td>
                            <td class="font-weight-bold" width="10%">Total Harga</td>
                            <td class="font-weight-bold optional_field">Tgl Set Up</td>
                            <td class="font-weight-bold">Keterangan</td>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @php
                            $grandtotal = 0;
                            $count = 1;
                        @endphp
                        {{-- kalau pr nya sudah ada --}}
                        @foreach ($ticket->pr->pr_detail ?? [] as $detail)
                            <input type="hidden" name="pr_detail_id" value="{{ $detail->id }}">
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>
                                    {{ $detail->name }}
                                    @if ($detail->ticket_item)
                                        @if (isset($detail->ticket_item->bidding->expired_date))
                                            <br>
                                            <span class="text-danger small text-nowrap">* bidding expired date :
                                                {{ \Carbon\Carbon::parse($detail->ticket_item->bidding->expired_date)->format('d-m-Y') }}</span>
                                        @endif
                                        @if (isset($detail->ticket_item->bidding->id))
                                            <br>
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/bidding/printview/{{ \Crypt::encryptString($detail->ticket_item->bidding->id) }}")'>
                                                tampilkan bidding</a><br>
                                        @endif
                                        @if (isset($detail->ticket_item->bidding->signed_filepath))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $detail->ticket_item->bidding->signed_filepath }}")'>
                                                tampilkan file penawaran dengan ttd</a><br>
                                        @endif
                                        @if (isset($detail->ticket_item->custom_bidding))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $detail->ticket_item->custom_bidding->filepath }}")'>
                                                tampilkan dokumen bidding</a><br>
                                        @endif
                                        @if ($detail->ticket_item->ticket->fri_forms->count() > 0)
                                            @php
                                                $fri_form = $detail->ticket_item->ticket->fri_forms->first();
                                            @endphp
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/printfriform/{{ $fri_form->id }}")'>
                                                tampilkan FRI</a><br>
                                        @endif
                                        @if (isset($detail->ticket_item->bidding->signed_filepath))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $detail->ticket_item->bidding->signed_filepath }}")'>tampilkan
                                                file penawaran dengan ttd</a><br>
                                        @endif
                                        @if (isset($detail->ticket_item->custom_bidding))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $detail->ticket_item->custom_bidding->filepath }}")'>tampilkan
                                                dokumen bidding</a><br>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $detail->uom }}</td>
                                <td>{{ $detail->qty }}</td>
                                <td class="rupiah_text">{{ $detail->price }}</td>
                                <td class="rupiah_text">{{ $detail->qty * $detail->price }}</td>
                                <td>{{ $detail->setup_date ?? '-' }}</td>
                                <td width="20%" class="text-justify">
                                    <div class="d-flex flex-column">
                                        <label class="optional_field">Keterangan</label>
                                        <span>{{ $detail->notes }}</span>
                                    </div>
                                </td>
                            </tr>
                            @if ($detail->ongkir_price > 0)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>Ongkir {{ $detail->name }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="rupiah text-nowrap">{{ $detail->ongkir_price }}</td>
                                    <td class="rupiah text-nowrap">{{ $detail->ongkir_price }}</td>
                                    <td class="text-nowrap"></td>
                                    <td class="text-justify"></td>
                                </tr>
                            @endif
                            @if ($detail->ongpas_price > 0)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>Ongpas {{ $detail->name }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="rupiah text-nowrap">{{ $detail->ongpas_price }}</td>
                                    <td class="rupiah text-nowrap">{{ $detail->ongpas_price }}</td>
                                    <td class="text-nowrap"></td>
                                    <td class="text-justify"></td>
                                </tr>
                            @endif
                            @php
                                $grandtotal += $detail->qty * $detail->price;
                                $grandtotal += $detail->ongkir_price ?? 0;
                                $grandtotal += $detail->ongpas_price ?? 0;
                            @endphp
                        @endforeach
                        {{-- kalau pr belum ada / ambil tarikan bidding --}}
                        @if (empty($ticket->pr) || ($ticket->pr->pr_detail->count() ?? 0) < 1)
                            @php
                                $grandtotal = 0;
                                $count = 1;
                            @endphp
                            @foreach ($ticket->ticket_item->where('isCancelled', '!=', true) as $key => $item)
                                @php
                                    if (isset($item->bidding)) {
                                        $grandtotal += $item->bidding->selected_vendor()->end_harga * $item->count;
                                    }
                                    $default_uom = '';
                                    if ($item->ticket->custom_settings != null) {
                                        $custom_settings = json_decode($item->ticket->custom_settings);
                                        $default_uom = $custom_settings->uom;
                                    }
                                @endphp
                                <input type="hidden" name="item[{{ $key }}][ticket_item_id]"
                                    value="{{ $item->id }}">
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>
                                        {{ $item->name }}
                                        @if (isset($item->bidding->expired_date))
                                            <br>
                                            <span class="text-danger small text-nowrap">* bidding expired date :
                                                {{ \Carbon\Carbon::parse($item->bidding->expired_date)->format('d-m-Y') }}</span>
                                        @endif
                                        @if (isset($item->bidding->id))
                                            <br>
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/bidding/printview/{{ \Crypt::encryptString($item->bidding->id) }}")'>
                                                tampilkan bidding</a><br>
                                        @endif
                                        @if (isset($item->bidding->signed_filepath))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $item->bidding->signed_filepath }}")'>
                                                tampilkan file penawaran dengan ttd</a><br>
                                        @endif
                                        @if (isset($item->custom_bidding))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $item->custom_bidding->filepath }}")'>
                                                tampilkan dokumen bidding</a><br>
                                        @endif
                                        @if ($item->ticket->fri_forms->count() > 0)
                                            @php
                                                $fri_form = $item->ticket->fri_forms->first();
                                            @endphp
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/printfriform/{{ $fri_form->id }}")'>
                                                tampilkan FRI</a><br>
                                        @endif
                                        @if (isset($item->bidding->signed_filepath))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $item->bidding->signed_filepath }}")'>tampilkan
                                                file penawaran dengan ttd</a><br>
                                        @endif
                                        @if (isset($item->custom_bidding))
                                            <a class="text-primary small text-nowrap" role="button"
                                                onclick='window.open("/storage/{{ $item->custom_bidding->filepath }}")'>tampilkan
                                                dokumen bidding</a><br>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->budget_pricing_id != null && $item->budget_pricing_id != -1)
                                            <input type="text" class="form-control"
                                                name="item[{{ $key }}][uom]"
                                                value="{{ $item->budget_pricing->uom }}" required>
                                        @else
                                            <input type="text" class="form-control"
                                                name="item[{{ $key }}][uom]" value="{{ $default_uom }}"
                                                required>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" class="form-control qty item{{ $key }}"
                                            min="0" max="{{ $item->count }}" value="{{ $item->count }}"
                                            onchange="refreshItemTotal(this)" name="item[{{ $key }}][qty]">
                                        <small class="text-secondary">max: {{ $item->count }}</small>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control price rupiah item{{ $key }}"
                                            @if (isset($item->bidding)) data-max="{{ $item->bidding->selected_vendor()->end_harga }}"
                                                value="{{ $item->pr_detail ? $item->pr_detail->price : $item->bidding->selected_vendor()->end_harga }}"
                                            @else
                                                value="{{ $item->pr_detail ? $item->pr_detail->price : 0 }}" @endif
                                            onchange="refreshItemTotal(this)" name="item[{{ $key }}][price]">
                                        @if (isset($item->bidding))
                                            <small class="text-secondary">max: <span
                                                    class="rupiah_text">{{ $item->bidding->selected_vendor()->end_harga }}</span></small>
                                        @endif
                                    </td>
                                    @php
                                        $data_total = 0;
                                        if (isset($item->bidding)) {
                                            $data_total = $item->bidding->selected_vendor()->end_harga * $item->count;
                                        }
                                    @endphp
                                    <td class="rupiah_text item{{ $key }} total"
                                        data-total="{{ $data_total }}">
                                        {{ $data_total }}
                                    </td>
                                    <td>
                                        <input class="form-control" type="date"
                                            name="item[{{ $key }}][setup_date]" {{ $isReadonly }}>
                                    </td>
                                    <td class="text-justify">
                                        <div class="d-flex flex-column">
                                            @if (isset($item->bidding))
                                                @if ($item->bidding->price_notes != null && $item->bidding->price_notes != '-')
                                                    <label>notes bidding harga</label>
                                                    <span
                                                        style="white-space: pre-line; !important">{{ $item->bidding->price_notes }}</span>
                                                @endif
                                                @if ($item->bidding->ketersediaan_barang_notes != null && $item->bidding->ketersediaan_barang_notes != '-')
                                                    <label>notes bidding barang</label>
                                                    <span
                                                        style="white-space: pre-line; !important">{{ $item->bidding->ketersediaan_barang_notes }}</span>
                                                @endif
                                            @endif
                                            <label class="optional_field">Keterangan</label>
                                            <textarea class="form-control" rows="3" placeholder="keterangan tambahan"
                                                name="item[{{ $key }}][notes]">{{ $isReadonly }}</textarea>
                                        </div>
                                    </td>
                                </tr>
                                @if (isset($item->bidding))
                                    @if ($item->bidding->selected_vendor()->end_ongkir_price > 0)
                                        @php
                                            $ongkir_price = $item->pr_detail ? $item->pr_detail->ongkir_price : $item->bidding->selected_vendor()->end_ongkir_price;
                                            $grandtotal += $ongkir_price;
                                        @endphp
                                        <tr>
                                            <td>{{ $count++ }}</td>
                                            <td>Ongkir {{ $item->name }}</td>
                                            <td></td>
                                            <td>
                                                <input type="hidden" class="qty item{{ $key }}"
                                                    value="1">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    class="form-control form-control-sm rupiah item{{ $key }}"
                                                    value="{{ $ongkir_price }}" onchange="refreshItemTotal(this)"
                                                    name="item[{{ $key }}][ongkir_price]">
                                            </td>
                                            <td class="rupiah_text item{{ $key }} total"
                                                data-total="{{ $ongkir_price }}">
                                                {{ $ongkir_price }}
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif
                                    @if ($item->bidding->selected_vendor()->end_pasang_price > 0)
                                        @php
                                            $ongpas_price = $item->pr_detail ? $item->pr_detail->ongpas_price : $item->bidding->selected_vendor()->end_pasang_price;
                                            $grandtotal += $ongpas_price;
                                        @endphp
                                        <tr>
                                            <td>{{ $count++ }}</td>
                                            <td>Ongpas {{ $item->name }}</td>
                                            <td></td>
                                            <td>
                                                <input type="hidden" class="qty item{{ $key }}"
                                                    value="1">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    class="form-control form-control-sm rupiah item{{ $key }}"
                                                    value="{{ $ongpas_price }}" onchange="refreshItemTotal(this)"
                                                    name="item[{{ $key }}][ongpas_price]">
                                            </td>
                                            <td class="rupiah_text item{{ $key }} total"
                                                data-total="{{ $ongpas_price }}">
                                                {{ $ongpas_price }}
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                        <tr>
                            <td colspan="5"><b>Total</b></td>
                            <td class="rupiah_text grandtotal" data-grandtotal="{{ $grandtotal }}">{{ $grandtotal }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
                @if ($ticket->status < 4)
                    <div class="row">
                        @php
                            $custom_settings = json_decode($ticket->custom_settings);
                            foreach ($ticket->ticket_authorization as $key => $author) {
                                if ($key == 0) {
                                    $aos = $author->employee_position;
                                }
                            }
                        @endphp
                        @if (
                            $ticket->custom_settings != null &&
                                $custom_settings->ticket_name == 'Ekspedisi Unit COP' &&
                                $aos != 'Area Operational Supervisor')
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="required_field">Dibuat Oleh</label>
                                    <select class="form-control" name="dibuat_oleh_ticketauthorization_id"
                                        id="dibuat_select" required>
                                        @foreach ($ticket->ticket_authorization as $key => $author)
                                            @if ($key == 0)
                                                <option value="{{ $author->id }}"
                                                    data-authorization="{{ $author }}"
                                                    data-tickettype='Ekspedisi Unit COP'>
                                                    {{ $author->employee_name }} -- {{ $author->employee_position }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <small class="text-danger">* Golongan 5B</small>
                                </div>
                            </div>
                        @elseif ($ticket->is_over_budget == 1)
                            @php
                                $levelll = [2, 3];
                                $collection = $ticket->ticket_authorization->whereIn('level', $levelll);
                                $values = collect($collection)->values();
                            @endphp
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="required_field">Dibuat Oleh</label>
                                    <select class="form-control" name="dibuat_oleh_ticketauthorization_id"
                                        id="dibuat_select" required>
                                        @foreach ($values as $author)
                                            <option value="{{ $author->id }}"
                                                data-authorization="{{ $author }}">
                                                {{ $author->employee->name }} -- {{ $author->employee_position }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger">* Minimal Golongan 5A</small>
                                </div>
                            </div>
                        @else
                            @php
                                $collection = $ticket->ticket_authorization->sortByDesc('level')->take(2);
                                $values = collect($collection)->values();
                            @endphp
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="required_field">Dibuat Oleh</label>
                                    <select class="form-control" name="dibuat_oleh_ticketauthorization_id"
                                        id="dibuat_select" required>
                                        @foreach ($values as $author)
                                            <option value="{{ $author->id }}"
                                                data-authorization="{{ $author }}">
                                                {{ $author->employee->name }} -- {{ $author->employee_position }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger">* Minimal Golongan 5A</small>
                                </div>
                            </div>
                        @endif

                        <div class="col-9">
                            <div class="form-group">
                                <label class="required_field">Matriks Approval</label>
                                <select class="form-control select2 authorization_select2" required
                                    name="pr_authorization_id">
                                    @php
                                        $is_budget_text = $isBudget ? 'Budget' : 'Non-Budget';
                                        $authorizations = $authorizations->where('notes', $is_budget_text);
                                    @endphp
                                    @foreach ($authorizations as $authorization)
                                        @php
                                            $list = $authorization->authorization_detail;
                                            $string = '';
                                            foreach ($list as $key => $author) {
                                                $string = $string . $author->employee->name;
                                                $open = $author->employee_position;
                                                if (count($list) - 1 != $key) {
                                                    $string = $string . ' -> ';
                                                }
                                            }
                                            $string .= ' || ' . $authorization->notes;
                                        @endphp
                                        <option value="{{ $authorization->id }}" data-list="{{ $list }}">
                                            {{ $string }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($ticket->over_budget_reason != null)
                    <br>
                    <table class="table table-bordered">
                        <thead class="text-center">
                            <td class="text-center" colspan="6">
                                <h5 class="font-weight-bold ">Daftar Over Budget Barang</h5>
                            </td>
                            <tr>
                                <td class="font-weight-bold">No</td>
                                <td class="font-weight-bold">Nama Barang</td>
                                <td class="font-weight-bold">Nilai Budget</td>
                                <td class="font-weight-bold">Nilai Ajuan</td>
                                <td class="font-weight-bold">Selisih</td>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($ticket->ticket_item as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td class="rupiah_text">{{ $item->nilai_budget_over_budget }}</td>
                                    <td class="rupiah_text">{{ $item->nilai_ajuan_over_budget }}</td>
                                    <td class="rupiah_text">{{ $item->selisih_over_budget }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <td class="text-justify" colspan="6">
                            <h5>Reason : {{ $ticket->over_budget_reason }}</h5>
                        </td>
                    </table>

                    <br>
                    <table class="table table-bordered">
                        <thead class="text-center">
                            <td class="text-center" colspan="6">
                                <h5 class="font-weight-bold ">History Approval Pengadaan</h5>
                            </td>
                            <tr>
                                <td class="font-weight-bold">No</td>
                                <td class="font-weight-bold">Nama Employee</td>
                                <td class="font-weight-bold">Jabatan Employee</td>
                                <td class="font-weight-bold">Sebagai</td>
                            </tr>
                        </thead>
                        <tbody class="text-justify">
                            @foreach ($ticket->ticket_authorization as $key => $author)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $author->employee_name }}</td>
                                    <td>{{ $author->employee_position }}</td>
                                    <td>{{ $author->as }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                @endif

                <center>
                    <h4>Otorisasi PR Manual</h4>
                </center>
                <div class="d-flex justify-content-center">
                    @if ($ticket->status < 4)
                        <div class="d-flex align-items-center justify-content-center" id="dibuat_oleh_field">
                        </div>
                    @endif
                    <div class="d-flex align-items-center justify-content-center" id="authorization_field">
                        @if ($ticket->status > 3)
                            @if ($ticket->status < 4)
                                <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                            @endif
                            @foreach ($ticket->pr->pr_authorizations as $key => $author)
                                <div class="mr-3">
                                    <span
                                        class="font-weight-bold">{{ $author->employee->name }}<br>{{ $author->employee_position }}</span><br>
                                    @if ($author->status == 1)
                                        <span class="text-success">Approved</span><br>
                                        <span
                                            class="text-success">{{ $author->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                                    @endif
                                    @if (($ticket->pr->current_authorization()->id ?? -1) == $author->id)
                                        <span class="text-warning">Menunggu Approval</span><br>
                                    @endif
                                    <span>{{ $author->as }}</span>
                                </div>
                                @if ($key != $ticket->pr->pr_authorizations->count() - 1)
                                    <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                <br><span>FRM-PCD-011 REV 01</span>
                <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="d-none">hidden_submit_button</button>
                    @if ($ticket->status == 3)
                        <button type="button" class="btn btn-primary" onclick="startAuthorization()">Mulai Approval
                            Form PR</button>
                    @else
                        @if (($ticket->pr->current_authorization()->employee_id ?? -1) == Auth::user()->id)
                            <button type="button" class="btn btn-success" onclick="approve()">Approve</button>
                            <button type="button" class="btn btn-danger ml-2" onclick="reject()">Reject</button>
                        @endif
                    @endif
                </div>
            </div>
    </form>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('input[type="number"]').change(function() {
                autonumber($(this));
            });
            $('.rupiah').each(function() {
                let index = $('.rupiah').index($(this));
                let max = $(this).data('max');

                if (max) {
                    let rupiahElement = autoNumeric_field[index];
                    rupiahElement.update({
                        "maximumValue": max
                    });
                }
            });
            $('.authorization_select2').change(function() {
                let list = $(this).find('option:selected').data('list');
                $('#authorization_field').empty();
                if (list !== undefined) {
                    $('#authorization_field').append(
                        '<i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>')
                    list.forEach(function(item, index) {
                        $('#authorization_field').append(
                            '<div class="mr-3"><span class="font-weight-bold">' + item.employee
                            .name + ' -- ' + item.employee_position.name + '</span><br><span>' +
                            item.sign_as + '</span></div>');
                        if (index != list.length - 1) {
                            $('#authorization_field').append(
                                '<i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>');
                        }
                    });
                }
            });
            $(".authorization_select2").trigger("change");
            $('#dibuat_select').change(function() {
                let author = $(this).find('option:selected').data('authorization');
                let ticket_type = $(this).find('option:selected').data('tickettype');

                $('#dibuat_oleh_field').empty();
                if (author !== undefined) {
                    if (ticket_type) {
                        $('#dibuat_oleh_field').append('<div class="mr-3"><span class="font-weight-bold">' +
                            author.employee_name + ' -- ' + author.employee_position +
                            '</span><br><span>Dibuat Oleh</span></div>');
                    } else {
                        $('#dibuat_oleh_field').append('<div class="mr-3"><span class="font-weight-bold">' +
                            author.employee_name + ' -- ' + author.employee_position +
                            '</span><br><span>Dibuat Oleh</span></div>');
                    }
                }
            });
            $('#dibuat_select').trigger('change');
        });

        function refreshItemTotal(this_el) {
            const tr_element = $(this_el).closest('tr');
            const qty = tr_element.find('td:eq(3) input').val();
            const price = AutoNumeric.unformat(tr_element.find('td:eq(4) input').val(), autonum_setting);
            tr_element.find('td:eq(5)').data('total', qty * price);
            tr_element.find('td:eq(5)').text(setRupiah(qty * price));
            refreshGrandTotal();
        }

        function refreshGrandTotal() {
            let grandtotal = 0;
            $('.total').each(function() {
                grandtotal += parseFloat($(this).data('total'));
            });
            $('.grandtotal').text(setRupiah(grandtotal));
            $('.grandtotal').data('grandtotal', grandtotal);
        }

        function startAuthorization() {
            $('form').prop('action', '/addnewpr');
            $('form').prop('method', 'POST');
            $('form input[name="_method"]').val('POST');
            $('button[type="submit"]').trigger('click');
        }

        function approve() {
            $('form').prop('action', '/approvepr');
            $('form').prop('method', 'POST');
            $('form input[name="_method"]').val('PATCH');
            $('.rupiah').each(function() {
                let index = $('.rupiah').index($(this));
                let rupiahElement = autoNumeric_field[index];
                rupiahElement.update({
                    "aSign": '',
                    "aDec": '.',
                    "aSep": ''
                });
            });
            $('form').submit();
        }

        function reject() {
            var reason = prompt("Harap memasukan alasan penolakan");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }
                $('form').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('form').prop('action', '/rejectpr');
                $('form').prop('method', 'POST');
                $('form input[name="_method"]').val('PATCH');
                $('.rupiah').each(function() {
                    let index = $('.rupiah').index($(this));
                    let rupiahElement = autoNumeric_field[index];
                    rupiahElement.update({
                        "aSign": '',
                        "aDec": '.',
                        "aSep": ''
                    });
                });
                $('form').submit();
            }
        }
    </script>
@endsection
