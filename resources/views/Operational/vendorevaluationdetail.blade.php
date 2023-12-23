@extends('Layout.app')

@section('local-css')
    <style>
        #form_table thead {
            border: 1px solid #000 !important;
        }

        #form_table td,
        #form_table th {
            vertical-align: middle !important;
            /* border: 2px solid #000 !important; */
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fal fa-arrow-left" aria-hidden="true" style="cursor: pointer;"
                            onclick="window.location.href='/vendor-evaluation/'"></i>
                        Form Vendor Evaluation ({{ $vendors->code }})
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Vendor Evaluation</li>
                        <li class="breadcrumb-item">{{ $vendors->code }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mt-3">
    </div>
    <div class="content-body px-4">
        <form action="/vendor-evaluation/approve/{{ $vendors->id }}" method="post" enctype="multipart/form-data"
            id="approve_button">
            @csrf
        </form>

        <form action="/vendor-evaluation/addvendorevaluationdetail/{{ $vendors->id }}" method="post"
            enctype="multipart/form-data" id="add_vendor">
            @csrf
            <div class="row">
                <div class="col-md-2 mt-3">Tanggal Pengajuan</div>
                <div class="col-md-4 mt-3">
                    <input type="text" class="form-control" value="{{ $vendors->created_at->translatedFormat('d F Y') }}"
                        readonly>
                </div>

                <div class="col-md-2 mt-3">Area / SalesPoint</div>
                <div class="col-md-4 mt-3">
                    <input type="text" class="form-control" name="salespoint_id" value="{{ $vendors->salespoint->name }}"
                        readonly>
                </div>

                <div class="col-md-2 mt-3">Pembuat Form</div>
                <div class="col-md-4 mt-3">
                    <input type="text" class="form-control" value="{{ $vendors->created_by_employee->name ?? '-' }}"
                        readonly>
                </div>

                <div class="col-md-2 mt-3">Vendor</div>
                <div class="col-md-4 mt-3">
                    @if ($vendors->vendor == 0)
                        <input type="text" class="form-control" value="Pest Control" readonly>
                    @elseif ($vendors->vendor == 1)
                        <input type="text" class="form-control" value="CIT" readonly>
                    @elseif ($vendors->vendor == 2)
                        <input type="text" class="form-control" value="Si Cepat" readonly>
                    @else
                        <input type="text" class="form-control" value="Ekspedisi" readonly>
                    @endif
                </div>

                <div class="col-md-2 mt-3">Start Periode Penilaian</div>
                <div class="col-md-4 mt-3">
                    <input type="text" class="form-control"
                        value="{{ \Carbon\Carbon::parse($vendors->start_periode_penilaian)->translatedFormat('d F Y') }}"
                        readonly>
                </div>

                <div class="col-md-2 mt-3">End Periode Penilaian</div>
                <div class="col-md-4 mt-3">
                    <input type="text" class="form-control"
                        value="{{ \Carbon\Carbon::parse($vendors->end_periode_penilaian)->translatedFormat('d F Y') }}"
                        readonly>
                </div>
            </div>

            <div class="col-md-4 mt-3">
            </div>

            {{-- Pest Control --}}
            @if ($vendors->vendor == 0)
                <table class="table table-bordered table-sm small" id="form_table">
                    <thead>
                        <tr>
                            <th class="text-center" colspan="5">Form Penilaian Vendor Pest Control</th>

                        </tr>
                        <tr>
                            <th class="text-center text-danger" colspan="5">Jika NILAI dari ASPEK PENILAIAN Kurang dari 3
                                Maka WAJIB Mengisi
                                Kolom ALASAN</th>
                        </tr>
                        <tr>
                            <th class="text-center" width="1%">NO</th>
                            <th class="text-center" width="19%">ASPEK PENILAIAN</th>
                            <th class="text-center" width="10%">NILAI</th>
                            <th class="text-center" width="40%">KETERANGAN NILAI</th>
                            <th class="text-center" width="30%">ALASAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Harga : Harga Jasa yang Kompetitif</td>
                            <td rowspan="1">
                                <select class="form-control nilai" name="harga_score" id="harga_score_id"
                                    {{ isset($vendors->vendor_evaluation_detail->harga_score) ? 'disabled' : '' }} required>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->harga_score) && $vendors->vendor_evaluation_detail->harga_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->harga_score) && $vendors->vendor_evaluation_detail->harga_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->harga_score) && $vendors->vendor_evaluation_detail->harga_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="harga_score_reason"
                                    id="harga_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->harga_score_reason) ? $vendors->vendor_evaluation_detail->harga_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="treatment_plan_score" id="treatment_plan_score_id"
                                    {{ isset($vendors->vendor_evaluation_detail->treatment_plan_score) ? 'disabled' : '' }}
                                    required>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->treatment_plan_score) && $vendors->vendor_evaluation_detail->treatment_plan_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->treatment_plan_score) && $vendors->vendor_evaluation_detail->treatment_plan_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->treatment_plan_score) && $vendors->vendor_evaluation_detail->treatment_plan_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
                            </td>
                            <td>
                                <div>
                                    Nilai 3 = Selama Periode Penilaian, Selalu memberikan seluruh informasi (100%)
                                    mengenai
                                    treatment
                                    plan & teknik yang digunakan.
                                </div>
                                <div>
                                    Nilai 2 = Hanya memberikan 50% informasi mengenai treatment plan & teknik yang
                                    digunakan.
                                </div>
                                <div>
                                    Nilai 1 = Hanya memberikan kurang dari 50% informasi mengenai treatment plan &
                                    teknik
                                    yang
                                    digunakan.
                                </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="treatment_plan_score_reason" id="treatment_plan_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->treatment_plan_score_reason) ? $vendors->vendor_evaluation_detail->treatment_plan_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="pelayanan_rentokill_score"
                                    id="pelayanan_rentokill_score_id"
                                    {{ isset($vendors->vendor_evaluation_detail->pelayanan_rentokill_score) ? 'disabled' : '' }}
                                    required>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_rentokill_score) && $vendors->vendor_evaluation_detail->pelayanan_rentokill_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_rentokill_score) && $vendors->vendor_evaluation_detail->pelayanan_rentokill_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_rentokill_score) && $vendors->vendor_evaluation_detail->pelayanan_rentokill_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="pelayanan_rentokill_reason" id="pelayanan_rentokill_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->pelayanan_rentokill_reason) ? $vendors->vendor_evaluation_detail->pelayanan_rentokill_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="laporan_score" id="laporan_score_id"
                                    {{ isset($vendors->vendor_evaluation_detail->laporan_score) ? 'disabled' : '' }}
                                    required>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->laporan_score) && $vendors->vendor_evaluation_detail->laporan_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->laporan_score) && $vendors->vendor_evaluation_detail->laporan_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->laporan_score) && $vendors->vendor_evaluation_detail->laporan_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="laporan_score_reason"
                                    id="laporan_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->laporan_score_reason) ? $vendors->vendor_evaluation_detail->laporan_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="kelengkapan_adm_score"
                                    id="kelengkapan_adm_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->kelengkapan_adm_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->kelengkapan_adm_score) && $vendors->vendor_evaluation_detail->kelengkapan_adm_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->kelengkapan_adm_score) && $vendors->vendor_evaluation_detail->kelengkapan_adm_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->kelengkapan_adm_score) && $vendors->vendor_evaluation_detail->kelengkapan_adm_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="kelengkapan_adm_score_reason" id="kelengkapan_adm_score_reason" disabled
                                    required
                                    value="{{ isset($vendors->vendor_evaluation_detail->kelengkapan_adm_score_reason) ? $vendors->vendor_evaluation_detail->kelengkapan_adm_score_reason : '' }}">
                            </td>
                        </tr>

                        <tr class="text-center">
                            <td colspan="2"><b>Total</b></td>
                            <td id="total" name="total"><b>0</b></td>
                            <td> </td>
                        </tr>

                        <tr>
                            <td colspan="4"><b>Nb: Mohon untuk mengisi kolom nilai 1 sampai 3 sesuai dengan
                                    keterangan
                                    dikolom sebelahnya</b></td>
                        </tr>
                    </tbody>
                </table>

                {{-- CIT --}}
            @elseif ($vendors->vendor == 1)
                <table class="table table-bordered table-sm small" id="form_table">
                    <thead>
                        <tr>
                            <th class="text-center" colspan="6">Evaluasi Vendor Pengadaan Jasa Pick Up Service</th>
                        </tr>
                        <tr>
                            <th class="text-center text-danger" colspan="6">Jika NILAI dari ASPEK PENILAIAN Kurang dari
                                3
                                Maka WAJIB Mengisi
                                Kolom ALASAN</th>
                        </tr>
                        <tr>
                            <th class="text-center" width="1%">NO</th>
                            <th class="text-center" width="10%">ASPEK PENILAIAN</th>
                            <th class="text-center" width="20%">PARAMETER</th>
                            <th class="text-center" width="10%">NILAI</th>
                            <th class="text-center" width="34%">KETERANGAN</th>
                            <th class="text-center" width="25%">ALASAN</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Waktu Layanan</td>
                            <td>
                                <div>
                                    1. Pengambilan uang tunai dilakukan setiap hari senin-jumat, kecuali utk ad hoc
                                    (sudah
                                    ada kesepakatan sebelumnya)
                                </div>
                                <div>
                                    2. Ketepatan waktu pengambilan uang tunai
                                </div>
                                <div>
                                    3. Ketepatan waktu penyetoran uang tunai
                                </div>
                            </td>
                            <td>
                                <select class="form-control nilai" name="waktu_penilaian_score"
                                    id="waktu_penilaian_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->waktu_penilaian_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_penilaian_score) && $vendors->vendor_evaluation_detail->waktu_penilaian_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_penilaian_score) && $vendors->vendor_evaluation_detail->waktu_penilaian_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_penilaian_score) && $vendors->vendor_evaluation_detail->waktu_penilaian_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="waktu_penilaian_score_reason" id="waktu_penilaian_score_reason" disabled
                                    required
                                    value="{{ isset($vendors->vendor_evaluation_detail->waktu_penilaian_score_reason) ? $vendors->vendor_evaluation_detail->waktu_penilaian_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="koordinasi_dan_komunikasi_score"
                                    id="koordinasi_dan_komunikasi_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score) && $vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score) && $vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score) && $vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="koordinasi_dan_komunikasi_score_reason"
                                    id="koordinasi_dan_komunikasi_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score_reason) ? $vendors->vendor_evaluation_detail->koordinasi_dan_komunikasi_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="pelayanan_cit_score" id="pelayanan_cit_score_id"
                                    required
                                    {{ isset($vendors->vendor_evaluation_detail->pelayanan_cit_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_cit_score) && $vendors->vendor_evaluation_detail->pelayanan_cit_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_cit_score) && $vendors->vendor_evaluation_detail->pelayanan_cit_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_cit_score) && $vendors->vendor_evaluation_detail->pelayanan_cit_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
                            </td>
                            <td>
                                <div>
                                    Nilai 3 = Selama periode penilaian, semua uang setoran di pick up, disetor dan
                                    tercatat
                                    di RK
                                    PMA.
                                </div>
                                <div>
                                    Nilai 2 = Selama periode penilaian,semua uang setoran di pick up, disetor dan
                                    tercatat
                                    di RK
                                    PMA
                                    kurang dari 80%.
                                </div>
                                <div>
                                    Nilai 1 = Selama periode penilaian,semua uang setoran di pick up, disetor dan
                                    tercatat
                                    di RK
                                    PMA
                                    kurang dari 50%.
                                </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="pelayanan_cit_score_reason" id="pelayanan_cit_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->pelayanan_cit_score_reason) ? $vendors->vendor_evaluation_detail->pelayanan_cit_score_reason : '' }}">
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
                            <td id="total" name="total">0</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Si cepat --}}
            @elseif ($vendors->vendor == 2)
                <table class="table table-bordered table-sm small" id="form_table">
                    <thead>
                        <tr>
                            <th class="text-center" colspan="6">Evaluasi Vendor Ekspedisi Si Cepat</th>
                        </tr>
                        <tr>
                            <th class="text-center text-danger" colspan="6">Jika NILAI dari ASPEK PENILAIAN Kurang dari
                                3
                                Maka WAJIB Mengisi
                                Kolom ALASAN</th>
                        </tr>
                        <tr>
                            <th class="text-center" width="1%">NO</th>
                            <th class="text-center" width="5%">ASPEK PENILAIAN</th>
                            <th class="text-center" width="20%">PARAMETER</th>
                            <th class="text-center" width="10%">NILAI</th>
                            <th class="text-center" width="39%">KETERANGAN</th>
                            <th class="text-center" width="25%">ALASAN</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Kuantitas</td>
                            <td>Kesesuaian jumlah barang/jasa/tenaga kerja yang dikirim dengan jumlah barang/jasa/tenaga
                                kerja yang diminta</td>
                            <td>
                                <select class="form-control nilai" name="kuantitas_score" id="kuantitas_score_id"
                                    required
                                    {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) && $vendors->vendor_evaluation_detail->kuantitas_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) && $vendors->vendor_evaluation_detail->kuantitas_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) && $vendors->vendor_evaluation_detail->kuantitas_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
                            </td>
                            <td>
                                <div>
                                    Nilai 3 = Selama periode penilaian kuantitas barang yang datang selalu sama dengan
                                    yang
                                    dipesan.
                                </div>
                                <div>
                                    Nilai 2 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan
                                    yang
                                    dipesan kurang dari 10 % dari total barang yang dibeli.
                                </div>
                                <div>
                                    Nilai 1 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan
                                    yang
                                    dipesan lebih dari 10 % dari total barang yang dibeli.
                                </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="kuantitas_score_reason"
                                    id="kuantitas_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->kuantitas_score_reason) ? $vendors->vendor_evaluation_detail->kuantitas_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="kualitas_score" id="kualitas_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->kualitas_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->kualitas_score) && $vendors->vendor_evaluation_detail->kualitas_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->kualitas_score) && $vendors->vendor_evaluation_detail->kualitas_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->kualitas_score) && $vendors->vendor_evaluation_detail->kualitas_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                                            a. Selama periode penilaian terdapat barang yang reject> 10 % dari total
                                            barang
                                            yang
                                            dibeli.
                                        </div>
                                        <div>
                                            b. Keahlian tenaga kerja tidak sesuai dengan kebutuhan perusahaan.
                                        </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="kualitas_score_reason"
                                    id="kualitas_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->kualitas_score_reason) ? $vendors->vendor_evaluation_detail->kualitas_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="waktu_score" id="waktu_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->waktu_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_score) && $vendors->vendor_evaluation_detail->waktu_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_score) && $vendors->vendor_evaluation_detail->waktu_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_score) && $vendors->vendor_evaluation_detail->waktu_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                                            Nilai 1= Selama periode penilaian, barang/jasa/tenaga kerja pernah
                                            dikirimkan
                                            atau
                                            diselesaikan terlambat> 10 % dari total transaksi.
                                        </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="waktu_score_reason"
                                    id="waktu_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->waktu_score_reason) ? $vendors->vendor_evaluation_detail->waktu_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="pelayanan_sicepat_score"
                                    id="pelayanan_sicepat_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) && $vendors->vendor_evaluation_detail->pelayanan_sicepat_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) && $vendors->vendor_evaluation_detail->pelayanan_sicepat_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) && $vendors->vendor_evaluation_detail->pelayanan_sicepat_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="pelayanan_sicepat_score_reason" id="pelayanan_sicepat_score_reason" disabled
                                    required
                                    value="{{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score_reason) ? $vendors->vendor_evaluation_detail->pelayanan_sicepat_score_reason : '' }}">
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
                            <td id="total" name="total">0</td>
                        </tr>
                    </tbody>
                </table>
            @else
                {{-- {{Ekspedisi}} --}}

                <table class="table table-bordered table-sm small" id="form_table">
                    <thead>
                        <tr>
                            <th class="text-center" colspan="6">Evaluasi Vendor Ekspedisi</th>
                        </tr>
                        <tr>
                            <th class="text-center text-danger" colspan="6">Jika NILAI dari ASPEK PENILAIAN Kurang dari
                                3
                                Maka WAJIB Mengisi
                                Kolom ALASAN</th>
                        </tr>
                        <tr>
                            <th class="text-center" width="1%">NO</th>
                            <th class="text-center" width="5%">ASPEK PENILAIAN</th>
                            <th class="text-center" width="20%">PARAMETER</th>
                            <th class="text-center" width="10%">NILAI</th>
                            <th class="text-center" width="39%">KETERANGAN</th>
                            <th class="text-center" width="25%">ALASAN</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Kuantitas</td>
                            <td>Kesesuaian jumlah barang/jasa/tenaga kerja yang dikirim dengan jumlah barang/jasa/tenaga
                                kerja yang diminta</td>
                            <td>
                                <select class="form-control nilai" name="kuantitas_score" id="kuantitas_score_id"
                                    required
                                    {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) && $vendors->vendor_evaluation_detail->kuantitas_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) && $vendors->vendor_evaluation_detail->kuantitas_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->kuantitas_score) && $vendors->vendor_evaluation_detail->kuantitas_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
                            </td>
                            <td>
                                <div>
                                    Nilai 3 = Selama periode penilaian kuantitas barang yang datang selalu sama dengan
                                    yang
                                    dipesan.
                                </div>
                                <div>
                                    Nilai 2 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan
                                    yang
                                    dipesan kurang dari 10 % dari total barang yang dibeli.
                                </div>
                                <div>
                                    Nilai 1 = Selama periode penilaian kuantitas barang yang datang tidak sama dengan
                                    yang
                                    dipesan lebih dari 10 % dari total barang yang dibeli.
                                </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="kuantitas_score_reason"
                                    id="kuantitas_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->kuantitas_score_reason) ? $vendors->vendor_evaluation_detail->kuantitas_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="kualitas_score" id="kualitas_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->kualitas_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->kualitas_score) && $vendors->vendor_evaluation_detail->kualitas_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->kualitas_score) && $vendors->vendor_evaluation_detail->kualitas_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->kualitas_score) && $vendors->vendor_evaluation_detail->kualitas_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                                            a. Selama periode penilaian terdapat barang yang reject> 10 % dari total
                                            barang
                                            yang
                                            dibeli.
                                        </div>
                                        <div>
                                            b. Keahlian tenaga kerja tidak sesuai dengan kebutuhan perusahaan.
                                        </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="kualitas_score_reason"
                                    id="kualitas_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->kualitas_score_reason) ? $vendors->vendor_evaluation_detail->kualitas_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="waktu_score" id="waktu_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->waktu_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_score) && $vendors->vendor_evaluation_detail->waktu_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_score) && $vendors->vendor_evaluation_detail->waktu_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->waktu_score) && $vendors->vendor_evaluation_detail->waktu_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                                            Nilai 1= Selama periode penilaian, barang/jasa/tenaga kerja pernah
                                            dikirimkan
                                            atau
                                            diselesaikan terlambat> 10 % dari total transaksi.
                                        </div>
                            </td>
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai" name="waktu_score_reason"
                                    id="waktu_score_reason" disabled required
                                    value="{{ isset($vendors->vendor_evaluation_detail->waktu_score_reason) ? $vendors->vendor_evaluation_detail->waktu_score_reason : '' }}">
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
                            <td>
                                <select class="form-control nilai" name="pelayanan_sicepat_score"
                                    id="pelayanan_sicepat_score_id" required
                                    {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Nilai --</option>
                                    <option value="3"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) && $vendors->vendor_evaluation_detail->pelayanan_sicepat_score == 3 ? 'selected' : '' }}>
                                        3</option>
                                    <option value="2"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) && $vendors->vendor_evaluation_detail->pelayanan_sicepat_score == 2 ? 'selected' : '' }}>
                                        2</option>
                                    <option value="1"
                                        {{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score) && $vendors->vendor_evaluation_detail->pelayanan_sicepat_score == 1 ? 'selected' : '' }}>
                                        1</option>
                                    </option>
                                </select>
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
                            <td rowspan="1">
                                <input type="text-area" class="form-control reason_nilai"
                                    name="pelayanan_sicepat_score_reason" id="pelayanan_sicepat_score_reason" disabled
                                    required
                                    value="{{ isset($vendors->vendor_evaluation_detail->pelayanan_sicepat_score_reason) ? $vendors->vendor_evaluation_detail->pelayanan_sicepat_score_reason : '' }}">
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
                            <td id="total" name="total">0</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <table class="table table-borderless table-sm">
                @if ($vendors->status == 2 || $vendors->status == 3)
                    <div class="d-flex align-items-center justify-content-center text-center">
                        @foreach ($vendors->vendor_evaluation_authorizations as $key => $authorization)
                            <div class="mr-3">
                                <span class="font-weight-bold">{{ $authorization->employee_name }} --
                                    {{ $authorization->employee_position }}</span><br>
                                @if ($authorization->status == 1)
                                    <span class="text-success">Approved</span><br>
                                    <span
                                        class="text-success">{{ $authorization->updated_at->translatedFormat('d F Y (H:i)') }}</span><br>
                                @endif
                                @if ($authorization->status == 0)
                                    <span class="text-warning">Menunggu Approval</span><br>
                                @endif
                                <span>{{ $authorization->as }}</span>
                            </div>
                            @if (count($vendors->vendor_evaluation_authorizations) - 1 != $key)
                                <i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>
                            @endif
                        @endforeach
                    </div>
                    <div class="col-md-4 mt-3">
                    </div>
                @else
                    @if ($vendors->status != 4)
                        <div class="form-group">
                            <label for="">Pilih Matriks Approval</label>
                            <select class="form-control select2 authorization_select2" required id="authorization_awal"
                                name="authorization">
                                <option value="">Pilih Matriks Approval</option>
                                @foreach ($authorizations as $authorization)
                                    @php
                                        $list = $authorization->authorization_detail;
                                        $string = '';
                                        foreach ($list as $key => $author) {
                                            $string = $string . $author->employee->name;
                                            $open = $author->employee_position;
                                            if (count($list) - 1 != $key) {
                                                $string = $string . ' -> ';
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $authorization->id }}" data-list="{{ $list }}">
                                        {{ $string }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif

                <center>
                    @if ($vendors->status == 1)
                        <input type="submit" class="btn btn-primary" id="submit_button" name="submit" value="Submit">
                        </input>
                    @elseif ($vendors->status == 2)
                        <button type="button" class="btn btn-primary" id="open_data">Open Data</button>
                        <input type="submit" class="btn btn-primary" id="submit_button" name="submit"
                            value="Revisi Data">
                        </input>
                    @elseif ($vendors->status == 3)
                        <button type="button" class="btn btn-info cetak_button"
                            data-url="{{ url('') }}/printVendorEvaluation/{{ $vendors->code }}"
                            onclick="window.open('/printVendorEvaluation/{{ $vendors->code }}')">Print</button>
                        <button type="button" class="btn btn-danger" onclick="rejectData()">Reject</button>
                    @elseif ($vendors->status == 0)
                        <button type="button" class="btn btn-primary" id="open_data">Open Data</button>
                        <input type="submit" class="btn btn-primary" id="submit_button" name="submit"
                            value="Revisi Data">
                        </input>
                        <input type="submit" class="btn btn-primary" id="submit_button" name="submit"
                            value="Approval Ulang">
                        </input>
                    @endif

                    @if ($vendors->status == 0 || $vendors->status == 1 || $vendors->status == 2)
                        @if (Auth::user()->id == 1 ||
                                Auth::user()->id == 115 ||
                                Auth::user()->id == 117 ||
                                Auth::user()->id == 197 ||
                                Auth::user()->id == 116)
                            <button type="button" class="btn btn-danger" onclick="terminatedData()">Batalkan Vendor
                                Evaluation</button>
                        @endif
                    @endif
                </center>

                @php
                    $authorization = $vendors->current_authorization();
                @endphp

                <div class="d-flex justify-content-center mt-3 bottom_action">
                    @if ($vendors->status == 2 && $authorization->status != 1)
                        @if ($authorization)
                            @if (Auth::user()->id == $authorization->employee->id)
                                <button type="button" class="btn btn-danger" onclick="rejectData()">Reject</button>
                                <button type="submit" class="btn btn-primary ml-4" id="approve_button"
                                    form="approve_button">Approve</button>
                            @endif
                        @endif
                    @endif
                </div>
            </table>
        </form>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Vendor Evaluation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/vendor-evaluation/reject/{{ $vendors->id }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Masukan Alasan : (Wajib)</label>
                                    <input type="text-area" class="form-control" name="reason" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="terminatedModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Vendor Evaluation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/vendor-evaluation/terminated/{{ $vendors->id }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Masukan Alasan : (Wajib)</label>
                                    <input type="text-area" class="form-control" name="reason" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Batalkan Vendor Evaluation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('local-js')
    <script>
        $(document).ready(function() {
            let total = 0;
            $('.nilai').each(function() {
                let data = $(this).val();
                console.log(data);
                if (data) {
                    total = total + parseInt(data);
                }
            })
            $('#total').text(total);
        })

        $('.nilai').change(function() {
            let total = 0;
            $('.nilai').each(function() {
                let data = $(this).val();
                if (data) {
                    total = total + parseInt(data);
                }
            })
            $('#total').text(total);
        })

        $('#harga_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#harga_score_reason').prop('disabled', false);
            } else {
                $('#harga_score_reason').prop('disabled', true);
                $('#harga_score_reason').val('');
            }
            console.log(data);
        })

        $('#treatment_plan_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#treatment_plan_score_reason').prop('disabled', false);
            } else {
                $('#treatment_plan_score_reason').prop('disabled', true);
                $('#treatment_plan_score_reason').val('');
            }
            console.log(data);
        })

        $('#pelayanan_rentokill_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#pelayanan_rentokill_reason').prop('disabled', false);
            } else {
                $('#pelayanan_rentokill_reason').prop('disabled', true);
                $('#pelayanan_rentokill_reason').val('');
            }
            console.log(data);
        })

        $('#laporan_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#laporan_score_reason').prop('disabled', false);
            } else {
                $('#laporan_score_reason').prop('disabled', true);
                $('#laporan_score_reason').val('');
            }
            console.log(data);
        })

        $('#kelengkapan_adm_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#kelengkapan_adm_score_reason').prop('disabled', false);
            } else {
                $('#kelengkapan_adm_score_reason').prop('disabled', true);
                $('#kelengkapan_adm_score_reason').val('');
            }
            console.log(data);
        })

        $('#waktu_penilaian_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#waktu_penilaian_score_reason').prop('disabled', false);
            } else {
                $('#waktu_penilaian_score_reason').prop('disabled', true);
                $('#waktu_penilaian_score_reason').val('');
            }
            console.log(data);
        })

        $('#koordinasi_dan_komunikasi_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#koordinasi_dan_komunikasi_score_reason').prop('disabled', false);
            } else {
                $('#koordinasi_dan_komunikasi_score_reason').prop('disabled', true);
                $('#koordinasi_dan_komunikasi_score_reason').val('');
            }
            console.log(data);
        })

        $('#pelayanan_cit_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#pelayanan_cit_score_reason').prop('disabled', false);
            } else {
                $('#pelayanan_cit_score_reason').prop('disabled', true);
                $('#pelayanan_cit_score_reason').val('');
            }
            console.log(data);
        })

        $('#kuantitas_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#kuantitas_score_reason').prop('disabled', false);
            } else {
                $('#kuantitas_score_reason').prop('disabled', true);
                $('#kuantitas_score_reason').val('');
            }
            console.log(data);
        })

        $('#kualitas_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#kualitas_score_reason').prop('disabled', false);
            } else {
                $('#kualitas_score_reason').prop('disabled', true);
                $('#kualitas_score_reason').val('');
            }
            console.log(data);
        })

        $('#waktu_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#waktu_score_reason').prop('disabled', false);
            } else {
                $('#waktu_score_reason').prop('disabled', true);
                $('#waktu_score_reason').val('');
            }
            console.log(data);
        })

        $('#pelayanan_sicepat_score_id').change(function() {
            let data = $(this).val();
            if (data != 3) {
                $('#pelayanan_sicepat_score_reason').prop('disabled', false);
            } else {
                $('#pelayanan_sicepat_score_reason').prop('disabled', true);
                $('#pelayanan_sicepat_score_reason').val('');
            }
            console.log(data);
        })

        $('#open_data').click(function() {
            $('#authorization_awal').prop('required', false);
            // $(this).toggle();
            $('.nilai').each(function() {
                let data = $(this).prop("disabled", false);
            })
        })

        $('.authorization_select2').change(function() {
            let list = $(this).find('option:selected').data('list');
            $('#authorization_field').empty();
            if (list !== undefined) {
                $('#authorization_field').append('<i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>')
                list.forEach(function(item, index) {
                    $('#authorization_field').append('<div class="mr-3"><span class="font-weight-bold">' +
                        item.employee.name + ' -- ' + item.employee_position.name +
                        '</span><br><span>' + item.sign_as + '</span></div>');
                    if (index != list.length - 1) {
                        $('#authorization_field').append(
                            '<i class="fa fa-chevron-right mr-3" aria-hidden="true"></i>');
                    }
                });
            }
        });

        function rejectData() {
            $('#addModal').modal('show');
        }

        function terminatedData() {
            $('#terminatedModal').modal('show');
        }
    </script>
@endsection
