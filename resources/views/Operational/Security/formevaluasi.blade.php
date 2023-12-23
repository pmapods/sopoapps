@section('ba_ticketing_block')
    @php
        $securityticket = $evaluasiform->security_ticket;
        $security_ticketing_block = DB::table('ticketing_block')
            ->where('ticketing_type_name', 'Security')
            ->first();
        $ticketing_block_open_request = \App\Models\TicketingBlockOpenRequest::where('ticket_code', $securityticket->code)
            ->whereIn('status', [0, 1])
            ->first();
        if ($ticketing_block_open_request) {
            $ba_file_path = $ticketing_block_open_request->ba_file_path;
            $status_ba = $ticketing_block_open_request->status_name();
        }
        $old_rejected_ba = \App\Models\TicketingBlockOpenRequest::where('ticket_code', $securityticket->code)
            ->whereIn('status', [-1])
            ->first();
    @endphp
    @if (now()->day > $security_ticketing_block->max_block_day)
        <span class="text-danger">Tidak dapat melanjutkan proses evaluasi form security (telah melebihi batas waktu)</span>
    @elseif (now()->day > $security_ticketing_block->block_day)
        {{-- munculkan link upload BA hanya pada saat sudah melebihi max day --}}
        @if (isset($ba_file_path))
            <a href="#" onclick="window.open('/storage/{{ $ba_file_path }}')">Tampilkan BA
                Perpanjangan<br>({{ $status_ba }})</a>
        @else
            {{-- jika sudah melebihi max day. jangan munculkan button untuk upload BA --}}
            <a href="#"
                onclick="uploadPerpanjanganBA('{{ $securityticket->code }}', '{{ $securityticket->po_reference_number }}')">Upload
                BA Perpanjangan</a>
            @if (isset($old_rejected_ba))
                <br>
                <span class="text-danger">BA Reject Oleh
                    {{ $old_rejected_ba->rejected_by_employee->name }}</span><br>
                <span class="text-danger">Alasan : {{ $old_rejected_ba->reject_reason }}</span>
            @endif
        @endif
    @else
    @endif
@endsection
<div class="row">
    <div class="col-6">
        <h5>
            Formulir Evaluasi
            @if ($evaluasiform->status == 1)
                <span class="text-success">(Selesai)</span>
            @else
                <span class="text-warning">(Menunggu Approval)</span>
            @endif
        </h5>
    </div>
    <div class="col-6 text-right">
        @yield('ba_ticketing_block')
    </div>
</div>
@php
    $personils = collect(json_decode($evaluasiform->personil));
    $array_a1 = [];
    $array_a2 = [];
    $array_a3 = [];
    foreach ($personils as $personil) {
        $values = explode(',', $personil->values);
        $array_a1 = array_merge($array_a1, array_slice($values, 0, 5));
        $array_a2 = array_merge($array_a2, array_slice($values, 4, 10));
        $array_a3 = array_merge($array_a3, array_slice($values, 15, 2));
    }

    $rate_a1 = round(array_sum($array_a1) / count($array_a1), 2);
    $rate_a2 = round(array_sum($array_a2) / count($array_a2), 2);
    $rate_a3 = round(array_sum($array_a3) / count($array_a3), 2);

    $total_a1 = round(($rate_a1 / 4) * 30, 2);
    $total_a2 = round(($rate_a2 / 4) * 20, 2);
    $total_a3 = round(($rate_a3 / 4) * 10, 2);

