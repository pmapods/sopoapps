<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    {{-- <link rel="stylesheet" href="{{public_path('css/pdfstyles.css')}}"> --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link rel="stylesheet" href="/fontawesome/css/all.min.css">
</head>
@php
    $personils = collect(json_decode($evaluasiform->personil));
    $array_a1 = [];
    $array_a2 = [];
    $array_a3 = [];
    foreach ($personils as $personil) {
        $values = explode(',',$personil->values);
        $array_a1 = array_merge($array_a1,array_slice($values,0,5));
        $array_a2 = array_merge($array_a2,array_slice($values,4,10));
        $array_a3 = array_merge($array_a3,array_slice($values,15,2));
    }
    // dd($array_a1,$array_a2,$array_a3);
    $rate_a1 = round(array_sum($array_a1) / count($array_a1),2); 
    $rate_a2 = round(array_sum($array_a2) / count($array_a2),2); 
    $rate_a3 = round(array_sum($array_a3) / count($array_a3),2); 

    $total_a1 = round($rate_a1 / 4 * 30,2);
    $total_a2 = round($rate_a2 / 4 * 20,2);
    $total_a3 = round($rate_a3 / 4 * 10,2);
@endphp
<style>
    .page_break { page-break-before: always; }
</style>
<body>
    <div><b>NAMA VENDOR : {{ $evaluasiform->vendor_name }}</b></div>
    <div><b>PERIODE PENILAIAN : {{ \Carbon\Carbon::parse($evaluasiform->security_ticket->period)->translatedFormat('F Y') }}</b></div>
    <div><b>CABANG/DEPO : {{ $evaluasiform->salespoint_name }}</b></div>
    <div style="margin-top:10px"><b>A. ASPEK PENILAIAN PERSONIL SECURITY</b></div>    
    <div>
        <table class="table table-bordered text-sm" style="font-size:10px !important">
            <thead>
                <tr>
                    <th class="bg-success text-light" rowspan="2" width="3%">NO</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">KATEGORI</th>
                    <th class="bg-success text-light" rowspan="2" width="">ITEM PENILAIAN</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">RATA-RATA</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">BOBOT</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">TOTAL NILAI</th>
                    <th class="bg-success text-light" colspan="5" width="35%">DETAIL PENILAIAN PERSONIL</th>
                </tr>
                <tr class="bg-success">
                    @for ($i = 0; $i < 5; $i++)
                        <td width="7%">
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $name = $personils->where('column_index',$i)->first()->name;
                                }else{
                                    $name = '-';
                                }
                            @endphp
                            {{ $name }}
                        </td>
                    @endfor
                </tr>
            </thead>
            @php
            $row_count = 0;
            @endphp
            <tbody>
                <tr>
                    <td rowspan="5">A.1</td>
                    <td rowspan="5">SIKAP</td>
                    <td>a) Kerapian & Penampilan Diri (Seragam & Atribut)</td>
                    <td rowspan="5">{{ $rate_a1 }}</td>
                    <td rowspan="5">30%</td>
                    <td rowspan="5">{{ $total_a1 }}%</td>
                    @for ($i = 0; $i < 5; $i++) 
                        <td>
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $value = explode(',',$personils->where('column_index',$i)->first()->values)[$row_count];
                                }else{
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                @php
                $a1_array_text = ['b) Pelayanan terhadap Karyawan & Tamu (Salam & Kesopanan)',
                'c) Ketepatan waktu hadir di lokasi (Kantor)',
                'd) Tegas, Dapat diandalkan, Sigap',
                'e) Kedisiplinan atas peraturan yang dijaga nya'];
                @endphp
                @foreach ($a1_array_text as $text)
                <tr style="line-height: 30px">
                    <td>{{ $text }}</td>
                    @for ($i = 0; $i < 5; $i++) 
                        <td>
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $value = explode(',',$personils->where('column_index',$i)->first()->values)[$row_count];
                                }else{
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                @endforeach

                <tr style="line-height: 30px">
                    <td rowspan="10">A.2</td>
                    <td rowspan="10">HASIL KERJA</td>
                    <td>a) Kepatuhan atas instruksi user</td>
                    <td rowspan="10">{{ $rate_a2 }}</td>
                    <td rowspan="10">20%</td>
                    <td rowspan="10">{{ $total_a2 }}%</td>
                    @for ($i = 0; $i < 5; $i++) 
                        <td>
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $value = explode(',',$personils->where('column_index',$i)->first()->values)[$row_count];
                                }else{
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>

                @php
                $a2_array_text = [
                    'b) Menjaga gudang steril dari orang yang tidak berkepentingan',
                    'c) Pencatatan Kilometer Kendaraan dengan benar',
                    'd) Pencatatan stock pada saat proses Loading & Unloading dengan benar',
                    'e) Penataan / pengaturan letak kendaraan bermotor (Mobil/Motor)',
                    'f) Mencatat Setiap Tamu yang Hadir dan melaporkan kepada orang dituju',
                    'g) Kunjungan (Patroli) Penanggung Jawab atau Koordinator Lapangan',
                    'h) Mampu bertugas dengan menerapkan standar-standar pengamanan yang baik',
                    'i) Komunikasi dengan Pihak Luar(RT/RW, Kelurahan, Aparat TNI & POLRI di sekitar)',
                    'j) Penjangaan terhadap Gerbang Utama / Kantor \'Tidak Pernah Kosong\' ',
                ];
                @endphp
                @foreach ($a2_array_text as $text)
                <tr style="line-height: 30px">
                    <td>{{ $text }}</td>
                    @for ($i = 0; $i < 5; $i++) 
                        <td>
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $value = explode(',',$personils->where('column_index',$i)->first()->values)[$row_count];
                                }else{
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                @endforeach

                <tr style="line-height: 30px">
                    <td rowspan="2">A.3</td>
                    <td rowspan="2">RESPON ATAS KEJADIAN</td>
                    <td>1. Update terhadap situasi dan kondisi pengamanan di Lokasi</td>
                    <td rowspan="2">{{ $rate_a3 }}</td>
                    <td rowspan="2">10%</td>
                    <td rowspan="2">{{ $total_a3 }}%</td>
                    @for ($i = 0; $i < 5; $i++) 
                        <td>
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $value = explode(',',$personils->where('column_index',$i)->first()->values)[$row_count];
                                }else{
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>

                <tr style="line-height: 30px">
                    <td>2. Respons atas komplain dari User / Tamu</td>
                    @for ($i = 0; $i < 5; $i++) 
                        <td>
                            @php
                                if ($personils->where('column_index',$i)->first() != null){
                                    $value = explode(',',$personils->where('column_index',$i)->first()->values)[$row_count];
                                }else{
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                <tr>
                    <td class="bg-success text-light" colspan="3">SUBTOTAL</td>
                    <td class="bg-success text-light text-right" colspan="3">{{ $total_a1 + $total_a2 + $total_a3 }}%</td>
                    <td class="bg-success" colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div><b>B. ASPEK PENILAIAN KELEMBAGAAN</b></div>
    <div>
        <table class="table table-sm table-bordered" style="font-size:10px !important">
            <thead>
                <tr>
                    <th class="bg-success text-light" width="5%">NO</th>
                    <th class="bg-success text-light">KATEGORI</th>
                    <th class="bg-success text-light">ITEM PENILAIAN</th>
                    <th class="bg-success text-light" width="8%">NILAI</th>
                    <th class="bg-success text-light" width="8%">BOBOT</th>
                    <th class="bg-success text-light" width="8%">TOTAL NILAI</th>
                    <th class="bg-success text-light">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $lists = [
                        [
                            'no' => 'B.1',
                            'category' => 'TRAINING',
                            'item' => 'Pemenuhan pelatihan personil security',
                            'bobot' => '15',
                        ],
                        [
                            'no' => 'B.2',
                            'category' => 'SUPERVISI',
                            'item' => 'Pemenuhan inspeksi / supervisi terhadap personil security',
                            'bobot' => '10',
                        ],
                        [
                            'no' => 'B.3',
                            'category' => 'ATRIBUT',
                            'item' => 'Pemenuhan atribut atau penggantian atribut jika ada kerusakan',
                            'bobot' => '15',
                        ],
                    ];
                    $lembaga = json_decode($evaluasiform->lembaga);
                    $total_lembaga = 0;
                @endphp
                @foreach ($lists as $key=>$list)
                <tr>
                    <td>{{ $list['no'] }}</td>
                    <td>{{ $list['category'] }}</td>
                    <td>{{ $list['item'] }}</td>
                    <td>
                        {{ $lembaga[$key]->nilai }}%
                    </td>
                    <td data-value="{{ $list['bobot'] }}">{{ $list['bobot'] }}%</td>
                    <td>
                        {{ intval($lembaga[$key]->nilai / 100 * $list['bobot'])}}%
                        @php
                            $total_lembaga += intval($lembaga[$key]->nilai / 100 * $list['bobot']);
                        @endphp
                    </td>
                    <td>
                        {!! nl2br(e($lembaga[$key]->keterangan)) !!}
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td class="text-light bg-success" colspan="5">SUBTOTAL</td>
                    <td class="text-light bg-success" colspan="2">{{ $total_lembaga }}%</td>
                </tr>
            </tbody>
        </table>
        <div>
            <table class="table" style="border-style: solid !important;">
                <tbody>
                    <tr>
                        <td>
                            <h5>TOTAL NILAI</h5>
                            <h5>(Catatan : Nilai Minimum 70%)</h5>
                        </td>
                        <td style="text-align:center;">
                            <h4>{{ $total_a1 + $total_a2 + $total_a3 + $total_lembaga }}%</h4>
                            <h3>
                                @if ($total_a1 + $total_a2 + $total_a3 + $total_lembaga >= 70)
                                    DIREKOMENDASIKAN
                                @else
                                    TIDAK DIREKOMENDASIKAN
                                @endif
                            </h3>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <table class="table">
            <tr>
                <td>
                    <span>KESIMPULAN <br> (Pilih salah satu)</span>
                </td>
                <td>
                    <input type="checkbox" @if ($evaluasiform->kesimpulan == 0) checked @endif style="margin-right:2px"><span>VENDOR DAN PERSONIL TETAP</span><br>
                    <input type="checkbox" @if ($evaluasiform->kesimpulan == 1) checked @endif style="margin-right:2px"><span>GANTI VENDOR</span><br>
                    <input type="checkbox" @if ($evaluasiform->kesimpulan == 2) checked @endif style="margin-right:2px"><span>GANTI PERSONIL SECURITY DENGAN VENDOR SAMA</span>
                </td>
            </tr>
        </table>
    </div>
    <div class="row">
        <div class="col-6 mt-2">
            <table class="table table-bordered">
                <tbody>
                    @php
                        $count = $evaluasiform->authorizations->count();
                        $headers_name = [];
                        $headers_colspan = [];
                        foreach($evaluasiform->authorizations as $authorization){
                            array_push($headers_name, $authorization->as);
                            array_push($headers_colspan, 1);
                            $last = $headers_name[count($headers_name)-1];
                            $before_last = $headers_name[count($headers_name)-2] ?? null;
                            // skip check first array
                            if($before_last == null){
                                continue;
                            }
                            if($last == $before_last){
                                array_pop($headers_name);
                                array_pop($headers_colspan);
                                $headers_colspan[count($headers_colspan)-1] += 1;
                            }
                        }
                    @endphp
                    <tr>
                        @foreach($headers_name as $key => $name)
                            <td class="align-middle small table-secondary" colspan="{{ $headers_colspan[$key] }}">{{ $name }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($evaluasiform->authorizations as $authorization)
                            <td width="{{ 100/$count }}%" class="align-bottom small" style="height: 80px">
                                @if (($evaluasiform->current_authorization()->id ?? -1) == $authorization->id)
                                    <span class="text-warning">Pending approval</span><br>
                                @endif
                                @if ($authorization->status == 1)
                                    <span class="text-success">Approved {{ $authorization->updated_at->format('Y-m-d (H:i)') }}</span><br>
                                @endif
                                {{ $authorization->employee_name }}<br>{{ $authorization->employee_position }}
                            </td>
                        @endforeach
                    </tr>   
                </tbody>
            </table>
        </div>
    </div>
    <br>FRM-HCD-096 REV 03
</body>
</html>
