<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"
        integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <style>
        .small {
            font-size: 10px;
        }

        .title {
            font-size: 20px !important;
        }

        .address {
            font-weight: bold;
            border-bottom: 1px solid;
            width: 200px;
        }

        .purchase_order {
            border: 1px solid black;
            font-size: 13px !important;
        }

        .purchase_order .title {
            font-size: 20px !important;
            font-weight: bold;
            color: white;
            background-color: black;
            padding: 0.2em;
        }

        .purchase_order .body {
            padding: 1em;
        }

        .vendor_address {
            height: 120px;
            font-size: 12px !important;
        }

        .kami_memesan_text {
            border-bottom: 1px solid black;
            width: 220 px;
        }

        .item_table {
            width: 100%;
            border: 1px solid black;
            padding-bottom: 2em;
        }

        .item_table thead {
            background-color: #DFD9DB;
        }

        .item_table thead th {
            padding: 1px 0px;
        }

        .item_table tbody td {
            padding: 15px 0;
        }

        .vcenter {
            vertical-align: top;
            float: none;
        }

        .sign_box {
            border: 1px solid black;
        }

        .sign_box .header {
            border-bottom: 1px solid black;
        }

        .sign_space {
            height: 100px !important
        }

        #watermark {
            position: absolute;
            left: 25%;
            top: 35%;
            z-index: -1000;

            font-size: 100px;
            font-weight: bold;
            transform: rotate(-30deg);
            letter-spacing: 20px;
        }
    </style>
</head>

