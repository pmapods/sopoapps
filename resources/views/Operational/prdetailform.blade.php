@extends('Layout.app')
@section('local-css')
    <style>
        #pr_table tr,
        #pr_table td {
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
                @if ($ticket->pr->revise_ba_filepath)
                    <div class="col-sm-6 d-flex flex-column">
                        <span>Telah di revisi oleh <b>{{ $ticket->pr->revised_by_employee()->name }}</b></span>
                        <span>Dengan Alasan : <b>{{ $ticket->pr->revise_reason }}</b></span>
                        <span>Tanggal Revisi :
                            <b>{{ \Carbon\Carbon::parse($ticket->pr->revised_at)->translatedFormat('d F Y (H:i)') }}</b></span>
                        <span><a href="#"
                                onclick="window.open('/storage/{{ $ticket->pr->revise_ba_filepath }}')"><b>Dokumen
                                    Terkait</b></a></span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 card bg-info d-none">
            <div class="card-body">
                <h5>Request Nomor Asset (Web Asset)</h5>
                @if (isset($asset_number_request_data))
                    <div class="row">
                        {{-- <div class="col-md-4">Tanggal Request</div>
                <div class="col-md-8">: {{ $asset_number_request_data->request_date ?? 'data tidak ditemukan'}}</div>
                <div class="col-md-4">Update terakhir</div>
                <div class="col-md-8">: {{ $asset_number_request_data->last_updated ?? 'data tidak ditemukan'}}</div>
                <div class="col-md-4">Nomor</div>
                <div class="col-md-8">: {{ $asset_number_request_data->request_code ?? 'data tidak ditemukan'}}</div> --}}
                        <div class="col-md-4">Status</div>
                        <div class="col-md-8">: {{ $asset_number_request_data->message ?? 'data tidak ditemukan' }}</div>
                    </div>
                @endif
            </div>
        </div>
        <form action="/submitassetnumber" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <input type="hidden" name="pr_id" value="{{ $ticket->pr->id }}">
            @isset($ticket)
                <input type="hidden" name="updated_at" value="{{ $ticket->updated_at->translatedFormat('Y-m-d H:i:s') }}">
            @endisset
            {{-- @isset($armadaticket)
    <input type="hidden" name="updated_at" value="{{$armadaticket->updated_at->translatedFormat('Y-m-d H:i:s')}}">
    @endisset
    @isset($securityticket)
    <input type="hidden" name="updated_at" value="{{$armadaticket->updated_at->translatedFormat('Y-m-d H:i:s')}}">
    @endisset --}}
            <div class="content-body border border-dark p-2">
                <div class="d-flex flex-column">
                    <span>PT. PINUS MERAH ABADI</span>
                    <span>CABANG / DEPO : {{ $ticket->salespoint->name }}</span>
                    <h4 class="align-self-center font-weight-bold">PURCHASE REQUISITION (PR) - MANUAL</h4>
                    <div class="align-self-end">
                        <i class="fal @if ($ticket->pr->isBudget()) fa-check-square @else fa-square @endif mr-1"
                            aria-hidden="true"></i>Budget
                        <i class="fal @if (!$ticket->pr->isBudget()) fa-check-square @else fa-square @endif ml-5 mr-1"
                            aria-hidden="true"></i>Non Budget
                    </div>
                    <span>Tanggal : {{ $ticket->pr->created_at->format('Y-m-d') }}</span>
                    <table class="table table-bordered table-sm" id="pr_table">
                        <thead class="text-center">
                            <tr>
                                <td class="font-weight-bold">No</td>
                                <td class="font-weight-bold" width="15%">Nama Barang</td>
                                <td class="font-weight-bold" width="10%">Satuan</td>
                                <td class="font-weight-bold" width="8%">Qty</td>
                                <td class="font-weight-bold">Harga Satuan (Rp)</td>
                                <td class="font-weight-bold" width="10%">Total Harga</td>
                                <td class="font-weight-bold" width="10%">Tgl Set Up</td>
                                <td class="font-weight-bold">Keterangan</td>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php
                                $grandtotal = 0;
                                $count = 1;
                            @endphp
                            @foreach ($ticket->pr->pr_detail ?? [] as $key => $detail)
                                <input type="hidden" name="item[{{ $key }}][pr_detail_id]"
                                    value="{{ $detail->id }}">
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
                                                    onclick='window.open("/bidding/printview/{{ \Crypt::encryptString($detail->ticket_item->bidding->id) }}")'>tampilkan
                                                    bidding</a><br>
                                            @endif
                                            @if (isset($detail->ticket_item->bidding->signed_filepath))
                                                <a class="text-primary small text-nowrap" role="button"
                                                    onclick='window.open("/storage/{{ $detail->ticket_item->bidding->signed_filepath }}")'>tampilkan
                                                    file penawaran dengan ttd</a><br>
                                            @endif
                                            @if ($detail->ticket_item->ticket->fri_forms->count() > 0)
                                                @php
                                                    $fri_form = $detail->ticket_item->ticket->fri_forms->first();
                                                @endphp
                                                <a class="text-primary small text-nowrap" role="button"
                                                    onclick='window.open("/printfriform/{{ $fri_form->id }}")'>
                                                    tampilkan FRI</a><br>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $detail->uom ?? '-' }}</td>
                                    <td>{{ $detail->qty }}</td>
                                    <td class="rupiahDecimal text-nowrap">{{ $detail->price }}</td>
                                    <td class="rupiah text-nowrap">{{ $detail->qty * $detail->price }}</td>
                                    <td class="text-nowrap">{{ $detail->setup_date ?? '-' }}</td>
                                    <td class="text-justify">
                                        <div class="d-flex flex-column asset_field">
                                            @if (isset($detail->ticket_item->bidding))
                                                @if ($detail->ticket_item->bidding->price_notes && $detail->ticket_item->bidding->price_notes != '-')
                                                    <b>notes bidding harga</b>
                                                    <div style="white-space: pre-line; !important">
                                                        {{ $detail->ticket_item->bidding->price_notes }}</div>
                                                @endif
                                                @if ($detail->ticket_item->bidding->ketersediaan_barang_notes &&
                                                    $detail->ticket_item->bidding->ketersediaan_barang_notes != '-')
                                                    <b>notes keterangan barang</b>
                                                    <span
                                                        style="white-space: pre-line; !important">{{ $detail->ticket_item->bidding->ketersediaan_barang_notes }}</span>
                                                @endif
                                            @endif
                                            @if ($detail->ticket_item->pr_detail->notes)
                                                <b>Keterangan</b>
                                                <span
                                                    style="white-space: pre-line; !important">{{ $detail->ticket_item->pr_detail->notes ?? '-' }}</span>
                                            @endif
                                            @if ($ticket->status < 6)
                                                @if ($ticket->budget_type == 0)
                                                    @php
                                                        $isAsset = $detail->isAsset;
                                                    @endphp
                                                    <input type="hidden" name="item[{{ $key }}][isAsset]"
                                                        value="{{ $isAsset }}">
                                                    <span>Jenis Item :
                                                        <b>{{ $isAsset ? 'Asset' : 'Non Asset' }}</b></span>
                                                    @if ($isAsset)
                                                        <div class="form-group text-nowrap">
                                                            <label>Nomor Asset</label>
                                                            <textarea type="text" class="form-control" placeholder="cth: nomorasset1, nomorasset2, nomorasset3"
                                                                name="item[{{ $key }}][asset_numbers]"></textarea>
                                                            <small class="form-text text-muted">Masukkan nomor asset, jika
                                                                ada
                                                                beberapa nomor asset pisahkan dengan karakter koma
                                                                (,)
                                                            </small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Upload File Nomor Asset</label>
                                                            <input type="file" class="form-control-file"
                                                                name="item[{{ $key }}][asset_numbers_file]">
                                                            <small class="form-text text-muted">Pilih File Kelengkapan
                                                                berisi
                                                                informasi nomor asset item terkait</small>
                                                        </div>
                                                    @else
                                                    @endif
                                                @endif
                                                @if ($ticket->budget_type == 1)
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input assetnumber_check"
                                                                type="checkbox">
                                                            Apakah Asset ?
                                                        </label>
                                                    </div>
                                                    <input type="hidden" class="is_asset"
                                                        name="item[{{ $key }}][isAsset]" value="0">
                                                    <div class="form-group text-nowrap">
                                                        <label>Nomor Asset</label>
                                                        <textarea type="text" class="form-control assetnumber_input"
                                                            placeholder="cth: nomorasset1, nomorasset2, nomorasset3" name="item[{{ $key }}][asset_numbers]"
                                                            disabled></textarea>
                                                        <small class="form-text text-muted">Masukkan nomor asset, jika ada
                                                            beberapa nomor asset pisahkan dengan karakter koma (,)</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Upload File Nomor Asset</label>
                                                        <input type="file" class="form-control-file assetnumber_input"
                                                            name="item[{{ $key }}][asset_numbers_file]" disabled>
                                                        <small class="form-text text-muted">Pilih File Kelengkapan berisi
                                                            informasi nomor asset item terkait</small>
                                                    </div>
                                                @endif
                                            @else
                                                @if ($detail->asset_number || $detail->asset_number_filepath)
                                                    <b>Nomor Asset</b>
                                                    @if ($detail->asset_number)
                                                        <span>{{ $detail->asset_numbers_list_text }}</span>
                                                    @endif
                                                    @if ($detail->asset_number_filepath)
                                                        <a href="#"
                                                            onclick="window.open('/storage/{{ $detail->asset_number_filepath }}')">
                                                            lampiran nomor asset
                                                        </a>
                                                    @endif
                                                @endif
                                            @endif
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
                            {{-- @foreach ($ticket->ticket_item->where('isCancelled', '!=', true) as $key => $item)
                        <input type="hidden" name="item[{{$key}}][pr_detail_id]" value="{{$item->pr_detail->id}}">
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                {{$item->name}}
                            </td>
                            <td>{{$item->budget_pricing->uom ?? '-'}}</td>
                            <td>
                                {{$item->pr_detail->qty}}
                            </td>
                            @php
                                $total = 0;
                                $total += $item->pr_detail->qty * $item->pr_detail->price;
                                $total += $item->pr_detail->ongkir;
                                $total += $item->pr_detail->ongpas;
                                if($item->pr_detail->qty == 0){
                                    $total = 0;
                                }
                            @endphp
                            <td class="rupiah_text text-nowrap">
                                {{$item->pr_detail->price}}
                            </td>
                            <td class="rupiah_text text-nowrap item{{$key}} total" data-total="{{$total}}">
                                {{$total}}
                            </td>
                            <td class="text-nowrap">
                                {{$item->pr_detail->setup_date}}
                            </td>
                        </tr>
                    @endforeach --}}
                            <tr>
                                <td colspan="5"><b>Total</b></td>
                                <td class="rupiah_text grandtotal" data-grandtotal="{{ $grandtotal }}">
                                    {{ $grandtotal }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                    <center>
                        <h4>Otorisasi</h4>
                        <center>
                            <div class="d-flex justify-content-center">
                                <div class="d-flex align-items-center justify-content-center" id="authorization_field">
                                    @foreach ($ticket->pr->pr_authorizations as $key => $author)
                                        <div class="mr-3">
                                            <span class="font-weight-bold">{{ $author->employee_name }} --
                                                {{ $author->employee_position }}</span><br>
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
                                </div>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                @if ($ticket->pr->status == 2 || $ticket->pr->status == 1)
                                    <button type="button" onclick="window.open('/printPR/{{ $ticket->code }}')"
                                        class="btn btn-info ml-3">Cetak</button>
                                    <button type="button" onclick="revisePR({{ $ticket->pr->id }})"
                                        class="btn btn-secondary ml-3">Revisi PR</button>
                                    @if (Auth::user()->id == 1)
                                        {{-- superadmin only --}}
                                        <a href="/pr/{{ $ticket->code }}/updateassetnumber"
                                            class="btn btn-warning ml-3">Update Nomor Asset</a>
                                        <a href="/pr/{{ $ticket->code }}/updateprdata"
                                            class="btn btn-warning ml-3">Update PR Data</a>
                                    @endif
                                @endif
                                @if ($ticket->pr->status == 1)
                                    <button type="submit" class="btn btn-primary ml-3">Submit Nomor Asset Manual</button>
                                    {{-- <button type="button" class="btn btn-info ml-3" onclick="sendRequestAsset()">Kirim Request Nomor Asset</button> --}}
                                    {{-- <button type="button" class="btn btn-warning ml-3" onclick="window.open('/requestassetnumber/{{ $ticket->id }}/{{ $ticket->pr->id }}')">Kirim Ulang Request</button> --}}
                                @endif
                            </div>
                </div>
            </div>
        </form>
        <hr>
        <form method="post" id="submitform">
            @csrf
            <div></div>
        </form>

        <div class="modal fade" id="sendRequestAssetModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kirim Request Web Asset</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="/resendrequestassetnumber" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        <input type="hidden" name="pr_id" value="{{ $ticket->pr->id }}">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <h5>Pilih Item Request nomor asset</h5>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ticket->pr->pr_detail ?? [] as $key => $detail)
                                                <tr>
                                                    <td>{{ $detail->name }}</td>
                                                    <td>{{ $detail->qty }}</td>
                                                    <td class="rupiah">{{ $detail->price }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <h5>Attachment</h5>
                                @foreach ($ticket->all_attachments() as $attachment)
                                    <div class="col-12 text-wrap small"><a
                                            onclick="window.open('{{ $attachment->url }}')"
                                            href="#">{{ $attachment->name }}</a></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Kirim Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection
    @section('local-js')
        <script>
            const asset_number_request_data = @json($asset_number_request_data);
            $(document).ready(function() {
                $('input[type="number"]').change(function() {
                    autonumber($(this));
                });
                $('.assetnumber_check').change(function() {
                    if ($(this).prop('checked')) {
                        $(this).closest('.asset_field').find('.is_asset').val(1);
                        $(this).closest('.asset_field').find('.assetnumber_input').prop('disabled', false);
                    } else {
                        $(this).closest('.asset_field').find('.is_asset').val(0);
                        $(this).closest('.asset_field').find('.assetnumber_input').prop('disabled', true);
                    }
                    $(this).closest('.asset_field').find('.assetnumber_input').val('');
                });
            });

            function refreshItemTotal(this_el) {
                let classes = $(this_el).prop('class').split(' ');
                classes = classes.filter(function(item) {
                    if (item.includes('item')) {
                        return true;
                    } else {
                        return false;
                    }
                });
                let itemindex = classes[0].replace('item', '');
                let qty = $('.item' + itemindex + ':eq(0)').val();
                let price = autoNumeric_field[$('.rupiah').index($('.item' + itemindex + ':eq(1)'))].get();
                let ongkir = autoNumeric_field[$('.rupiah').index($('.item' + itemindex + ':eq(3)'))].get();
                let ongpas = autoNumeric_field[$('.rupiah').index($('.item' + itemindex + ':eq(4)'))].get();
                let total = (qty * parseFloat(price)) + parseFloat(ongkir) + parseFloat(ongpas);

                if (qty < 1) {
                    total = 0;
                    $('.item' + itemindex + ':eq(1)').prop('disabled', true);
                    $('.item' + itemindex + ':eq(3)').prop('disabled', true);
                    $('.item' + itemindex + ':eq(4)').prop('disabled', true);
                } else {
                    $('.item' + itemindex + ':eq(1)').prop('disabled', false);
                    $('.item' + itemindex + ':eq(3)').prop('disabled', false);
                    $('.item' + itemindex + ':eq(4)').prop('disabled', false);
                }

                $('.item' + itemindex + ':eq(2)').text(setRupiah(total));
                $('.item' + itemindex + ':eq(2)').data('total', total);
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

            function revisePR(pr_id) {
                if (asset_number_request_data != null) {
                    alert('Harus melakukan reject pada web Asset sebelum melakukan Revisi');
                }
                var reason = prompt(
                    "Status PR saat ini akan menjadi rejected dan harus melakukan pembuatan pr dari awal .Harap memasukan alasan revisi"
                );
                $('#submitform div').empty();
                if (reason != null) {
                    if (reason.trim() == '') {
                        alert("Alasan Harus diisi");
                        return
                    }
                    $('#submitform').prop('action', '/revisePR');
                    $('#submitform').prop('method', 'POST');
                    $('#submitform div').append('<input type="hidden" name="pr_id" value="' + pr_id + '">');
                    $('#submitform div').append('<input type="hidden" name="type" value="barangjasa">');
                    $('#submitform div').append('<input type="hidden" name="reason" value="' + reason + '">');
                    $('#submitform').submit();
                }
            }

            function sendRequestAsset() {
                if (asset_number_request_data.error == false) {
                    alert('Harus melakukan reject pada web Asset sebelum melakukan Request Asset Ulang');
                    return;
                }
                $('#sendRequestAssetModal').modal('show');
            }
        </script>
    @endsection
