@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

<div>
    Dear Bpk/Ibu Tim Purchasing & IT
</div>
<div>
    Berikut merupakan Ticket Barang Jasa Jenis IT Yang Reminder End Date H-30
</div>

<br><br>
<style>
</style>

<table style="border-collapse: collapse; border: 1px solid;">
    <thead>
        <tr>
            <th style="border: 1px solid;">No</th>
            <th style="border: 1px solid;">Sales Point</th>
            <th style="border: 1px solid;">Kode Pengadaan</th>
            <th style="border: 1px solid;">Tanggal Pengajuan</th>
            <th style="border: 1px solid;">Nama Pengaju</th>
            <th style="border: 1px solid;">Reminder End Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['pos_list'] as $po)
            <tr>
                <td style="border: 1px solid;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid;">{{ $po->salespoint }}</td>
                <td style="border: 1px solid;">{{ $po->code }}</td>
                <td style="border: 1px solid;">{{ $po->created_at }}</td>
                <td style="border: 1px solid;">{{ $po->created_by }}</td>
                <td style="border: 1px solid;">{{ $po->end_date }}</td>
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
