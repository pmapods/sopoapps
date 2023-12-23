<style>
    tbody td {
        padding: 1em !important;
    }
</style>
@php
    if (isset($vendor_evaluation)) {
        $code = $vendor_evaluation->code;
        $vendor = $vendor_evaluation->vendor;
        $salespoint = $vendor_evaluation->salespoint->name;
        $created_by = $vendor_evaluation->created_at;
        $start_periode_penilaian = $vendor_evaluation->start_periode_penilaian;
        $end_periode_penilaian = $vendor_evaluation->end_periode_penilaian;
        $created_at = $vendor_evaluation->created_at;
        $reason = $vendor_evaluation->vendor_evaluation_detail->reason;
    }
@endphp

@php
    $logourl = public_path() . '/assets/logo_small.png';
@endphp

<img src="{{ $message->embed($logourl) }}"><br>
@switch($type)
    @case('vendor_evaluation_approval')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Mohon approvalnya atas pengajuan vendor evaluation terlampir<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Pengajuan : {{ $created_by->translatedFormat('d F Y') }}<br>
            Informasi Vendor evaluation :<br>
            =================================<br>
            @if ($vendor_evaluation)
                Daftar Item : <br>
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Pembuat Form</th>
                            <th>Area</th>
                            <th>Vendor</th>
                            <th>Start Periode Penilaian</th>
                            <th>End Periode Penilaian</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendor_evaluation as $vendors)
                            <tr>
                                <td>{{ $vendors->code }}</td>
                                <td>{{ $vendors->created_at->translatedFormat('d F Y') }}</td>
                                <td>{{ $vendors->created_by_employee->name ?? '-' }}</td>
                                <td>{{ $vendors->salespoint->name }}</td>
                                @if ($vendors->vendor == 0)
                                    <td>Pest Control</td>
                                @elseif ($vendors->vendor == 1)
                                    <td>CIT</td>
                                @elseif ($vendors->vendor == 2)
                                    <td>Si Cepat</td>
                                @else
                                    <td>Ekspedisi</td>
                                @endif
                                <td>{{ \Carbon\Carbon::parse($vendors->start_periode_penilaian)->translatedFormat('d F Y') }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($vendors->end_periode_penilaian)->translatedFormat('d F Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table><br>
            @endif
            =================================<br>
            Kami tunggu kabar baiknya<br>
            Regards<br>
            {{ $from }}<br>
        </p>
    @break

    @case('vendor_evaluation_reject')
        <p>
            Dear Bapak/Ibu {{ $to }}<br>
            Terkait approval vendor evaluation telah di reject :<br>
            Kode Tiket : {{ $code }}<br>
            Tanggal Pengajuan : {{ $created_by->translatedFormat('d F Y') }}<br>
            =================================<br>
            @if ($vendor_evaluation)
                Daftar Item : <br>
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Pembuat Form</th>
                            <th>Area</th>
                            <th>Vendor</th>
                            <th>Start Periode Penilaian</th>
                            <th>End Periode Penilaian</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendor_evaluation as $vendors)
                            <tr>
                                <td>{{ $vendors->code }}</td>
                                <td>{{ $vendors->created_at->translatedFormat('d F Y') }}</td>
                                <td>{{ $vendors->created_by_employee->name ?? '-' }}</td>
                                <td>{{ $vendors->salespoint->name }}</td>
                                @if ($vendors->vendor == 0)
                                    <td>Pest Control</td>
                                @elseif ($vendors->vendor == 1)
                                    <td>CIT</td>
                                @elseif ($vendors->vendor == 2)
                                    <td>Si Cepat</td>
                                @else
                                    <td>Ekspedisi</td>
                                @endif
                                <td>{{ \Carbon\Carbon::parse($vendors->start_periode_penilaian)->translatedFormat('d F Y') }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($vendors->end_periode_penilaian)->translatedFormat('d F Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table><br>
            @endif
            =================================<br>
            di karenakan : {{ $reason }}<br>
            Kami tunggu kabar baiknya<br>
            Regards
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
