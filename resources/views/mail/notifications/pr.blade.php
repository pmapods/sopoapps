@php
    if ($pr->ticket_id != null) {
        $code = $pr->ticket->code;
        $po_before = $pr->ticket->po_reference_number;
        $custom_settings = json_decode($pr->ticket->custom_settings);
    }
    if ($pr->armada_ticket_id != null) {
        $code = $pr->armada_ticket->code;
        $po_before = $pr->armada_ticket->po_reference_number;
    }
    if ($pr->security_ticket_id != null) {
        $code = $pr->security_ticket->code;
        $po_before = $pr->security_ticket->po_reference_number;
    }
@endphp
@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

@switch($type)
    @case('pr_approval')
        Dear Bapak/Ibu {{ $to }}<br>
        Mohon approvalnya atas PR Manual {{ $type_name }} terlampir <br>
        Informasi PR :<br>
        ===============================<br>
        @if ($pr->ticket_id != null)
            @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                <b>Nama Pengaju</b> : {{ $pr->ticket->created_by_employee->name }}<br>
            @endif
        @endif
        <b>Kode Tiket</b> : {{ $code }}<br>
        <b>Tanggal Dibuat</b> : {{ $pr->created_at->translatedFormat('d F Y') }}<br>
        <b>Jenis Budget</b> : {{ $pr->isBudget() == true ? 'Budget' : 'Non Budget' }}<br>
        <b>Daftar Item : </b><br>
        <ul>
            @foreach ($pr->pr_detail as $item)
                <li>{{ $item->qty . ' ' . $item->uom . ' ' . $item->name . ' Rp.' . $item->price }}</li>
            @endforeach
        </ul><br>
        ===============================<br>
        Regards<br>
        {{ $from }}<br>
    @break

    @case('pr_approved')
        Dear Bapak/Ibu {{ $to }}<br>
        Terlampir adalah PR Manual {{ $type_name }} yang telah full approval<br>
        Informasi PR :<br>
        ===============================<br>
        @if ($pr->ticket_id != null)
            @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                <b>Nama Pengaju</b> : {{ $pr->ticket->created_by_employee->name }}<br>
            @endif
        @endif
        <b>Kode Tiket</b> : {{ $code }}<br>
        <b>Tanggal Dibuat</b> : {{ $pr->created_at->translatedFormat('d F Y') }}<br>
        <b>Jenis Budget</b> : {{ $pr->isBudget() == true ? 'Budget' : 'Non Budget' }}<br>
        <b>Daftar Item : </b><br>
        <ul>
            @foreach ($pr->pr_detail as $item)
                <li>{{ $item->qty . ' ' . $item->uom . ' ' . $item->name . ' Rp.' . $item->price }}</li>
            @endforeach
        </ul><br>
        ===============================<br>
        Regards<br>
        {{ $from }}<br>
    @break

    @case('pr_reject')
        Dea Bapak/Ibur {{ $to }}<br>
        Terkait request approval PR Manual, telah di reject<br>
        Informasi PR :<br>
        ===============================<br>
        @if ($pr->ticket_id != null)
            @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                <b>Nama Pengaju</b> : {{ $pr->ticket->created_by_employee->name }}<br>
            @endif
        @endif
        <b>Kode Tiket</b> : {{ $code }}<br>
        <b>Tanggal Dibuat</b> : {{ $pr->created_at->translatedFormat('d F Y') }}<br>
        <b>Jenis Budget</b> : {{ $pr->isBudget() == true ? 'Budget' : 'Non Budget' }}<br>
        <b>Daftar Item : </b><br>
        <ul>
            @foreach ($pr->pr_detail()->withTrashed()->get() as $item)
                <li>{{ $item->qty . ' ' . $item->uom . ' ' . $item->name . ' Rp.' . $item->price }}</li>
            @endforeach
        </ul><br>
        ===============================<br>
        di karenakan : {{ $data['reason'] }}<br>
        Regards<br>
        {{ $from }}<br>
    @break

    @case('pr_cancel')
        Dear Bapak/Ibu {{ $to }}<br>
        Terkait request approval PR Manual, telah di cancel<br>
        Informasi PR :<br>
        ===============================<br>
        @if ($pr->ticket_id != null)
            @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                <b>Nama Pengaju</b> : {{ $pr->ticket->created_by_employee->name }}<br>
            @endif
        @endif
        <b>Kode Tiket</b> : {{ $code }}<br>
        <b>Tanggal Dibuat</b> : {{ $pr->created_at->translatedFormat('d F Y') }}<br>
        <b>Jenis Budget</b> : {{ $pr->isBudget() == true ? 'Budget' : 'Non Budget' }}<br>
        <b>Daftar Item : </b><br>
        <ul>
            @foreach ($pr->pr_detail as $item)
                <li>{{ $item->qty . ' ' . $item->uom . ' ' . $item->name . ' Rp.' . $item->price }}</li>
            @endforeach
        </ul><br>
        ===============================<br>
        di karenakan : {{ $data['reason'] }}<br>
        Regards<br>
        {{ $from }}<br>
    @break

    @case('pr_ga')
        Dear Bapak/Ibu {{ $to }},<br>
        Mohon bantuannya untuk dibuatkan PR SAP terkait {{ $type_name }},<br>
        Informasi Pengadaan <br>
        ===============================<br>
        @if ($pr->ticket_id != null)
            @if ($custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                <b>Nama Pengaju</b> : {{ $pr->ticket->created_by_employee->name }}<br>
            @endif
        @endif
        <b>Kode Tiket</b> : {{ $code }}<br>
        <b>Tanggal Dibuat</b> : {{ $pr->created_at->translatedFormat('d F Y') }}<br>
        <b>Jenis Budget</b> : {{ $pr->isBudget() == true ? 'Budget' : 'Non Budget' }}<br>
        @if (count($pr->setup_dates()) > 0)
            <b>Tanggal Setup</b> : {{ implode(', ', $pr->setup_dates()) }}<br>
        @endif
        @if ($po_before != null)
            <b>PO Sebelumnya</b> : {{ $po_before }}<br>
        @endif
        ===============================<br>
        Regards<br>
        {{ $from }}<br>
    @break

    @default
    @break

@endswitch

<br>
@if (config('app.env') != 'production')
    DEVELOPMENT PODS<br>
    =========================================<br>
    original emails : {{ implode(', ', $original_emails) }}<br>
    original ccs : {{ implode(', ', $original_ccs) }}<br>
    =========================================<br>
@endif
