<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    {{-- <link rel="stylesheet" href="{{public_path('css/pdfstyles.css')}}"> --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    {{-- <link rel="stylesheet" href="/fontawesome/css/all.min.css"> --}}
</head>
<body>
    <style>
        html { margin: 1em}
    </style>
    <table class="table table-bordered">
        <tbody>
            <tr>
                <td width="70%" style="font-size: 1em" class="text-center font-weight-bold">
                    FORMULIR<br>FASILITAS KARYAWAN & PERLENGKAPAN KERJA KARYAWAN BARU
                </td>
                <td width="30%" class="small">
                    Tanggal<br>{{ $facility_form->created_at->format('Y-m-d') }}<br>Nomor<br>{{ $facility_form->code }}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table table-bordered table-sm small">
        <tr>
            <td width="20%" class="table-secondary">Nama</td>
            <td colspan="3">{{ $facility_form->nama}}</td>
        </tr>
        <tr>
            <td width="20%" class="table-secondary">Divisi/Dept/Bag</td>
            <td width="30%">{{ $facility_form->divisi }}</td>
            <td width="20%" class="table-secondary">Telephone</td>
            <td width="30%">{{ $facility_form->phone }}</td>
        </tr>
        <tr>
            <td width="20%" class="table-secondary">Jabatan</td>
            <td width="30%">{{ $facility_form->jabatan }}</td>
            <td width="20%" class="table-secondary">Tanggal Masuk</td>
            <td width="30%">{{ $facility_form->tanggal_mulai_kerja }}</td>
        </tr>
        <tr>
            <td width="20%" class="table-secondary">HO/Cabang/Depo</td>
            <td width="30%">{{ $facility_form->salespoint->name }}</td>
            <td width="20%" class="table-secondary">Golongan</td>
            <td width="30%">{{ $facility_form->golongan }}</td>
        </tr>
        <tr>
            <td width="20%" class="table-secondary">Status Karyawan</td>
            <td colspan="3">
                <input type="checkbox" @if($facility_form->status_karyawan == "percobaan") checked @endif id="percobaan"> <label for="percobaan">Percobaan</label>
                <input type="checkbox"  @if($facility_form->status_karyawan == "tetap") checked @endif id="tetap"> <label for="tetap">Tetap</label>
            </td>
        </tr>
        <tr>
            <td width="20%" class="table-secondary">Fasilitas & Perlengkapan Kerja</td>
            <td colspan="3">
                @php
                    $list = json_decode($facility_form->facilitylist);
                    $namelist = [
                        "Ruangan, lokasi",
                        "Pesawat telepon",
                        "Meja & Kursi",
                        "Line & Telepon",
                        "PC / LOP",
                        "Kartu Nama",
                        "Mobil Dinas",
                        "ATK & perlengkapan kerja",
                        "Rumah Dinas",
                        "Lemari Arsip / Filling Kabinet / Whiteboard",
                        "Akses Internet",
                        "ID Card",
                        "Akses email Pinus Merah Abadi"
                    ];
                @endphp
                <table class="table table-borderless table-sm">
                    <tbody>
                        @foreach ($namelist as $key=>$name)
                            @if($key % 2 == 0) <tr> @endif
                                <td>
                                    <input type="checkbox" @if (in_array( $key+1 ,$list)) checked @endif id="keylist{{ $key }}"> 
                                    <label for="keylist{{ $key }}">{{ $name }}</label>
                                </td>
                            @if($key+1 % 2 == 0) </tr> @endif
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td width="20%" class="table-secondary">Catatan</td>
            <td colspan="3">
                {{ $facility_form->notes }}
            </td>
        </tr>
    </table>
    <div class="small">*Jenis Fasilitas yang disiapkan adalah standa berdasarkan Surat keputusan Direksi mengenai Standar Kompensasi dan Benefit</div>
    
    <table class="table table-sm table-bordered text-center mt-3">
        <tbody>
            <tr>
                @foreach ($facility_form->facility_form_authorizations as $authorization)
                    <td class="align-middle small table-secondary">{{ $authorization->as }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach ($facility_form->facility_form_authorizations as $authorization)
                    <td width="50%" class="align-bottom small" style="height: 80px">
                        @if (($facility_form->current_authorization()->id ?? -1) == $authorization->id)
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
    <br>FRM-HCD-114 REV 00
</body>
</html>
