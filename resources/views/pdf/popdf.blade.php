<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    {{-- <link rel="stylesheet" href="{{public_path('css/pdfstyles.css')}}"> --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
        integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <style>
        .small {
            font-size: 10px;
        }

        .title {
            font-size: 20px !important;
        }

        .address {
            font-weight: bold;
            border-bottom: 1px solid;
            width: 200px;
        }

        .purchase_order {
            border: 1px solid black;
            font-size: 13px !important;
        }

        .purchase_order .title {
            font-size: 20px !important;
            font-weight: bold;
            color: white;
            background-color: black;
            padding: 0.2em;
        }

        .purchase_order .body {
            padding: 1em;
        }

        .vendor_address {
            height: 120px;
            font-size: 12px !important;
        }

        .kami_memesan_text {
            border-bottom: 1px solid black;
            width: 220 px;
        }

        .item_table {
            width: 100%;
            border: 1px solid black;
            padding-bottom: 2em;
        }

        .item_table thead {
            background-color: #DFD9DB;
        }

        .item_table thead th {
            padding: 1px 0px;
        }

        .item_table tbody td {
            padding: 15px 0;
        }

        .vcenter {
            vertical-align: top;
            float: none;
        }

        .sign_box {
            border: 1px solid black;
        }

        .sign_box .header {
            border-bottom: 1px solid black;
        }

        .sign_space {
            height: 100px !important
        }

        #watermark {
            position: absolute;
            left: 25%;
            top: 35%;
            z-index: -1000;

            font-size: 100px;
            font-weight: bold;
            transform: rotate(-30deg);
            letter-spacing: 20px;
        }
    </style>
</head>
@php
    function setRupiah($amount)
    {
        $isNegative = false;
        if (floatval($amount) < 0) {
            $isNegative = true;
            $amount *= -1;
        }
        $reversed = str_split(strrev(strval(intval($amount))));
        $ctr = 0;
        $addedDots = '';
        foreach ($reversed as $i => $r) {
            $addedDots = $addedDots . $r;

            if (($i + 1) % 3 == 0 && $i < count($reversed) - 1) {
                $addedDots = $addedDots . '.';
            }
        }
        $addedDots = strrev($addedDots);
        $finalString = '';
        if ($isNegative) {
            $finalString = $finalString . '- ';
        }
        $finalString = $finalString . 'Rp ' . $addedDots;
        return $finalString;
    }

@endphp

<body>
    <div id="watermark">
        <span style="color: rgba(65, 65, 65, 0.329)">ASLI</span>
    </div>
    <div class="row">
        <div class="col-xs-6">
            <img src="{{ public_path('assets/logo.png') }}" width="40px">
            <span class="title">PT. Pinus Merah Abadi</span><br>
            <div class="address small">Jl. Soekarno Hatta 112<br>Babakan Ciparay, Babakan Ciparay<br>Bandung 40233 -
                Jawa Barat</div>
            <div class="vendor_address">
                <b>Kepada Yth / To :</b><br>
                {{ $po->sender_name }}<br>
                {!! nl2br(e($po->sender_address)) !!}
            </div>
            <div class="kami_memesan_text small">Kami memesan barang / produk sebagai berikut</div>
            <div class="small"><i>We would like to confirm our order as follows</i></div>
        </div>
        <div class="col-xs-5">
            <div class="purchase_order">
                <div class="title">Purchase Order</div>
                <div class="body">
                    <table>
                        <tbody>
                            <tr>
                                <td>No Purchase Order</td>
                                <td>&nbsp;:{{ $po->no_po_sap }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal / Date</td>
                                <td>&nbsp;:{{ $po->created_at->format('d.m.Y') }}</td>
                            </tr>
                            <tr>
                                <td>Ref. PR No.</td>
                                <td>&nbsp;:{{ $po->no_pr_sap }}</td>
                            </tr>
                            <tr>
                                <td>Pembayaran / Payment</td>
                                <td>&nbsp;:{{ $po->payment_days }} Hari / Days</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="small" style="margin-top: 1em">Alamat kirim / Delivery Address</div>
            @php
                $salespoint_name = '';
                if ($po->ticket_id != null) {
                    $salespoint_name = $po->ticket->salespoint->name;
                }
                if ($po->armada_ticket_id != null) {
                    $salespoint_name = $po->armada_ticket->salespoint->name;
                }
                if ($po->security_ticket_id != null) {
                    $salespoint_name = $po->security_ticket->salespoint->name;
                }
            @endphp
            <div>
                {{-- {{$salespoint_name}}<br> --}}
                {!! nl2br(e($po->send_address)) !!}
            </div>
        </div>
    </div>

    <table class="item_table" style="margin-top: 2em">
        <thead>
            <tr>
                <th style="font-size: 12px" rowspan="2">&nbsp; No.</th>
                <th style="font-size: 12px">Deskripsi Barang</th>
                <th style="font-size: 12px">Jumlah</th>
                <th style="font-size: 12px">Harga/ Unit</th>
                <th style="font-size: 12px" class="text-right">Jumlah</th>
                <th style="font-size: 12px" class="text-center">Tgl kirim</th>
            </tr>
            <tr>
                <th style="font-size: 12px"><i>Description of Goods</i></th>
                <th style="font-size: 12px"><i>Quantity</i></th>
                <th style="font-size: 12px"><i>Unit Price</i></th>
                <th style="font-size: 12px" class="text-right"><i>Amount</i></th>
                <th style="font-size: 12px" class="text-center"><i>Delivery Date</i></th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $subtotal = 0;
                $ppn = 0;
                $groups = $po->po_detail->groupBy('item_number');
                $groupPoDetail = $groups->map(function ($group) {
                    return (object) [
                        'item_number' => $group->first()['item_number'],
                        'item_name' => $group->first()['item_name'],
                        'item_description' => $group->first()['item_description'],
                        'qty' => $group->sum('qty'),
                        'uom' => $group->first()['uom'],
                        'item_price' => $group->first()['item_price'],
                        'delivery_notes' => $group->implode('delivery_notes', "\n"),
                    ];
                });
            @endphp
            @foreach ($groupPoDetail as $po_detail)
                <tr>
                    <td style="vertical-align:top; font-size:12px">&nbsp; {{ $po_detail->item_number }}</td>
                    <td style="vertical-align:top">
                        <span style="font-size:12px">{{ $po_detail->item_name }}</span><br>
                        <span style="font-size:11px">{!! nl2br(e($po_detail->item_description)) !!}</span>
                    </td>
                    <td style="vertical-align:top; font-size:12px">{{ $po_detail->qty }} {{ $po_detail->uom ?? '' }}
                    </td>
                    <td style="vertical-align:top; font-size:12px" class="rupiah_text">
                        {{ setRupiah($po_detail->item_price) }}</td>
                    <td style="vertical-align:top; font-size:12px" class="rupiah_text text-right">
                        {{ setRupiah($po_detail->qty * $po_detail->item_price) }}</td>
                    <td style="vertical-align:top; font-size:11px; padding-left:5px" class="text-center">
                        {!! nl2br(e($po_detail->delivery_notes)) !!}
                    </td>
                    @php $subtotal += $po_detail->qty*$po_detail->item_price; @endphp
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $ppn = (($po->ppn_percentage ?? 0) * $subtotal) / 100;
                $total = ceil($ppn + $subtotal);
            @endphp
            <tr>
                <td colspan="3"></td>
                <td>Subtotal</td>
                <td class="text-right rupiah_text">{{ setRupiah($subtotal) }}</td>
                <td></td>
            </tr>
            @if ($ppn > 0)
                <tr>
                    <td colspan="3"></td>
                    <td>PPN ({{ $po->ppn_percentage }}%)</td>
                    <td class="text-right rupiah_text">{{ setRupiah(ceil($ppn)) }}</td>
                    <td></td>
                </tr>
            @endif
            <tr>
                <td colspan="3"></td>
                <td style="border-bottom: 1px solid #000"><b>Jumlah Total</b></td>
                <td class="text-right rupiah_text"><b>{{ setRupiah($total) }}</b></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td><i>Total Amount</i></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    <div>
        <b>PASTIKAN UNTUK SELALU MEMINTA LPB PADA SAAT MELAKUKAN PENGIRIMAN/SELESAI MELAKUKAN JASA.</b><br>
        <u>Mohon PO ini diemailkan kembali setelah konfirmasi</u><br>
        <i>Please return this PO by email after signing</i><br>
        <u>Catatan</u><br>
        <i>{!! nl2br(e($po->notes)) !!}</i>
    </div>
    <table style="width: 100%">
        @php
            $names = ['Dibuat Oleh', 'Diperiksa dan disetujui oleh', 'Konfirmasi Supplier'];
            $enames = ['Created by', 'Checked and Approval by', 'Supplier Confirmation'];
            $po_authorizations = $po->po_authorization;
            $authorizations = [];
            foreach ($po_authorizations as $po_authorization) {
                $auth = new \stdClass();
                $auth->employee_name = $po_authorization->employee_name;
                $auth->employee_position = $po_authorization->employee_position;
                array_push($authorizations, $auth);
            }
            $auth = new \stdClass();
            $auth->employee_name = $po->supplier_pic_name;
            $auth->employee_position = $po->supplier_pic_position;
            array_push($authorizations, $auth);
        @endphp
        <tr>
            @foreach ($authorizations as $key => $authorization)
                <td style="padding: 1em 1em; width: 25%">
                    <div class="sign_box">
                        <div class="text-center header">
                            {{ $names[$key] }}<br>
                            <i>{{ $enames[$key] }}</i>
                        </div>
                        <div class="sign_space"></div>
                        <div class="text-center text-uppercase small">
                            {{ $authorization->employee_name }}
                            @if ($authorization->employee_name == '')
                                {!! '&nbsp;' !!}
                            @endif
                        </div>
                        <div class="text-center">
                            {{ $authorization->employee_position }}
                            @if ($authorization->employee_position == '')
                                {!! '&nbsp;' !!}
                            @endif
                        </div>
                    </div>
                </td>
            @endforeach
        </tr>
    </table>
    <span style="font-size:10px">{{ now()->format('Y-m-d H:i:s') }}</span>
</body>

</html>
