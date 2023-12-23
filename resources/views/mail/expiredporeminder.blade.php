@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

Berikut merupakan PO yang perlu segera di proses untuk Salespoint {{ $data["salespoint_name"] }} <br><br>
<style>
</style>
<table style="border-collapse: collapse; border: 1px solid;">
    <thead>
        <tr>
            <th style="border: 1px solid;">PO</th>
            <th style="border: 1px solid;">Tipe PO</th>
            <th style="border: 1px solid;">PO Expired Date</th>
            @if(strpos($transaction_type, "Armada") !== false)
                <th style="border: 1px solid;">Jenis Unit</th>
                <th style="border: 1px solid;">Nopol GS</th>
                <th style="border: 1px solid;">Nopol GT</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($data["pos_list"] as $po)
        <tr>
            <td style="border: 1px solid;">{{ $po->po_number }}</td>
            <td style="border: 1px solid;">{{ $po->type }}</td>
            <td style="border: 1px solid;">{{ $po->end_date }}</td>
            @if(strpos($transaction_type, "Armada") !== false)
                <td style="border: 1px solid;">{{ $po->vehicle_type ?? '-' }}</td>
                <td style="border: 1px solid;">{{ $po->gs_plate ?? '-' }}</td>
                <td style="border: 1px solid;">{{ $po->gt_plate ?? '-' }}</td>
            @endif

        </tr>
        @endforeach
    </tbody>
</table>
<br>
<b>
@if(strpos(strtolower($transaction_type), "armada") !== false)
Form perpanjangan yang telah full approval max kami terima tanggal 10 setiap bulannya<br>
Lewat dr tanggal tersebut akan terblock secara sistem dan WAJIB mengupload BA utk proses unblock
@endif
@if(strpos(strtolower($transaction_type), "security") !== false)
Form perpanjangan yang telah full approval max kami terima tanggal 05 setiap bulannya<br>				
Lewat dr tanggal tersebut akan terblock secara sistem dan WAJIB mengupload BA utk proses unblock
@endif
@if(strpos(strtolower($transaction_type), "cit") !== false)
Form perpanjangan yang telah full approval max kami terima tanggal 05 setiap bulannya<br>				
Lewat dr tanggal tersebut akan terblock secara sistem dan WAJIB mengupload BA utk proses unblock
@endif
@if(strpos(strtolower($transaction_type), "pest control") !== false)
Form perpanjangan yang telah full approval max kami terima tanggal 10 setiap bulannya<br>							
Lewat dr tanggal tersebut akan terblock secara sistem dan WAJIB mengupload BA utk proses unblock
@endif
</b><br>

@if (config('app.env') != 'production')
DEVELOPMENT PODS<br>
=========================================<br>
original emails : {{ implode(', ',$original_emails) }}<br>
original ccs : {{ implode(', ',$original_ccs) }}<br>
=========================================<br>
@endif