@endphp
<div class="d-flex flex-column box p-3">
    <b>NAMA VENDOR : {{ $evaluasiform->vendor_name }}</b>
    <b>PERIODE PENILAIAN : {{ \Carbon\Carbon::parse($securityticket->period)->translatedFormat('F Y') }}</b>
    <b>CABANG/DEPO : {{ $evaluasiform->salespoint_name }}</b>
    <b class="mt-3">A. ASPEK PENILAIAN PERSONIL SECURITY</b>
    <div>
        <table class="table table-bordered text-sm">
            <thead>
                <tr>
                    <th class="bg-success text-light" rowspan="2" width="3%">NO</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">KATEGORI</th>
                    <th class="bg-success text-light" rowspan="2" width="">ITEM PENILAIAN</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">RATA-RATA</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">BOBOT</th>
                    <th class="bg-success text-light" rowspan="2" width="5%">TOTAL NILAI</th>
                    <th class="bg-white" rowspan="2">&nbsp;</th>
                    <th class="bg-success text-light" colspan="5" width="35%">DETAIL PENILAIAN PERSONIL</th>
                </tr>
                <tr class="bg-success">
                    @for ($i = 0; $i < 5; $i++)
                        <td style="padding:1px !important;">
                            @php
                                if ($personils->where('column_index', $i)->first() != null) {
                                    $name = $personils->where('column_index', $i)->first()->name;
                                } else {
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
                    <td>&nbsp;</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td>
                            @php
                                if ($personils->where('column_index', $i)->first() != null) {
                                    $value = explode(',', $personils->where('column_index', $i)->first()->values)[$row_count];
                                } else {
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>
                @php
                    $a1_array_text = ['b) Pelayanan terhadap Karyawan & Tamu (Salam & Kesopanan)', 'c) Ketepatan waktu hadir di lokasi (Kantor)', 'd) Tegas, Dapat diandalkan, Sigap', 'e) Kedisiplinan atas peraturan yang dijaga nya'];
                @endphp
                @foreach ($a1_array_text as $text)
                    <tr style="line-height: 30px">
                        <td>{{ $text }}</td>
                        <td>&nbsp;</td>
                        @for ($i = 0; $i < 5; $i++)
                            <td>
                                @php
                                    if ($personils->where('column_index', $i)->first() != null) {
                                        $value = explode(',', $personils->where('column_index', $i)->first()->values)[$row_count];
                                    } else {
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
                    <td>&nbsp;</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td>
                            @php
                                if ($personils->where('column_index', $i)->first() != null) {
                                    $value = explode(',', $personils->where('column_index', $i)->first()->values)[$row_count];
                                } else {
                                    $value = '-';
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    @endfor
                    @php $row_count++; @endphp
                </tr>

                @php
                    $a2_array_text = ['b) Menjaga gudang steril dari orang yang tidak berkepentingan', 'c) Pencatatan Kilometer Kendaraan dengan benar', 'd) Pencatatan stock pada saat proses Loading & Unloading dengan benar', 'e) Penataan / pengaturan letak kendaraan bermotor (Mobil/Motor)', 'f) Mencatat Setiap Tamu yang Hadir dan melaporkan kepada orang dituju', 'g) Kunjungan (Patroli) Penanggung Jawab atau Koordinator Lapangan', 'h) Mampu bertugas dengan menerapkan standar-standar pengamanan yang baik', 'i) Komunikasi dengan Pihak Luar(RT/RW, Kelurahan, Aparat TNI & POLRI di sekitar)', 'j) Penjangaan terhadap Gerbang Utama / Kantor \'Tidak Pernah Kosong\' '];
                @endphp
                @foreach ($a2_array_text as $text)
                    <tr style="line-height: 30px">
                        <td>{{ $text }}</td>
                        <td>&nbsp;</td>
                        @for ($i = 0; $i < 5; $i++)
                            <td>
                                @php
                                    if ($personils->where('column_index', $i)->first() != null) {
                                        $value = explode(',', $personils->where('column_index', $i)->first()->values)[$row_count];
                                    } else {
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
                    <td>&nbsp;</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td>
                            @php
                                if ($personils->where('column_index', $i)->first() != null) {
                                    $value = explode(',', $personils->where('column_index', $i)->first()->values)[$row_count];
                                } else {
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
                    <td>&nbsp;</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td>
                            @php
                                if ($personils->where('column_index', $i)->first() != null) {
                                    $value = explode(',', $personils->where('column_index', $i)->first()->values)[$row_count];
                                } else {
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
                    <td class="bg-success text-light text-right" colspan="3">
                        {{ $total_a1 + $total_a2 + $total_a3 }}%</td>
                    <td>&nbsp;</td>
                    <td class="bg-success" colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <b class="mt-3">B. ASPEK PENILAIAN KELEMBAGAAN</b>
    <div class="row">
        <div class="col-12">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th class="bg-success text-light" width="5%">NO</th>
                        <th class="bg-success text-light">KATEGORI</th>
                        <th class="bg-success text-light">ITEM PENILAIAN</th>
                        <th class="bg-success text-light" width="8%">NILAI</th>
                        <th class="bg-success text-light" width="8%">BOBOT</th>
                        <th class="bg-success text-light" width="8%">TOTAL NILAI</th>
                        <th class="bg-white border-white">&nbsp;</th>
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
                    @foreach ($lists as $key => $list)
                        <tr>
                            <td>{{ $list['no'] }}</td>
                            <td>{{ $list['category'] }}</td>
                            <td>{{ $list['item'] }}</td>
                            <td>
                                {{ $lembaga[$key]->nilai }}%
                            </td>
                            <td data-value="{{ $list['bobot'] }}">{{ $list['bobot'] }}%</td>
                            <td>
                                {{ intval(($lembaga[$key]->nilai / 100) * $list['bobot']) }}%
                                @php
                                    $total_lembaga += intval(($lembaga[$key]->nilai / 100) * $list['bobot']);
                                @endphp
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                {!! nl2br(e($lembaga[$key]->keterangan)) !!}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="text-light bg-success" colspan="3">SUB TOTAL</td>
                        <td class="text-light bg-success" colspan="3">{{ $total_lembaga }}%</td>
                        <td>&nbsp;</td>
                        <td class="text-light bg-success">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-8 mx-2 border border-dark d-flex justify-content-center align-items-center" style="height: 4em">
            <h5>TOTAL NILAI</h5>
            <h5>(Catatan : Nilai Minimum 70%)</h5>
        </div>
        <div class="col-1 border border-dark d-flex justify-content-center align-items-center" style="height: 4em">
            <h5>{{ $total_a1 + $total_a2 + $total_a3 + $total_lembaga }}%</h5>
        </div>
        <div class="col-2 offset-1 ml-2 border border-dark d-flex justify-content-center align-items-center"
            style="height: 4em">
            <h5>
                @if ($total_a1 + $total_a2 + $total_a3 + $total_lembaga >= 70)
                    DIREKOMENDASIKAN
                @else
                    TIDAK DIREKOMENDASIKAN
                @endif
            </h5>
        </div>
        <div class="col-12 border p-3 mt-2 border-dark mx-2 d-flex justify-content-start align-items-center">
            <span>KESIMPULAN <br> (Pilih salah satu)</span>
            @php

            @endphp
            <div class="row ml-5">
                <div class="col-12">
                    <i class="far
                        @if ($evaluasiform->kesimpulan == 0) fa-check-square
                        @else
                            fa-square @endif
                        mr-2"
                        aria-hidden="true"></i><span>VENDOR DAN PERSONIL TETAP</span>
                </div>
                <div class="col-12">
                    <i class="far
                        @if ($evaluasiform->kesimpulan == 1) fa-check-square
                        @else
                            fa-square @endif
                            mr-2"
                        aria-hidden="true"></i><span>GANTI VENDOR</span>
                </div>
                <div class="col-12">
                    <i class="far
                        @if ($evaluasiform->kesimpulan == 2) fa-check-square
                        @else
                            fa-square @endif
                        mr-2"
                        aria-hidden="true"></i><span>GANTI PERSONIL SECURITY DENGAN VENDOR SAMA</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6 mt-2">
            <table class="table table-bordered">
                <tbody>
                    @php
                        $count = $evaluasiform->authorizations->count();
                        $headers_name = [];
                        $headers_colspan = [];
                        foreach ($evaluasiform->authorizations as $authorization) {
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
                        @foreach ($evaluasiform->authorizations as $authorization)
                            <td width="{{ 100 / $count }}%" class="align-bottom small" style="height: 80px">
                                @if (($evaluasiform->current_authorization()->id ?? -1) == $authorization->id)
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
        </div>

        @if ($evaluasiform->notes_form_evaluasi == null &&
            Auth::user()->id == 115 &&
            $evaluasiform->security_ticket->ticketing_type == 3)
            <form action="/noteevaluasiform/{{ $evaluasiform->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="required_field">Notes (Isi Notes Sebelum Approve / Reject)</label>
                        <input type="text-area" class="form-control" name="notes_form_evaluasi" required>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3" name="submit_notes_form_evaluasi"
                        id="submit_notes_form_evaluasi">Submit</button>
                </div>
            </form>
        @endif

        @if ($evaluasiform->notes_form_evaluasi)
            <div class="col-md-12">
                <div class="form-group">
                    <label>Notes</label>
                    <input type="text" class="form-control" value="{{ $evaluasiform->notes_form_evaluasi }}"
                        readonly>
                </div>
            </div>
        @endif

        <div class="col-12 text-center">
            @if ($evaluasiform->notes_form_evaluasi == null &&
                Auth::user()->id == 115 &&
                $evaluasiform->security_ticket->ticketing_type == 3 &&
                ($evaluasiform->current_authorization()->employee_id ?? '-1') == Auth::user()->id)
                <button type="button" class="btn btn-success" disabled>Approve</button>
                <button type="button" class="btn btn-danger" disabled>Reject</button>
            @else
                @if (($evaluasiform->current_authorization()->employee_id ?? '-1') == Auth::user()->id)
                    <button type="button" class="btn btn-success"
                        onclick="evaluasiapprove({{ $evaluasiform->id }})">Approve</button>
                    <button type="button" class="btn btn-danger"
                        onclick="evaluasireject({{ $evaluasiform->id }})">Reject</button>
                @endif
            @endif
        </div>

        @if ($evaluasiform->status == 1)
            <div class="col-12 text-center">
                <a class="btn btn-primary" href="/printevaluasiform/{{ \Crypt::encryptString($evaluasiform->id) }}"
                    role="button">Cetak</a>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="uploadBAPerpanjanganModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload BA Perpanjangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/securityticketing/security_ticket_code/uploadBAPerpanjangan" method="post"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Nomor Ticket</label>
                                <input type="text" class="form-control" name="ticket_code" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Nomor PO</label>
                                <input type="text" class="form-control" name="po_number" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="required_field">File BA</label>
                                <input type="file" class="form-control-file" name="ba_file" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Submit BA</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('evaluasiform-js')
    <script>
        function uploadPerpanjanganBA(security_ticket_code, po_number) {
            $('#uploadBAPerpanjanganModal input[name="ticket_code"]').val(security_ticket_code);
            $('#uploadBAPerpanjanganModal input[name="po_number"]').val(po_number);
            $('#uploadBAPerpanjanganModal form').prop('action', "/securityticketing/" + security_ticket_code +
                "/uploadBAPerpanjangan");
            $('#uploadBAPerpanjanganModal').modal('show');
        }
    </script>
@endsection
