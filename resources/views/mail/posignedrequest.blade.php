@php
    $logourl = public_path() . '/assets/logo_small.png';
    $signature = null;
    if(Auth::user()->signature_filepath){
        $signature = public_path() .'/storage/'.Auth::user()->signature_filepath;
    }
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

<p>
    {!! nl2br(e($data['email_text'])) !!}<br>
    @if ($data['needVendorConfirmation'])
    silahkan melengkapi tanda tangan basah dengan membuka link berikut.<br>
    <a href="{{$data['url']}}">KLIK DISINI</a><br>
    @endif
</p>
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