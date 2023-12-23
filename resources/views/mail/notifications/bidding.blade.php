@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

@php
    $custom_settings = json_decode($bidding->ticket->custom_settings);
@endphp


<img src="{{ $message->embed($logourl) }}"><br>
@switch($type)
    @case('bidding_approval')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Mohon bantuannnya untuk verifikasi form seleksi vendor barang / jasa dan kelengkapannya (terlampir)<br>
            Informasi Seleksi<br>
            ================================================<br>
            @if ($bidding->ticket->custom_settings != null)
                @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                    <b>Nama Pengaju</b> : {{ $bidding->ticket->created_by_employee->name }}<br>
                @endif
            @endif
            <b>Kode Ticket terkait</b> : {{ $bidding->ticket->code }}<br>
            <b>Nama Produk</b> : {{ $bidding->product_name }}<br>
            <b>Group</b> : {{ $bidding->group() }}<br>
            @foreach ($bidding->bidding_detail as $key => $detail)
                <b>Vendor {{ $key + 1 }}</b> : {{ $detail->ticket_vendor->name }}<br>
            @endforeach
            <b>Vendor Terpilih</b> : {{ $bidding->selected_vendor()->ticket_vendor->name }}<br>
            ================================================<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('bidding_approved')
        <p>
            @if ($bidding->ticket->custom_settings != null)
                @if (
                    $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP' ||
                        $custom_settings->ticket_name == 'Pengadaan Jasa Semprot Disinfectan' ||
                        $custom_settings->ticket_name == 'Pengadaan ATK' ||
                        $custom_settings->ticket_name == 'Pengadaan Jasa Asuransi Kesehatan' ||
                        $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan LOP' ||
                        $custom_settings->ticket_name == 'Sewa Hand Dryer' ||
                        $custom_settings->ticket_name == 'Disposal Inventaris' ||
                        $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                    Dear Bapak/Ibu Pengaju<br>
                    Terlampir adalah form seleksi vendor yang telah di verifikasi<br>
                    Informasi Seleksi<br>
                    ================================================<br>
                    @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                        <b>Nama Pengaju</b> : {{ $bidding->ticket->created_by_employee->name }}<br>
                    @endif
                    <b>Kode Ticket terkait</b> : {{ $bidding->ticket->code }}<br>
                    <b>Nama Produk</b> : {{ $bidding->product_name }}<br>
                    <b>Group</b> : {{ $bidding->group() }}<br>
                    @foreach ($bidding->bidding_detail as $key => $detail)
                        <b>Vendor {{ $key + 1 }}</b> : {{ $detail->ticket_vendor->name }}<br>
                    @endforeach
                    <b>Vendor Terpilih</b> : {{ $bidding->selected_vendor()->ticket_vendor->name }}<br>
                    ================================================<br>
                    Regards<br>
                    {{ $from }}<br>
                @elseif (
                    $custom_settings->ticket_name == 'Pengadaan Vendor Asuransi Asset' ||
                        $custom_settings->ticket_name == 'Penambahan Merchendiser' ||
                        $custom_settings->ticket_name == 'Ekspedisi Unit COP')
                    Dear Bapak/Ibu Pengaju<br>
                    Terlampir adalah form seleksi vendor yang telah di verifikasi Mohon bantuannya untuk proses approval PR
                    Manual pada
                    PODS<br>
                    Informasi Seleksi<br>
                    ================================================<br>
                    <b>Kode Ticket terkait</b> : {{ $bidding->ticket->code }}<br>
                    <b>Nama Produk</b> : {{ $bidding->product_name }}<br>
                    <b>Group</b> : {{ $bidding->group() }}<br>
                    @foreach ($bidding->bidding_detail as $key => $detail)
                        <b>Vendor {{ $key + 1 }}</b> : {{ $detail->ticket_vendor->name }}<br>
                    @endforeach
                    <b>Vendor Terpilih</b> : {{ $bidding->selected_vendor()->ticket_vendor->name }}<br>
                    ================================================<br>
                    Regards<br>
                    {{ $from }}<br>
                @endif
            @else
                Dear Bapak/Ibu {{ $to }}<br>
                Terlampir adalah form seleksi vendor yang telah di verifikasi Mohon bantuannya untuk proses approval PR Manual
                pada
                PODS<br>
                Informasi Seleksi<br>
                ================================================<br>
                <b>Kode Ticket terkait</b> : {{ $bidding->ticket->code }}<br>
                <b>Nama Produk</b> : {{ $bidding->product_name }}<br>
                <b>Group</b> : {{ $bidding->group() }}<br>
                @foreach ($bidding->bidding_detail as $key => $detail)
                    <b>Vendor {{ $key + 1 }}</b> : {{ $detail->ticket_vendor->name }}<br>
                @endforeach
                <b>Vendor Terpilih</b> : {{ $bidding->selected_vendor()->ticket_vendor->name }}<br>
                ================================================<br>
                Regards<br>
                {{ $from }}<br>
            @endif
        </p>
    @break

    @case('bidding_reject')
        Dear Bapak/Ibu {{ $to }}<br>
        Terkait verifikasi form seleksi vendor dan kelengkapannya, telah di reject<br>
        Informasi Seleksi<br>
        ================================================<br>
        @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
            <b>Nama Pengaju</b> : {{ $bidding->ticket->created_by_employee->name }}<br>
        @endif
        <b>Kode Ticket terkait</b> : {{ $bidding->ticket->code }}<br>
        <b>Nama Produk</b> : {{ $bidding->product_name }}<br>
        <b>Group</b> : {{ $bidding->group() }}<br>
        @foreach ($bidding->bidding_detail as $key => $detail)
            <b>Vendor {{ $key + 1 }}</b> : {{ $detail->ticket_vendor->name }}<br>
        @endforeach
        <b>Vendor Terpilih</b> : {{ $bidding->selected_vendor()->ticket_vendor->name }}<br>
        ================================================<br>
        di karenakan : {{ $data['reason'] }}<br>
        Regards<br>
        {{ $from }}
    @break

    @case('bidding_revision_file')
        Dear Bapak/Ibu {{ $to }}<br>
        Terkait kelengkapan berkas telah di reject, silahkan revisi lagi kelengkapan berkas yang di perlukan<br>
        di karenakan : {{ $data['reason'] }}<br>
        Informasi Bidding<br>
        ================================================<br>
        @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
            <b>Nama Pengaju</b> : {{ $bidding->ticket->created_by_employee->name }}<br>
        @endif
        <b>Kode Ticket terkait</b> : {{ $bidding->ticket->code }}<br>
        <b>Nama Produk</b> : {{ $bidding->product_name }}<br>
        <b>Group</b> : {{ $bidding->group() }}<br>
        ================================================<br>
        Regards<br>
        {{ $from }}
    @break

    @case('bidding_cancel')
        Dear Bapak/Ibu Purchasing Staff<br>
        Terkait verifikasi form seleksi vendor dan kelengkapannya, telah di cancel<br>
        di karenakan :<br>
        Free Text - terkait reject evaluasi vendor barang/jasa<br>
        Regards<br>
    @break

    @default
@endswitch

<br>
@if (config('app.env') != 'production')
    DEVELOPMENT PODS<br>
    =========================================<br>
    original emails : {{ implode(', ', $original_emails) }}<br>
    original ccs : {{ implode(', ', $original_ccs) }}<br>
    =========================================<br>
@endif
