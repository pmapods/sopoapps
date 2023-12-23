@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

<div>
    Dear Bpk/Ibu Tim Purchasing
</div>
<div>
    Berikut merupakan PO Manual yang Harus di Lengkapi File Attachmentnya
</div>

<br><br>
<style>
</style>

<table style="border-collapse: collapse; border: 1px solid;">
    <thead>
        <tr>
            <th style="border: 1px solid;">No</th>
            <th style="border: 1px solid;">PO Number</th>
            <th style="border: 1px solid;">Sales Point</th>
            <th style="border: 1px solid;">Category Name</th>
            <th style="border: 1px solid;">Item Name</th>
            <th style="border: 1px solid;">Vendor Name</th>
            <th style="border: 1px solid;">Armada Name</th>
            <th style="border: 1px solid;">GS Plate</th>
            <th style="border: 1px solid;">GT Plate</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['pos_list'] as $po)
            <tr>
                <td style="border: 1px solid;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid;">{{ $po->po_number }}</td>
                <td style="border: 1px solid;">{{ $po->salespoint_name }}</td>
                <td style="border: 1px solid;">{{ $po->category_name }}</td>
                <td style="border: 1px solid;">{{ $po->item_name ? $po->item_name : '-' }}</td>
                <td style="border: 1px solid;">{{ $po->vendor_name ? $po->vendor_name : '-' }}</td>
                <td style="border: 1px solid;">{{ $po->armada_name }}</td>
                <td style="border: 1px solid;">{{ $po->gs_plate }}</td>
                <td style="border: 1px solid;">{{ $po->gt_plate }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<br>
<b>
</b><br>

@if (config('app.env') != 'production')
    DEVELOPMENT PODS<br>
    =========================================<br>
    original emails : {{ implode(', ', $original_emails) }}<br>
    =========================================<br>
@endif
