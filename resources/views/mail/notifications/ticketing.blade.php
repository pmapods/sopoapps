<style>
    tbody td {
        padding: 1em !important;
    }
</style>
@php
    if (isset($ticket)) {
        $code = $ticket->code;
        $requirement_date = $ticket->requirement_date;
        $created_at = $ticket->created_at;
        $termination_reason = $ticket->termination_reason;
        $po_reference_number = $ticket->po_reference_number;
        $over_plafon_reject_notes = $ticket->over_plafon_reject_notes;

        $agreement_filepath_reject_notes = $ticket->agreement_filepath_reject_notes;
        $sph_filepath_reject_notes = $ticket->sph_filepath_reject_notes;
        $tor_filepath_reject_notes = $ticket->tor_filepath_reject_notes;
        $user_agreement_filepath_reject_notes = $ticket->user_agreement_filepath_reject_notes;
    }
    if (isset($armadaticket)) {
        $code = $armadaticket->code;
        $requirement_date = $armadaticket->requirement_date;
        $created_at = $armadaticket->created_at;
        $termination_reason = $armadaticket->termination_reason;
        $po_reference_number = $armadaticket->po_reference_number;
    }
    $custom_settings = json_decode($ticket->custom_settings);

@endphp
@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>
@switch($type)
    @case('ticketing_approval')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Mohon approvalnya atas pengajuan tiketing {{ $type_name }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Informasi Pengadaan :<br>
            @if ($ticket)
                @if ($ticket->custom_settings != null && $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                    Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
                @endif
            @endif
            =================================<br>
            @if ($ticket)
                Daftar Item : <br>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Brand</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket->ticket_item as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->brand ?? '-' }}</td>
                                <td>{{ $item->type ?? '-' }}</td>
                                <td>{{ $item->count ?? '-' }}</td>
                                <td>{{ $item->price ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table><br>
            @endif
            @if ($armadaticket)
                Tipe Armada : {{ $armadaticket->armada_type->name }}<br>
                Rekomendasi Vendor area : {{ $armadaticket->vendor_recommendation_name }}<br>
                Jenis Niaga : {{ $armadaticket->isNiaga == true ? 'Niaga' : 'Non Niaga' }}<br>
            @endif
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('ticketing_approved')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Terlampir adalah form tiketing {{ $type_name }} yang sudah full approval<br>
            beserta dengan kelengkapan berkasnya<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>

            @if ($ticket)
                @if ($ticket->budget_type == 0 && $ticket->is_over_budget == 1)
                    Jenis Budget : Over Budget<br>
                @elseif ($ticket->budget_type == 0 && $ticket->is_over_budget == 0)
                    Jenis Budget : Budget<br>
                @else
                    Jenis Budget : Non Budget<br>
                @endif
            @endif

            @if ($ticket)
                @if ($ticket->custom_settings != null && $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                    Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
                @endif
            @endif
            =================================<br>
            @if ($ticket)
                Daftar Item : <br>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Brand</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket->ticket_item as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->brand ?? '-' }}</td>
                                <td>{{ $item->type ?? '-' }}</td>
                                <td>{{ $item->count ?? '-' }}</td>
                                <td>{{ $item->price ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table><br>
            @endif
            @if ($armadaticket)
                Tipe Armada : {{ $armadaticket->armada_type->name }}
                Rekomendasi Vendor area : {{ $armadaticket->vendor_recommendation_name }}
                Jenis Niaga : {{ $armadaticket->isNiaga == true ? 'Niaga' : 'Non Niaga' }}
            @endif
            =================================<br>
            Mohon bantuannya untuk proses selanjutnya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('ticketing_reject')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Terkait approval pengajuan tiketing {{ $type_name }} telah di reject :<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            @if ($ticket)
                @if ($ticket->custom_settings != null && $custom_settings->ticket_name == 'Pengadaan Fasilitas Karyawan COP')
                    Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
                @endif
            @endif
            =================================<br>
            @if ($ticket)
                Daftar Item : <br>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Brand</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket->ticket_item as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->brand ?? '-' }}</td>
                                <td>{{ $item->type ?? '-' }}</td>
                                <td>{{ $item->count ?? '-' }}</td>
                                <td>{{ $item->price ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table><br>
            @endif
            @if ($armadaticket)
                Tipe Armada : {{ $armadaticket->armada_type->name }}
                Rekomendasi Vendor area : {{ $armadaticket->vendor_recommendation_name }}
                Jenis Niaga : {{ $armadaticket->isNiaga == true ? 'Niaga' : 'Non Niaga' }}
            @endif
            =================================<br>
            di karenakan : {{ $termination_reason }}<br>
            Kami tunggu kabar baiknya<br>
            Regards
            {{ $from }}<br>
        </p>
    @break

    @case('ticketing_cancel')
        Dear Bapak/Ibu
        Terkait approval pengajuan tiketing {{ $type_name }} telah di cancel :
        di karenakan : Free text terkait cancel
        Regards
        Pengaju
    @break

    @case('facilityform_approval')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Mohon approvalnya atas form fasilitas untuk pengajuan tiketing {{ $type_name }} terlampir<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br>
            Jenis Armada : {{ $armadaticket->armada_type->name }}<br>
            Pilihan Vendor Area : {{ $armadaticket->vendor_recommendation_name }}<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}
        </p>
    @break

    @case('facilityform_reject')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Terkait approval form fasilitas untuk pengajuan tiketing {{ $type_name }} telah di reject :<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br>
            Jenis Armada : {{ $armadaticket->armada_type->name }}<br>
            Pilihan Vendor Area : {{ $armadaticket->vendor_recommendation_name }}<br>
            di karenakan : {{ $data['reason'] }}<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('perpanjanganform_approval')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Mohon approvalnya atas form perpanjangan untuk pengajuan tiketing {{ $type_name }} terlampir<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br><br>

            Data Form Perpanjangan <br>
            ================================<br>
            Nama : {{ $perpanjanganform->nama }}<br>
            NIK : {{ $perpanjanganform->nik }}<br>
            Jabatan : {{ $perpanjanganform->jabatan }}<br>
            Salespoint : {{ $perpanjanganform->nama_salespoint }}<br>
            Tipe Armada : {{ $perpanjanganform->tipe_armada }}<br>
            Jenis Kendaraan : {{ $perpanjanganform->jenis_kendaraan }}<br>
            Nopol : {{ $perpanjanganform->nopol }}<br>
            Unit : {{ $perpanjanganform->unit }}<br>
            Vendor : {{ $perpanjanganform->nama_vendor }}<br>
            ====================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}
        </p>
    @break

    @case('perpanjanganform_approved')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Berikut form perpanjangan yang sudah full approval {{ $type_name }} terlampir<br>
            Harap melakukan validasi form perpanjangan pada menu Form Validaton (Operational -> Form Validation)<br>
            Dilanjutkan dengan membuatkan PR SAP terkait {{ $type_name }} setelah proses validasi form,<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br><br>

            Data Form Perpanjangan <br>
            ================================<br>
            Nama : {{ $perpanjanganform->nama }}<br>
            NIK : {{ $perpanjanganform->nik }}<br>
            Jabatan : {{ $perpanjanganform->jabatan }}<br>
            Salespoint : {{ $perpanjanganform->nama_salespoint }}<br>
            Tipe Armada : {{ $perpanjanganform->tipe_armada }}<br>
            Jenis Kendaraan : {{ $perpanjanganform->jenis_kendaraan }}<br>
            Nopol : {{ $perpanjanganform->nopol }}<br>
            Unit : {{ $perpanjanganform->unit }}<br>
            Vendor : {{ $perpanjanganform->nama_vendor }}<br>
            ====================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}
        </p>
    @break

    @case('evaluasiform_approved')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Berikut form evaluasi yang sudah full approval {{ $type_name }} terlampir<br>
            Mohon bantuannya untuk dibuatkan PR SAP terkait {{ $type_name }},<br>
            Kode Tiket : {{ $securityticket->code }}<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}
        </p>
    @break

    @case('perpanjanganform_reject')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Terkait approval form perpanjangan untuk pengajuan tiketing {{ $type_name }} telah di reject :<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br><br>

            Data Form Perpanjangan <br>
            ================================<br>
            Nama : {{ $perpanjanganform->nama }}<br>
            NIK : {{ $perpanjanganform->nik }}<br>
            Jabatan : {{ $perpanjanganform->jabatan }}<br>
            Salespoint : {{ $perpanjanganform->nama_salespoint }}<br>
            Tipe Armada : {{ $perpanjanganform->tipe_armada }}<br>
            Jenis Kendaraan : {{ $perpanjanganform->jenis_kendaraan }}<br>
            Nopol : {{ $perpanjanganform->nopol }}<br>
            Unit : {{ $perpanjanganform->unit }}<br>
            Vendor : {{ $perpanjanganform->nama_vendor }}<br>
            ====================================<br>
            di karenakan : {{ $data['reason'] }}<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('mutasiform_approval')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Mohon approvalnya atas form mutasi untuk pengajuan tiketing {{ $type_name }} terlampir<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br><br>

            Data Form Mutasi <br>
            ================================<br>
            Kode Form Mutasi : {{ $mutasiform->code }}<br>
            Salespoint Pengirim : {{ $mutasiform->sender_salespoint_name }}<br>
            Salespoint Penerima : {{ $mutasiform->receiver_salespoint_name }}<br>
            Tanggal Mutasi : {{ $mutasiform->mutation_date }}<br>
            Tanggal Terima : {{ $mutasiform->received_date }}<br>
            Nopol : {{ $mutasiform->nopol }}<br>
            Vendor : {{ $mutasiform->vendor_name }}<br>
            Brand : {{ $mutasiform->brand_name }}<br>
            Jenis Kendaraan : {{ $mutasiform->jenis_kendaraan }}<br>
            Nomor Rangka : {{ $mutasiform->nomor_rangka }}<br>
            Nomor Mesin : {{ $mutasiform->nomor_mesin }}<br>
            Tahun Pembuatan : {{ $mutasiform->tahun_pembuatan }}<br>
            Tanggal STNK : {{ $mutasiform->stnk_date }}<br>
            ====================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}
        </p>
    @break

    @case('mutasiform_approved')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Full approvalnya atas form mutasi untuk pengajuan tiketing {{ $type_name }} terlampir<br>
            Mohon bantuannya untuk dibuatkan PR SAP terkait {{ $type_name }},<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br><br>

            Data Form Mutasi <br>
            ================================<br>
            Kode Form Mutasi : {{ $mutasiform->code }}<br>
            Salespoint Pengirim : {{ $mutasiform->sender_salespoint_name }}<br>
            Salespoint Penerima : {{ $mutasiform->receiver_salespoint_name }}<br>
            Tanggal Mutasi : {{ $mutasiform->mutation_date }}<br>
            Tanggal Terima : {{ $mutasiform->received_date }}<br>
            Nopol : {{ $mutasiform->nopol }}<br>
            Vendor : {{ $mutasiform->vendor_name }}<br>
            Brand : {{ $mutasiform->brand_name }}<br>
            Jenis Kendaraan : {{ $mutasiform->jenis_kendaraan }}<br>
            Nomor Rangka : {{ $mutasiform->nomor_rangka }}<br>
            Nomor Mesin : {{ $mutasiform->nomor_mesin }}<br>
            Tahun Pembuatan : {{ $mutasiform->tahun_pembuatan }}<br>
            Tanggal STNK : {{ $mutasiform->stnk_date }}<br>
            ====================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}
        </p>
    @break

    @case('mutasiform_reject')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Terkait approval form mutasi untuk pengajuan tiketing {{ $type_name }} telah di reject :<br>
            Kode Tiket : {{ $armadaticket->code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($armadaticket->requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $armadaticket->created_at->translatedFormat('d F Y') }}<br><br>

            Data Form Mutasi <br>
            ================================<br>
            Kode Form Mutasi : {{ $mutasiform->code }}<br>
            Salespoint Pengirim : {{ $mutasiform->sender_salespoint_name }}<br>
            Salespoint Penerima : {{ $mutasiform->receiver_salespoint_name }}<br>
            Tanggal Mutasi : {{ $mutasiform->mutation_date }}<br>
            Tanggal Terima : {{ $mutasiform->received_date }}<br>
            Nopol : {{ $mutasiform->nopol }}<br>
            Vendor : {{ $mutasiform->vendor_name }}<br>
            Brand : {{ $mutasiform->brand_name }}<br>
            Jenis Kendaraan : {{ $mutasiform->jenis_kendaraan }}<br>
            Nomor Rangka : {{ $mutasiform->nomor_rangka }}<br>
            Nomor Mesin : {{ $mutasiform->nomor_mesin }}<br>
            Tahun Pembuatan : {{ $mutasiform->tahun_pembuatan }}<br>
            Tanggal STNK : {{ $mutasiform->stnk_date }}<br>
            ====================================<br>
            di karenakan : {{ $data['reason'] }}<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('evaluasivendor_approval')
        Dear Bapak/Ibu
        Mohon approvalnya atas form evaluasi vendor untuk pengajuan tiketing {{ $type_name }} terlampir
        Kami tunggu kabar baiknya
        Regards
        Pengaju
    @break

    @case('evaluasivendor_reject')
        Dear Bapak/Ibu
        Terkait approval form perpanjangan untuk pengajuan tiketing {{ $type_name }} telah di reject :
        di karenakan : Free text terkait reject
        Regards
        Pengaju
    @break

    @case('legal_upload_file')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Tim legal sudah upload file atas pengajuan tiketing {{ $type_name }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Informasi Pengadaan :<br>
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('user_upload_evidance_overplafond')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            User sudah upload bukti transfer overplafond COP atas pengajuan tiketing {{ $code }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Informasi Pengadaan :<br>
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('user_reupload_evidance_overplafond')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            User sudah upload ulang bukti transfer overplafond COP atas pengajuan tiketing {{ $code }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Informasi Pengadaan :<br>
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reject_evidance_overplafond')
        <p>
            Dear Bapak/Ibu<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Terkait approval bukti transfer overplafond COP untuk pengajuan tiketing {{ $code }} telah di reject<br>
            di karenakan : {{ $over_plafon_reject_notes }} <br>
            Mohon untuk upload ulang bukti transfer overplafond COP. <br>

            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reject_agreement_legal')
        <p>
            Dear Bapak/Ibu Tim Legal<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Terkait approval File Perjanjian COP untuk pengajuan tiketing {{ $code }} telah di reject<br>
            di karenakan : {{ $agreement_filepath_reject_notes }} <br>
            Mohon untuk upload ulang File Perjanjian COP. <br>

            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reject_tor_legal')
        <p>
            Dear Bapak/Ibu Tim Legal<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Terkait approval File TOR COP untuk pengajuan tiketing {{ $code }} telah di reject<br>
            di karenakan : {{ $tor_filepath_reject_notes }} <br>
            Mohon untuk upload ulang File TOR COP. <br>

            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reject_sph_legal')
        <p>
            Dear Bapak/Ibu Tim Legal<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Terkait approval File SPH COP untuk pengajuan tiketing {{ $code }} telah di reject<br>
            di karenakan : {{ $sph_filepath_reject_notes }} <br>
            Mohon untuk upload ulang File SPH COP. <br>

            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reject_user_agreement_legal')
        <p>
            Dear Bapak/Ibu Tim Legal<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Terkait approval File Perjanjian User COP untuk pengajuan tiketing {{ $code }} telah di reject<br>
            di karenakan : {{ $user_agreement_filepath_reject_notes }} <br>
            Mohon untuk upload ulang File Perjanjian User COP. <br>

            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reupload_agreement_legal')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Tim Legal sudah upload ulang File Perjanjian COP atas pengajuan tiketing {{ $code }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            Informasi Pengadaan :<br>
            =================================<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reupload_tor_legal')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Tim Legal sudah upload ulang File TOR COP atas pengajuan tiketing {{ $code }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reupload_sph_legal')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Tim Legal sudah upload ulang File SPH COP atas pengajuan tiketing {{ $code }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('reupload_user_agreement_legal')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Tim Legal sudah upload ulang File Perjanjian User COP atas pengajuan tiketing {{ $code }} terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Setup : {{ \Carbon\Carbon::parse($requirement_date)->translatedFormat('d F Y') }}<br>
            Tanggal Pengajuan : {{ $created_at->translatedFormat('d F Y') }}<br>
            Nama Pengaju : {{ $ticket->created_by_employee->name }}<br>
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

@endswitch
<br>
@if (config('app.env') != 'production')
    DEVELOPMENT PODS<br>
    =========================================<br>
    original emails : {{ implode(', ', $original_emails) }}<br>
    original ccs : {{ implode(', ', $original_ccs) }}<br>
    =========================================<br>
@endif