<body>
    <div id="watermark">
        <span style="color: rgba(65, 65, 65, 0.329)">ASLI</span>
    </div>
    <div class="row mb-4">
        <div class="col-xs-6">
            <img src="{{ public_path('assets/logo.png') }}" width="40px">
            <span class="title">PT. Pinus Merah Abadi</span><br>
            <div class="address small">Jl. Soekarno Hatta 112<br>Babakan Ciparay, Babakan Ciparay<br>Bandung 40233 -
                Jawa Barat</div>
        </div>
    </div>
    <br>

    <div>Area :
        {{ $vendors->salespoint->name }}
    </div>
    <div>Periode Penilaian :
        {{ \Carbon\Carbon::parse($vendors->start_periode_penilaian)->translatedFormat('d F Y') }} -
        {{ \Carbon\Carbon::parse($vendors->end_periode_penilaian)->translatedFormat('d F Y') }}
    </div>
    <br>

    {{-- Pest Control --}}
    @if ($vendors->vendor == 0)
        <table class="table table-bordered table-sm small" id="form_table">
            <thead>
                <tr>
                    <th class="text-center" colspan="4">Form Penilaian Vendor Pest Control</th>
                </tr>
                <tr>
                    <th class="text-center">NO</th>
                    <th class="text-center">ASPEK PENILAIAN</th>
                    <th class="text-center">NILAI</th>
                    <th class="text-center">KETERANGAN NILAI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Harga : Harga Jasa yang Kompetitif</td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->harga_score }}
                    </td>
                    <td rowspan="1">
                        <div>
                            Nilai 3 = Selama Periode Penilaian, Harga yang diberikan lebih murah dari pesaing
                            lainnya.
                        </div>
                        <div>
                            Nilai 2 = Harga sama dengan pesaing lain namun Pest Control memiliki benefit lebih.
                        </div>
                        <div>
                            Nilai 1 = Harga lebih mahal dibanding pesaingnya
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>
                        <div>
                            Treatment Plan :
                        </div>
                        <div>
                            1. Rencana Treatment selalu di update setiap bulan
                        </div>
                        <div>
                            2. Menginformasikan mengenai teknik penanganan dan MSDS
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->treatment_plan_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama Periode Penilaian, Selalu memberikan seluruh informasi (100%) mengenai
                            treatment
                            plan & teknik yang digunakan.
                        </div>
                        <div>
                            Nilai 2 = Hanya memberikan 50% informasi mengenai treatment plan & teknik yang
                            digunakan.
                        </div>
                        <div>
                            Nilai 1 = Hanya memberikan kurang dari 50% informasi mengenai treatment plan & teknik
                            yang
                            digunakan.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>
                        <div>
                            Pelayanan :
                        </div>
                        <div>
                            1. Cepat tanggap terhadap penyelesaian komplain yang ada
                        </div>
                        <div>
                            2. Petugas lapangan mudah dihubungi dan bekerjasama dengan baik
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->pelayanan_rentokill_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Tidak pernah ada komplain.
                        </div>
                        <div>
                            Nilai 2 = Pernah ada komplain namun langsung diselesaikan oleh vendor.
                        </div>
                        <div>
                            Nilai 1 = Pernah ada komplain namun vendor tidak memberikan tanggapan & tindakan
                            penyelesaian.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>4</td>
                    <td>
                        <div>
                            Laporan :
                        </div>
                        <div>
                            1. Mengirimkan jadwal treatment tepat waktu
                        </div>
                        <div>
                            2. Selalu mengupdate petugas yang akan melakukan treatment
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->laporan_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama Periode Penilaian, Jadwal dan pelaporan treatment selalu dikirimkan
                            tepat
                            waktu.
                        </div>
                        <div>
                            Nilai 2 = Selama Periode Penilaian, Jadwal dan pelaporan treatment jarang dikirimkan
                            tepat
                            waktu.
                        </div>
                        <div>
                            Nilai 1 = Selama Periode Penilaian, Jadwal dan pelaporan treatment tidak pernah
                            dikirimkan
                            tepat
                            waktu.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>5</td>
                    <td>
                        <div>
                            Kelengkapan Adm :
                        </div>
                        <div>
                            Kelengkapan mengenai administrasi yang diperlukan
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->kelengkapan_adm_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Administrasi Lengkap.
                        </div>
                        <div>
                            Nilai 2 = Ada beberapa kelengkapan adm yang tidak dapat dipenuhi.
                        </div>
                        <div>
                            Nilai 1 = Tidak memenuhi persyaratan adm yang diminta.
                        </div>
                    </td>
                </tr>

                <tr class="text-center">
                    <td colspan="2"><b>Total</b></td>
                    <td>
                        {{ $vendors->vendor_evaluation_detail->kelengkapan_adm_score +
                            $vendors->vendor_evaluation_detail->laporan_score +
                            $vendors->vendor_evaluation_detail->pelayanan_rentokill_score +
                            $vendors->vendor_evaluation_detail->treatment_plan_score +
                            $vendors->vendor_evaluation_detail->harga_score }}
                    </td>
                    <td> </td>
                </tr>

                <tr>
                    <td colspan="4"><b>Nb: Mohon untuk mengisi kolom nilai 1 sampai 3 sesuai dengan keterangan
                            dikolom sebelahnya</b></td>
                </tr>
            </tbody>
        </table>

        {{-- CIT --}}
    @elseif ($vendors->vendor == 1)
        <table class="table table-bordered table-sm small" id="form_table">
            <thead>
                <tr>
                    <th class="text-center" colspan="5">Evaluasi Vendor Pengadaan Jasa Pick Up Service</th>
                </tr>
                <tr>
                    <th class="text-center">NO</th>
                    <th class="text-center">ASPEK PENILAIAN</th>
                    <th class="text-center">PARAMETER</th>
                    <th class="text-center">NILAI</th>
                    <th class="text-center">KETERANGAN</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Waktu Layanan</td>
                    <td>
                        <div>
                            1. Pengambilan uang tunai dilakukan setiap hari senin-jumat, kecuali utk ad hoc (sudah
                            ada kesepakatan sebelumnya)
                        </div>
                        <div>
                            2. Ketepatan waktu pengambilan uang tunai
                        </div>
                        <div>
                            3. Ketepatan waktu penyetoran uang tunai
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->waktu_penilaian_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama periode penilaian ketepatan window time sesuai dengan kesepatakan.
                        </div>
                        <div>
                            Nilai 2 = Selama periode penilaian ketepatan window time i (50%) sesuai dengan
                            kesepakatan.
                        </div>
                        <div>
                            Nilai 1 = Selama periode penilaian ketepatan window time < dari 50% sesuai dengan
                                kesepatakan. </div>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Koordinasi dan Komunikasi</td>
                    <td>
                        <div>
                            1. Cepat tanggap terhadap penyelesaian komplain dari perusahaan
                        </div>
                        <div>
                            2. Petugas lapangan mudah dihubungi dan bisa diajak kerjasama dengan baik
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Jika tidak pernah ada komplain yang diberikan oleh User.
                        </div>
                        <div>
                            Nilai 2 = Jika pernah ada komplain yang diberikan oleh User dan supplier langsung
                            memberikan
                            tanggapan dan tindakan perbaikan.
                        </div>
                        <div>
                            Nilai 1 = Jika pernah ada komplain yang diberikan oleh User dan supplier tidak
                            memberikan
                            tanggapan dan tindakan perbaikan.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Pelayanan</td>
                    <td>
                        <div>
                            1. Uang logam dan uang kertas disetor sesuai dengan serah terima
                        </div>
                        <div>
                            2. Nominal yang disetor sama dengan nominal yang tercatat di rekening koran
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->pelayanan_cit_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama periode penilaian, semua uang setoran di pick up, disetor dan tercatat
                            di RK
                            PMA.
                        </div>
                        <div>
                            Nilai 2 = Selama periode penilaian,semua uang setoran di pick up, disetor dan tercatat
                            di RK
                            PMA
                            kurang dari 80%.
                        </div>
                        <div>
                            Nilai 1 = Selama periode penilaian,semua uang setoran di pick up, disetor dan tercatat
                            di RK
                            PMA
                            kurang dari 50%.
                        </div>
                    </td>
                </tr>

                <tr class="text-center">
                    <td colspan="3">
                        <b>
                            <div>
                                TOTAL NILAI
                            </div>
                        </b>
                        <div>
                            (CATATAN : Nilai maksimum seluruh aspek = 9 )
                        </div>
                    </td>
                    <td>
                        {{ $vendors->vendor_evaluation_detail->waktu_penilaian_score +
                            $vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score +
                            $vendors->vendor_evaluation_detail->pelayanan_cit_score }}
                    </td>
                    <td> </td>
                </tr>
            </tbody>
        </table>

        {{-- Si cepat --}}
    @elseif ($vendors->vendor == 2)
        <table class="table table-bordered table-sm small" id="form_table">
            <thead>
                <tr>
                    <th class="text-center" colspan="5">Evaluasi Vendor Ekspedisi Si Cepat</th>
                </tr>
                <tr>
                    <th class="text-center">NO</th>
                    <th class="text-center">ASPEK PENILAIAN</th>
                    <th class="text-center">PARAMETER</th>
                    <th class="text-center">NILAI</th>
                    <th class="text-center">KETERANGAN</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Kuantitas</td>
                    <td>Kesesuaian jumlah barang/jasa/tenaga kerja yang dikirim dengan jumlah barang/jasa/tenaga
                        kerja yang diminta</td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->kuantitas_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama periode penilaian kuantitas barang yang datang selalu sama dengan yang
                            dipesan.
                        </div>
                        <div>
                            Nilai 2 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan yang
                            dipesan kurang dari 10 % dari total barang yang dibeli.
                        </div>
                        <div>
                            Nilai 1 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan yang
                            dipesan lebih dari 10 % dari total barang yang dibeli.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Kualitas</td>
                    <td>
                        <div>
                            1. Kualitas barang / jasa yang diminta
                        </div>
                        <div>
                            2. Kriteria keahlian tenaga kerja yang dimiliki sesuai dengan kebutuhan perusahaan
                            (untuk outsourcing)
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->kualitas_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 =
                        </div>
                        <div>
                            a. Selama periode penilaian tidak ada barang yang reject.
                        </div>
                        <div>
                            b. Keahlian tenaga kerja sesuai dengan kebutuhan perusahaan.
                        </div>
                        <div>
                            Nilai 2 =
                        </div>
                        <div>
                            a. Selama periode penilaian terdapat barang yang reject < 10 % dari total barang yang
                                dibeli. </div>
                                <div>
                                    b. Keahlian tenaga kerja kurang sesuai dengan kebutuhan perusahaan.
                                </div>
                                <div>
                                    Nilai 1 =
                                </div>
                                <div>
                                    a. Selama periode penilaian terdapat barang yang reject> 10 % dari total barang
                                    yang
                                    dibeli.
                                </div>
                                <div>
                                    b. Keahlian tenaga kerja tidak sesuai dengan kebutuhan perusahaan.
                                </div>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Waktu</td>
                    <td>
                        <div>
                            1. Ketepatan waktu pengiriman / penyelesaian pekerjaan
                        </div>
                        <div>
                            2. Ketepatan waktu pemenuhan permintaan tenaga kerja
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->waktu_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama periode penilaian, barang/jasa/tenaga kerja selalu dikirimkan atau
                            diselesaikan tepat waktu.
                        </div>
                        <div>
                            Nilai 2 = Selama periode penilaian, barang/jasa/tenaga kerja pernah dikirimkan atau
                            diselesaikan terlambat paling banyak < 10 % dari total transaksi. </div>
                                <div>
                                    Nilai 1= Selama periode penilaian, barang/jasa/tenaga kerja pernah dikirimkan
                                    atau
                                    diselesaikan terlambat> 10 % dari total transaksi.
                                </div>
                    </td>
                </tr>

                <tr>
                    <td>4</td>
                    <td>Pelayanan</td>
                    <td>
                        <div>
                            1. Cepat tanggap terhadap penyelesaian komplain dari perusahaan
                        </div>
                        <div>
                            2. PIC supplier mudah dihubungi dan bisa diajak kerjasama dengan baik
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->pelayanan_sicepat_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Jika tidak pernah ada komplain yang diberikan oleh User.
                        </div>
                        <div>
                            Nilai 2 = Jika pernah ada komplain yang diberikan oleh User dan supplier langsung
                            memberikan
                            tanggapan dan tindakan perbaikan.
                        </div>
                        <div>
                            Nilai 1 = Jika pernah ada komplain yang diberikan oleh User dan supplier tidak
                            memberikan
                            tanggapan dan tindakan perbaikan.
                        </div>
                    </td>
                </tr>

                <tr class="text-center">
                    <td colspan="3">
                        <b>
                            <div>
                                TOTAL NILAI
                            </div>
                        </b>
                        <div>
                            (CATATAN : Nilai maksimum seluruh aspek = 12 )
                        </div>
                    </td>
                    <td>
                        {{ $vendors->vendor_evaluation_detail->kuantitas_score +
                            $vendors->vendor_evaluation_detail->kualitas_score +
                            $vendors->vendor_evaluation_detail->waktu_score +
                            $vendors->vendor_evaluation_detail->pelayanan_sicepat_score }}
                    </td>
                    <td> </td>
                </tr>
            </tbody>
        </table>
    @else
        {{-- Ekspedisi --}}
        <table class="table table-bordered table-sm small" id="form_table">
            <thead>
                <tr>
                    <th class="text-center" colspan="5">Evaluasi Vendor Ekspedisi</th>
                </tr>
                <tr>
                    <th class="text-center">NO</th>
                    <th class="text-center">ASPEK PENILAIAN</th>
                    <th class="text-center">PARAMETER</th>
                    <th class="text-center">NILAI</th>
                    <th class="text-center">KETERANGAN</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Kuantitas</td>
                    <td>Kesesuaian jumlah barang/jasa/tenaga kerja yang dikirim dengan jumlah barang/jasa/tenaga
                        kerja yang diminta</td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->kuantitas_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama periode penilaian kuantitas barang yang datang selalu sama dengan yang
                            dipesan.
                        </div>
                        <div>
                            Nilai 2 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan yang
                            dipesan kurang dari 10 % dari total barang yang dibeli.
                        </div>
                        <div>
                            Nilai 1 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan yang
                            dipesan lebih dari 10 % dari total barang yang dibeli.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Kualitas</td>
                    <td>
                        <div>
                            1. Kualitas barang / jasa yang diminta
                        </div>
                        <div>
                            2. Kriteria keahlian tenaga kerja yang dimiliki sesuai dengan kebutuhan perusahaan
                            (untuk outsourcing)
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->kualitas_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 =
                        </div>
                        <div>
                            a. Selama periode penilaian tidak ada barang yang reject.
                        </div>
                        <div>
                            b. Keahlian tenaga kerja sesuai dengan kebutuhan perusahaan.
                        </div>
                        <div>
                            Nilai 2 =
                        </div>
                        <div>
                            a. Selama periode penilaian terdapat barang yang reject < 10 % dari total barang yang
                                dibeli. </div>
                                <div>
                                    b. Keahlian tenaga kerja kurang sesuai dengan kebutuhan perusahaan.
                                </div>
                                <div>
                                    Nilai 1 =
                                </div>
                                <div>
                                    a. Selama periode penilaian terdapat barang yang reject> 10 % dari total barang
                                    yang
                                    dibeli.
                                </div>
                                <div>
                                    b. Keahlian tenaga kerja tidak sesuai dengan kebutuhan perusahaan.
                                </div>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Waktu</td>
                    <td>
                        <div>
                            1. Ketepatan waktu pengiriman / penyelesaian pekerjaan
                        </div>
                        <div>
                            2. Ketepatan waktu pemenuhan permintaan tenaga kerja
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->waktu_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Selama periode penilaian, barang/jasa/tenaga kerja selalu dikirimkan atau
                            diselesaikan tepat waktu.
                        </div>
                        <div>
                            Nilai 2 = Selama periode penilaian, barang/jasa/tenaga kerja pernah dikirimkan atau
                            diselesaikan terlambat paling banyak < 10 % dari total transaksi. </div>
                                <div>
                                    Nilai 1= Selama periode penilaian, barang/jasa/tenaga kerja pernah dikirimkan
                                    atau
                                    diselesaikan terlambat> 10 % dari total transaksi.
                                </div>
                    </td>
                </tr>

                <tr>
                    <td>4</td>
                    <td>Pelayanan</td>
                    <td>
                        <div>
                            1. Cepat tanggap terhadap penyelesaian komplain dari perusahaan
                        </div>
                        <div>
                            2. PIC supplier mudah dihubungi dan bisa diajak kerjasama dengan baik
                        </div>
                    </td>
                    <td rowspan="1" align="center">
                        {{ $vendors->vendor_evaluation_detail->pelayanan_sicepat_score }}
                    </td>
                    <td>
                        <div>
                            Nilai 3 = Jika tidak pernah ada komplain yang diberikan oleh User.
                        </div>
                        <div>
                            Nilai 2 = Jika pernah ada komplain yang diberikan oleh User dan supplier langsung
                            memberikan
                            tanggapan dan tindakan perbaikan.
                        </div>
                        <div>
                            Nilai 1 = Jika pernah ada komplain yang diberikan oleh User dan supplier tidak
                            memberikan
                            tanggapan dan tindakan perbaikan.
                        </div>
                    </td>
                </tr>

                <tr class="text-center">
                    <td colspan="3">
                        <b>
                            <div>
                                TOTAL NILAI
                            </div>
                        </b>
                        <div>
                            (CATATAN : Nilai maksimum seluruh aspek = 12 )
                        </div>
                    </td>
                    <td>
                        {{ $vendors->vendor_evaluation_detail->kuantitas_score +
                            $vendors->vendor_evaluation_detail->kualitas_score +
                            $vendors->vendor_evaluation_detail->waktu_score +
                            $vendors->vendor_evaluation_detail->pelayanan_sicepat_score }}
                    </td>
                    <td> </td>
                </tr>
            </tbody>
        </table>
    @endif

    <table style="width: 100%">
        @php
            $names = ['Menilai', 'Mengetahui'];
            $vendors_evaluation_authorizations = $vendors->vendor_evaluation_authorizations;
            $authorizations = [];
            foreach ($vendors_evaluation_authorizations as $vendors_evaluation_authorization) {
                $auth = new \stdClass();
                $auth->employee_name = $vendors_evaluation_authorization->employee_name;
                $auth->employee_position = $vendors_evaluation_authorization->employee_position;
                array_push($authorizations, $auth);
            }
        @endphp
        <tr>
            @foreach ($vendors->vendor_evaluation_authorizations as $key => $authorization)
                <td style="padding: 1em 1em; width: 25%">
                    <div class="sign_box">
                        <div class="text-center header">
                            {{ $names[$key] }}<br>
                        </div>
                        <div class="sign_space "></div>
                        <div class="text-success text-center">
                            Approved
                        </div>
                        <div class="text-success text-center">
                            {{ $authorization->updated_at->translatedFormat('d F Y (H:i)') }}
                        </div>
                        <br>
                        <div class="text-center text-uppercase small">
                            {{ $authorization->employee_name }}
                            @if ($authorization->employee_name == '')
                                {!! '&nbsp;' !!}
                            @endif
                        </div>
                        <div class="text-center">
                            {{ $authorization->employee_position }}
                            @if ($authorization->employee_position == '')
                                {!! '&nbsp;' !!}
                            @endif
                        </div>
                        <br>
                    </div>
                </td>
            @endforeach
        </tr>
    </table>
    <span style="font-size:12px">{{ now()->format('Y-m-d H:i:s') }}</span>
</body>

</html>
