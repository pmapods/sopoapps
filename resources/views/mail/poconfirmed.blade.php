@php
    $logourl = public_path() . '/assets/logo_small.png';
    $signature = null;
    if(Auth::user()->signature_filepath){
        $signature = public_path() .'/storage/'.Auth::user()->signature_filepath;
    }
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

Upload tanda tangan untuk po {{$data['po']->no_po_sap}} telah di confirm oleh tim Pinus Merah Abadi. Bukti PO yang sudah lengkap di tanda tangan terlampir di attachment. Terimakasih.

<br>
@if ($signature)
<img src="{{ $message->embed($signature) }}"><br>
<br>
@endif
@if (config('app.env') != 'production')
DEVELOPMENT PODS<br>
=========================================<br>
original emails : {{ implode(', ',$original_emails) }}<br>
original ccs : {{ implode(', ',$original_ccs) }}<br>
=========================================<br>
@endif