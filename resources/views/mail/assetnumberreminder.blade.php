@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

Berikut merupakan Tiket Pengadaan yang perlu segera dilakukan proses IO / pengisian nomor asset untuk Salespoint
{{ $data['salespoint_name'] }} <br><br>
<style>
    tbody td {
        padding: 1em !important;
    }

</style>
<table>
    <thead>
        <tr>
            <th>Kode Tiket</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['ticket_list'] as $ticket)
            <tr>
                <td>{{ $ticket->code }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>
@if (config('app.env') != 'production')
    DEVELOPMENT PODS<br>
    =========================================<br>
    original emails : {{ implode(', ', $original_emails) }}<br>
    original ccs : {{ implode(', ', $original_ccs) }}<br>
    =========================================<br>
@endif
