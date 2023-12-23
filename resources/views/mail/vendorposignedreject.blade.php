@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

Vendor "{{ $data['po_upload_request']->vendor_name }}" melaporkan kesalahan pada :<br>
Informasi Kesalahan :<br>
===============================<br>
<b>PO</b> : {{ $data['po']->no_po_sap }}<br>
<b>Vendor PIC</b> : {{ $data['po_upload_request']->vendor_pic }}<br>
<b>Notes Kesalahan</b> : {{ $data['po_upload_request']->reject_notes }}<br>
<b>Dilaporkan Oleh</b> : {{ $data['po_upload_request']->rejected_by }}<br>
<b>Detail PO : </b><br>
<ul>
    @foreach ($data['po']->po_detail ?? [] as $po_detail)   
        <li>{{$po_detail->qty.' '.$po_detail->uom.' x '.$po_detail->item_name.'  Rp.' .$po_detail->item_price." / each"}}</li>
    @endforeach
</ul><br>
===============================<br>
<br>
@if (config('app.env') != 'production')
DEVELOPMENT PODS<br>
=========================================<br>
original emails : {{ implode(', ',$original_emails) }}<br>
original ccs : {{ implode(', ',$original_ccs) }}<br>
=========================================<br>
@endif