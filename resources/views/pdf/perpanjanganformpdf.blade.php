<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <link rel="stylesheet" href="/fontawesome/css/all.min.css">
    <style>
        @media print {
            #form_table thead {
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="border border-dark px-4">
        <center>
            <h5>FORM PERPANJANGAN/PERHENTIAN SEWA ARMADA</h5>
        </center>
        <div>Kami Yang bertanda tangan dibawah ini</div>
        <table class="table table-borderless small mt-3">
            <tbody>
                <tr>
                    <th width="30%">Nama</th>
                    <td>: {{ $perpanjangan_form->nama }}</td>
                </tr>
                <tr>
                    <th width="30%">NIK</th>
                    <td>: {{ $perpanjangan_form->nik }}</td>
                </tr>
                <tr>
                    <th width="30%">Jabatan</th>
                    <td>: {{ $perpanjangan_form->jabatan }}</td>
                </tr>
                <tr>
                    <th width="30%">Cabang/Depo/CP</th>
                    <td>: {{ $perpanjangan_form->nama_salespoint }}</td>
                </tr>
            </tbody>
        </table>
        <div>Dengan ini mengajukan perpanjangan / penghentian sewa armada sebagai berikut :</div>
        <table class="table table-borderless small mt-3">
            <tbody>
                <tr>
                    <td width="10%">1.</td>
                    <td width="30%">Armada</td>
                    <td>: 
                        @switch($perpanjangan_form->tipe_armada)
                            @case('niaga')
                                Niaga
                            @break

                            @case('nonniaga')
                                Non-Niaga
                            @break
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <td width="10%">2.</td>
                    <td width="30%">Jenis Kendaraan</td>
                    <td>: {{ $perpanjangan_form->jenis_kendaraan }}</td>
                </tr>
                <tr>
                    <td width="10%">3.</td>
                    <td width="30%">Nopol</td>
                    <td>: {{ $perpanjangan_form->nopol }}</td>
                </tr>
                <tr>
                    <td width="10%">4.</td>
                    <td width="30%">Unit</td>
                    <td>: {{ $perpanjangan_form->unit }}</td>
                </tr>
                <tr>
                    <td width="10%">5.</td>
                    <td width="30%">Vendor</td>
                    <td>: {{ $perpanjangan_form->nama_vendor }}</td>
                </tr>
                <tr>
                    <td width="10%">6.</td>
                    <td width="30%">Status</td>
                    <td></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="30%">Perpanjangan</td>
                    <td>{{ ($perpanjangan_form->perpanjangan_length != null) ? $perpanjangan_form->perpanjangan_length : '-'}} Bulan</td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="30%">Stop Sewa</td>
                    <td>{{ ($perpanjangan_form->stopsewa_date != null) ? $perpanjangan_form->stopsewa_date : '-'}}</td>
                </tr>
            </tbody>
        </table>
        <div class="text-center mt-3">Alasan : 
            @switch($perpanjangan_form->stopsewa_reason)
                @case('replace')
                    Replace
                    @break
                @case('renewal')
                    Renewal
                    @break
                @case('end')
                    End Kontrak
                    @break
                @default
                    -
                    @break
            @endswitch
        </div>
        <div class="mt-2">Pernyataan ini dibuat dengan sebenar-benarnya, jika ada perubahan kerugian akan dibebankan kepada
            masing-masing personal.</div>
        <table class="table table-bordered small mt-2">
            <tbody>
                @php
                    $count = $perpanjangan_form->authorizations->count();
                    $headers_name = [];
                    $headers_colspan = [];
                    foreach ($perpanjangan_form->authorizations as $authorization) {
                        array_push($headers_name, $authorization->as);
                        array_push($headers_colspan, 1);
                        $last = $headers_name[count($headers_name) - 1];
                        $before_last = $headers_name[count($headers_name) - 2] ?? null;
                        // skip check first array
                        if ($before_last == null) {
                            continue;
                        }
                        if ($last == $before_last) {
                            array_pop($headers_name);
                            array_pop($headers_colspan);
                            $headers_colspan[count($headers_colspan) - 1] += 1;
                        }
                    }
                @endphp
                <tr>
                    @foreach ($headers_name as $key => $name)
                        <td class="align-middle small table-secondary" colspan="{{ $headers_colspan[$key] }}">
                            {{ $name }}</td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($perpanjangan_form->authorizations as $authorization)
                        <td width="{{ 100 / $count }}%" class="align-bottom small" style="height: 80px">
                            @if (($perpanjangan_form->current_authorization()->id ?? -1) == $authorization->id)
                                <span class="text-warning">Pending approval</span><br>
                            @endif
                            @if ($authorization->status == 1)
                                <span class="text-success">Approved
                                    {{ $authorization->updated_at->format('Y-m-d (H:i)') }}</span><br>
                            @endif
                            {{ $authorization->employee_name }}<br>{{ $authorization->employee_position }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        <br>FRM-PCD-010 REV 02
    </div>
</body>

</html>
