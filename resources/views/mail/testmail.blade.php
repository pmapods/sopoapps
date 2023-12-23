@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>
This is test email PODS with image