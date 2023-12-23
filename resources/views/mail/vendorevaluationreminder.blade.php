@php
    $logourl = public_path() . '/assets/logo_small.png';
    $start_date = date('Y-m-01', strtotime('-1 months'));
    $end_date = date('Y-m-t', strtotime('-1 months'));
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

<div>
    Dear Bpk/Ibu Tim Area
</div>
<div>
    Berikut merupakan area yang harus melakukan penilaian Evaluasi Vendor di PODS
</div>

<br><br>
<style>
</style>

<table style="border-collapse: collapse; border: 1px solid;">
    <thead>
        <tr>
            <th style="border: 1px solid;">Periode Penilaian</th>
            <th style="border: 1px solid;">Sales Point</th>
            <th style="border: 1px solid;">Vendor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['pos_list'] as $po)
            <tr>
                <td style="border: 1px solid;">{{ $start_date }} - {{ $end_date }}</td>
                <td style="border: 1px solid;">{{ $po->salespoint_name }}</td>
                <td style="border: 1px solid;">{{ $po->sender_name ?? $po->vendor_name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<br>
<b>
    @if (strpos(strtolower($transaction_type), 'cit') !== false)
        <div>
            Maksimal input penilaian tanggal 10 setiap bulannya
        </div>
        <br>
        <div>
            Terima Kasih
        </div>
        <br>
    @endif
    @if (strpos(strtolower($transaction_type), 'pest control') !== false)
        <div>
            Maksimal input penilaian tanggal 10 setiap bulannya
        </div>
        <br>
        <div>
            Terima Kasih
        </div>
        <br>
    @endif
</b><br>

@if (config('app.env') != 'production')
    DEVELOPMENT PODS<br>
    =========================================<br>
    original emails : {{ implode(', ', $original_emails) }}<br>
    original ccs : {{ implode(', ', $original_ccs) }}<br>
    =========================================<br>
@endif
