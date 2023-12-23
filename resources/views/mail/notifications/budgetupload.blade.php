@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

@switch($type)
    @case("budget_approval")
        Dear Bapak/Ibu {{ $to }}<br>
        Mohon approvalnya atas Upload Budget {{ $budget_type }} terlampir <br>
        Informasi Budget :<br>
        ===============================<br>
        <b>Kode Budget</b> : {{ $budget->code }}<br>
        <b>Tanggal Dibuat</b> : {{ $budget->created_at->translatedFormat('d F Y')}}<br>
        <b>Detail Budget : </b><br>
        <ul>
            @foreach ($budget->budget_detail ?? [] as $item)
                @if ($budget_type == "Inventory")
                <li>{{$item->qty.' x '.$item->keterangan.' ('.$item->code.')  Rp.' .$item->value." / each"}}</li>
                @endif
                @if ($budget_type == "Armada")
                <li>{{$item->qty.' x '.$item->armada_type_name.' ('.$item->vendor_name.')  Rp.' .$item->value." / each"}}</li>
                @endif
                @if ($budget_type == "Assumption")
                <li>{{$item->qty.' x '.$item->name.' ('.$item->code.')  Rp.' .$item->value." / each"}}</li>
                @endif
            @endforeach
        </ul><br>
        ===============================<br>
        Regards<br>
        {{ $from }}<br>
        @break
    @case("budget_approved")
        Dear Bapak/Ibu {{ $to }}<br>
        Berikut informasi Full Approval atas Upload Budget {{ $budget_type }} terlampir <br>
        Informasi Budget :<br>
        ===============================<br>
        <b>Kode Budget</b> : {{ $budget->code }}<br>
        <b>Tanggal Dibuat</b> : {{ $budget->created_at->translatedFormat('d F Y')}}<br>
        <b>Detail Budget : </b><br>
        <ul>
            @foreach ($budget->budget_detail ?? [] as $item)
                @if ($budget_type == "Inventory")
                <li>{{$item->qty.' x '.$item->keterangan.' ('.$item->code.')  Rp.' .$item->value." / each"}}</li>
                @endif
                @if ($budget_type == "Armada")
                <li>{{$item->qty.' x '.$item->armada_type_name.' ('.$item->vendor_name.')  Rp.' .$item->value." / each"}}</li>
                @endif
                @if ($budget_type == "Assumption")
                <li>{{$item->qty.' x '.$item->name.' ('.$item->code.')  Rp.' .$item->value." / each"}}</li>
                @endif
            @endforeach
        </ul><br>
        ===============================<br>
        Regards<br>
        {{ $from }}<br>
        @break
    @case("budget_reject")
        Dear Bapak/Ibu {{ $to }}<br>
        Berikut terdapat reject approval. Mohon Revisinya untuk atas Upload Budget {{ $budget_type }} terlampir <br>
        Informasi Budget :<br>
        ===============================<br>
        <b>Kode Budget</b> : {{ $budget->code }}<br>
        <b>Tanggal Dibuat</b> : {{ $budget->created_at->translatedFormat('d F Y')}}<br>
        <b>Detail Budget : </b><br>
        <ul>
            @foreach ($budget->budget_detail ?? [] as $item)
                @if ($budget_type == "Inventory")
                <li>{{$item->qty.' x '.$item->keterangan.' ('.$item->code.')  Rp.' .$item->value." / each"}}</li>
                @endif
                @if ($budget_type == "Armada")
                <li>{{$item->qty.' x '.$item->armada_type_name.' ('.$item->vendor_name.')  Rp.' .$item->value." / each"}}</li>
                @endif
                @if ($budget_type == "Assumption")
                <li>{{$item->qty.' x '.$item->name.' ('.$item->code.')  Rp.' .$item->value." / each"}}</li>
                @endif
            @endforeach
        </ul><br>
        ===============================<br>
        di karenakan : {{ $data['reason'] }}<br>
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
original emails : {{ implode(', ',$original_emails) }}<br>
original ccs : {{ implode(', ',$original_ccs) }}<br>
=========================================<br>
@endif