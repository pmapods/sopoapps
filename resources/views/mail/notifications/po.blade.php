@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>

@switch($type)
    @case("po_approved")
        @switch($type_name)
            @case("Pengadaan Barang Jasa")
                Dear Bapak/Ibu
                Terlampir adalah PO pengadaan barang/jasa yang telah full approval
                Mohon untuk mengirimkan realisasi ke HO Bandung
                Regards 
                Purchasing Staff  
                @break
            @case("Pengadaan Armada")
                Dear Bapak/Ibu
                Terlampir adalah PO pengadaan armada baru yang telah full approval
                Mohon bantuannya untuk memastikan penerimaan unit sesuai dengan tanggal setup
                Kami lampirkan juga form BASTK  agar di isi saat armada diterima dan di kirimkan kembali kepada kami.
                Regards 
                Purchasing Staff
                @break
            @case("Replace Armada")
                Dear Bapak/Ibu
                Terlampir adalah PO replace armada yang telah full approval
                Mohon bantuannya untuk memastikan penerimaan unit sesuai dengan tanggal setup
                Kami lampirkan juga form BASTK  agar di isi saat armada diterima dan di kirimkan kembali kepada kami.
                Regards 
                Purchasing Staff            
                @break
            @case("Renewal Armada")
                Dear Bapak/Ibu
                Terlampir adalah PO replace armada yang telah full approval
                Mohon bantuannya untuk memastikan penerimaan unit sesuai dengan tanggal setup
                Kami lampirkan juga form BASTK  agar di isi saat armada diterima dan di kirimkan kembali kepada kami.
                Regards 
                Purchasing Staff            
                @break
            @case("Mutasi Armada")
                Dear Bapak/Ibu
                Terlampir adalah PO mutasi armada yang telah full approval
                Mohon bantuannya untuk memastikan penerimaan unit sesuai dengan tanggal setup
                Kami lampirkan juga form BASTK agar di isi saat armada diterima dan di kirimkan kembali kepada kami.
                Regards 
                Purchasing Staff                      
                @break
            @default
                
        @endswitch   
        @break
    @default    
@endswitch

<br>
@if (config('app.env') != 'production')
DEVELOPMENT PODS<br>
=========================================<br>
original emails : {{ implode(', ',$original_emails) }}<br>
original ccs : {{ implode(', ',$original_ccs) }}<br>
=========================================<br>
@endif