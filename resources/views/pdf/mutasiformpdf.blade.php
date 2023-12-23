<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
@php
@endphp
<body>
    <style>
        html { margin: 1em}
    </style>
    <h5 class="text-center">BERITA ACARA MUTASI INTERNAL ARMADA NIAGA/NON NIAGA</h5>
    <table class="table table-bordered table-sm">
        <tbody>
            <tr>
                <td width="25%" >No. BA Mutasi</td>
                <td width="75%" colspan="3">
                    {{ $mutasi_form->code }}
                </td>
            </tr>
            <tr>
                <td width="25%" >PMA Pengirim</td>
                <td width="25%">
                    {{ $mutasi_form->sender_salespoint_name }}
                </td>
                <td width="25%" >PMA Penerima</td>
                <td width="25%">
                    {{ $mutasi_form->receiver_salespoint_name }}
                </td>
            </tr>
            <tr>
                <td width="25%" >Tgl Mutasi</td>
                <td width="25%">
                    {{ $mutasi_form->mutation_date }}
                </td>
                <td width="25%" >Tgl Terima</td>
                <td width="25%">
                    {{ $mutasi_form->received_date }}
                </td>
            </tr>
        </tbody>
    </table>
    <div class="small">* No. BA Mutasi hanya berlaku untuk satu dokumen</span>
    <p class="small">Sehubungan dengan adanya perubahan Cabang/Depo/CP, maka dilakukan mutasi armada dengan rincian data armada sebagai berikut:</p>
    <table class="table table-sm table-borderless">
        <tbody>
            <tr>
                <td width="30%">No. Polisi</td>
                <td>{{ $mutasi_form->nopol }}</td>
            </tr>
            <tr>
                <td width="30%">Nama Pemilik (Vendor)</td>
                <td>{{ $mutasi_form->vendor_name }}</td>
            </tr>
            <tr>
                <td width="30%">Merk Kendaraan</td>
                <td>{{ $mutasi_form->brand_name }}</td>
            </tr>
            <tr>
                <td width="30%">Tipe/Jenis Kendaraan</td>
                <td>{{ $mutasi_form->jenis_kendaraan }}</td>
            </tr>
            <tr>
                <td width="30%">No. Rangka</td>
                <td>{{ $mutasi_form->nomor_rangka }}</td>
            </tr>
            <tr>
                <td width="30%">No. Mesin</td>
                <td>{{ $mutasi_form->nomor_mesin }}</td>
            </tr>
            <tr>
                <td width="30%">Tahun Pembuatan</td>
                <td>{{ $mutasi_form->tahun_pembuatan }}</td>
            </tr>
            <tr>
                <td width="30%">Masa Berlaku STNK</td>
                <td>{{ $mutasi_form->stnk_date }}</td>
            </tr>
        </tbody>
    </table>
    <div>Kelengkapan kendaraan: </div>
    
    <table class="table table-sm table-borderless">
        <tbody>
            <tr>
                <td width="30%">Kotak P3k</td>
                <td>{{ ($mutasi_form->p3k) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Segitiga Darurat</td>
                <td>{{ ($mutasi_form->segitiga) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Dongkrak</td>
                <td>{{ ($mutasi_form->dongkrak) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Tool Kit Standar</td>
                <td>{{ ($mutasi_form->toolkit) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Ban Serep</td>
                <td>{{ ($mutasi_form->ban) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Kunci Gembok</td>
                <td>{{ ($mutasi_form->gembok) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Ijin Bongkar</td>
                <td>{{ ($mutasi_form->bongkar) ? 'Ya' : 'Tidak' }}</td>
            </tr>
            <tr>
                <td width="30%">Buku Keur</td>
                <td>{{ ($mutasi_form->buku) ? 'Ya' : 'Tidak' }}</td>
            </tr>
        </tbody>
    </table>
    <p class="small">Semikian berita acara mutasi armada ini kamu buat untuk dapat digunakan sebagaimana mestinya. Terimakasih atas perhatian dan kerjasamanya.</p>
    <div class="row">
        {{ $mutasi_form->nama_tempat }}, {{ $mutasi_form->created_at->translatedFormat('d F Y') }}
    </div>
    <style>
        .sign_space{
            height: 125px;
        }
    </style>
    <div class="form-group mt-3">
    </div>
    
    <table class="table table-bordered authorization_table">
        <tbody>
            @php
                $count = $mutasi_form->authorizations->count();
                $headers_name = [];
                $headers_colspan = [];
                foreach($mutasi_form->authorizations as $authorization){
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
                @foreach($mutasi_form->authorizations as $authorization)
                    <td width="{{ 100/$count }}%" class="align-bottom small" style="height: 80px">
                        @if (($mutasi_form->current_authorization()->id ?? -1) == $authorization->id)
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
    <br>FRM-HCD-107-REV 03
</body>
</html>
