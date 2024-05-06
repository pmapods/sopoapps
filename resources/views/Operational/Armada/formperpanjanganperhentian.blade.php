@section('ba_ticketing_block')
    @php
        $armada_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Armada')->first();
        $ticketing_block_open_request = \App\Models\TicketingBlockOpenRequest::where('ticket_code', $armadaticket->code)
            ->whereIn('status', [0, 1])
            ->first();
        if ($ticketing_block_open_request) {
            $ba_file_path = $ticketing_block_open_request->ba_file_path;
            $status_ba = $ticketing_block_open_request->status_name();
        }
        $old_rejected_ba = \App\Models\TicketingBlockOpenRequest::where('ticket_code', $armadaticket->code)
            ->whereIn('status', [-1])
            ->first();
    @endphp
    @if (isset($ba_file_path))
        <a href="#" onclick="window.open('/storage/{{ $ba_file_path }}')">Tampilkan BA
            Perpanjangan<br>({{ $status_ba }})</a>
    @endif
    @if (now()->day > $armada_ticketing_block->max_block_day)
        @if (!isset($armadaticket->perpanjangan_form) || $armadaticket->perpanjangan_form->status != 1)
            @if ($armadaticket->ticketing_type != 4)
                <span class="text-danger">Tidak dapat melanjutkan proses perpanjangan armada (telah melebihi batas
                    waktu)</span>
            @endif
        @endif
    @elseif (now()->day > $armada_ticketing_block->block_day)
        {{-- munculkan link upload BA hanya pada saat sudah melebihi max day --}}
        @if (!isset($ba_file_path))
            {{-- jika sudah form perpanjangan sudah full approve tidak perlu munculkan upload perpanjangan BA --}}
            @if (!isset($armadaticket->perpanjangan_form) || $armadaticket->perpanjangan_form->status != 1)
                <a href="#"
                    onclick="uploadPerpanjanganBA('{{ $armadaticket->code }}', '{{ $armadaticket->po_reference_number }}')">Upload
                    BA Perpanjangan</a>
            @endif
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

@if ($armadaticket->status != -1)
    @isset($armadaticket->perpanjangan_form)
        @php
            $perpanjanganform = $armadaticket->perpanjangan_form;
        @endphp
        <div class="row">
            <div class="col-md-6">
                <h5>Form Perpanjangan Perhentian</h5>
            </div>
            <div class="col-md-6 text-right">
                @yield('ba_ticketing_block')
            </div>
        </div>
        <div class="row border border-dark bg-light p-4">
            <div class="col-12">
                <center class="h4 text-uppercase"><u>form perpanjangan/penghentian sewa armada</u></center>
            </div>
            <div class="col-12 d-flex flex-column mt-5">
                <span>Kami yang bertanda tangan di bawah ini :</span>
                <div class="form-group row mt-2">
                    <label class="col-3">Nama</label>
                    <div class="col-1">:</div>
                    <div class="col-8">
                        {{ $perpanjanganform->nama }}
                    </div>
                </div>
                <div class="form-group row mt-2">
                    <label class="col-3">NIK</label>
                    <div class="col-1">:</div>
                    <div class="col-8">
                        {{ $perpanjanganform->nik }}
                    </div>
                </div>
                <div class="form-group row mt-2">
                    <label class="col-3">Jabatan</label>
                    <div class="col-1">:</div>
                    <div class="col-8">
                        {{ $perpanjanganform->jabatan }}
                    </div>
                </div>
                <div class="form-group row mt-2">
                    <label class="col-3">Cabang/Depo/CP</label>
                    <div class="col-1">:</div>
                    <div class="col-8">
                        {{ $perpanjanganform->nama_salespoint }}
                    </div>
                </div>
                <span>Dengan ini mengajukan perpanjangan / penghentian sewa armada sebagai berikut :</span>
                <div class="form-group row mt-2">
                    <div class="col-1">1.</div>
                    <div class="col-2">Armada</div>
                    <div class="col-1">:</div>
                    <div class="col-7">
                        @switch($perpanjanganform->tipe_armada)
                            @case('niaga')
                                Niaga
                            @break

                            @case('nonniaga')
                                Non-Niaga
                            @break
                        @endswitch
                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="col-1">2.</div>
                    <div class="col-2">Jenis Kendaraan</div>
                    <div class="col-1">:</div>
                    <div class="col-7">
                        {{ $perpanjanganform->jenis_kendaraan }}
                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="col-1">3.</div>
                    <div class="col-2">Nopol</div>
                    <div class="col-1">:</div>
                    <div class="col-7">
                        {{ $perpanjanganform->nopol }}
                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="col-1">4.</div>
                    <div class="col-2">Unit</div>
                    <div class="col-1">:</div>
                    <div class="col-7">
                        {{ $perpanjanganform->unit }}
                    </div>
                </div>

                <div class="form-group row mt-2">
                    <div class="col-1">5.</div>
                    <div class="col-2">Vendor</div>
                    <div class="col-1">:</div>
                    <div class="col-7">
                        {{ $perpanjanganform->nama_vendor }}
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-1">6.</div>
                    <div class="col-10">Status</div>
                </div>

                <div class="form-group row mt-2">
                    <div class="offset-1 col-2">
                        Perpanjangan
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-2">
                        {{ $perpanjanganform->perpanjangan_length != null ? $perpanjanganform->perpanjangan_length : '-' }}
                        Bulan
                    </div>
                </div>
                <div class="form-group row mt-2">
                    <div class="offset-1 col-2">
                        Stop Sewa
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-5">
                        {{ $perpanjanganform->stopsewa_date != null ? $perpanjanganform->stopsewa_date : '-' }}
                    </div>
                </div>
            </div>
            <div class="col-12 text-center">
                <span class="mr-2">Alasan : </span>
                {{-- {{ $armadaticket->type() }} --}}
                @switch($perpanjanganform->stopsewa_reason)
                    @case('replace')
                        Replace @if ($perpanjanganform->is_percepatan == true)
                            (Percepatan)
                        @endif
                    @break

                    @case('renewal')
                        Renewal @if ($perpanjanganform->is_percepatan == true)
                            (Percepatan)
                        @endif
                    @break

                    @case('end')
                        End Kontrak @if ($perpanjanganform->is_percepatan == true)
                            (Percepatan)
                        @endif
                    @break

                    @default
                        -
                    @break
                @endswitch
            </div>
            <div class="col-12 pt-3">
                Pernyataan ini dibuat dengan sebenar-benarnya, jika ada perubahan kerugian akan dibebankan kepada
                masing-masing personal.
            </div>
            <div class="col-12 pt-2">
                <table class="table table-bordered authorization_table">
                    <tbody>
                        @php
                            $count = $perpanjanganform->authorizations->count();
                            $headers_name = [];
                            $headers_colspan = [];
                            foreach ($perpanjanganform->authorizations as $authorization) {
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
                            @foreach ($perpanjanganform->authorizations as $authorization)
                                <td width="{{ 100 / $count }}%" class="align-bottom small" style="height: 80px">
                                    @if (($perpanjanganform->current_authorization()->id ?? -1) == $authorization->id)
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
            <div class="col-12 text-center">
                @if (($perpanjanganform->current_authorization()->employee_id ?? '-1') == Auth::user()->id)
                    <button type="button" class="btn btn-success"
                        onclick="perpanjanganapprove({{ $perpanjanganform->id }})">Approve</button>
                    <button type="button" class="btn btn-danger"
                        onclick="perpanjanganreject({{ $perpanjanganform->id }})">Reject</button>
                @endif
            </div>
            @if ($perpanjanganform->status == 1)
                <div class="d-flex justify-content-center">
                    <a class="btn btn-primary btn-sm" href="/printperpanjanganform/{{ $armadaticket->code }}"
                        role="button">Cetak</a>
                </div>
            @endif
        </div>
        <div class="col-12 d-flex flex-column mt-3">
            <div class="form-group row mt-2">
                {{-- <div class="col-1">7.</div> --}}
                <div class="col-2">BA Renewal</div>
                <div class="col-1">:</div>
                <div class="col-7">
                    <a class="text-primary font-weight-bold ml-1" style="cursor: pointer;"
                        onclick='window.open("/storage/{{ $perpanjanganform->ba_renewal_path }}")'>Tampilkan
                        BA Renewal</a>
                </div>
            </div>
        </div>
    @else
        @php
            $po_before_end_date = false;
            // check apakah po masih belum h-30 (case percepatan end kontrak(replace/renew/stopsewa))
            $po = \App\Models\Po::where('no_po_sap', $armadaticket->po_reference_number)->first();
            $po_manual = \App\Models\PoManual::where('po_number', $armadaticket->po_reference_number)->first();
            $po_end_date = '';
            if (isset($po)) {
                $po_end_date = $po->end_date;
            }
            if (isset($po_manual)) {
                $po_end_date = $po_manual->end_date;
            }
            if (\Carbon\CarbonImmutable::parse($po_end_date)->subDays(30) > now()) {
                $po_before_end_date = true;
            }
        @endphp
        <form id="formperpanjangan" method="post" action="/addperpanjanganform" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
            <input type="hidden" name="armada_id" value="{{ $armadaticket->armada_id }}">
            <input type="hidden" name="po_before_end_date" value="{{ $po_before_end_date }}">
            <div class="row">
                <div class="col-md-6">
                    <h5>Form Perpanjangan Perhentian</h5>
                    @isset($armadaticket->last_rejected_perpanjangan_form)
                        Di Reject Oleh : <span
                            class="text-danger">{{ $armadaticket->last_rejected_perpanjangan_form->terminated_by_employee->name ?? '-' }}</span><br>
                        Alasan Reject : <span
                            class="text-danger">{{ $armadaticket->last_rejected_perpanjangan_form->termination_reason ?? '-' }}</span>
                    @endisset
                </div>
                <div class="col-md-6 text-right">
                    @yield('ba_ticketing_block')
                </div>
            </div>

            <div class="row border border-dark bg-light p-4">
                <div class="col-12">
                    <center class="h4 text-uppercase"><u>form perpanjangan/penghentian sewa armada</u></center>
                </div>
                <div class="col-12 d-flex flex-column mt-5">
                    <span>Kami yang bertanda tangan di bawah ini :</span>
                    <div class="form-group row mt-2">
                        <label class="col-3 col-form-label required_field">Nama</label>
                        <div class="col-1">:</div>
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm" placeholder="Masukkan Nama"
                                name="name" required>
                        </div>
                    </div>
                    <div class="form-group row mt-2">
                        <label class="col-3 col-form-label required_field">NIK</label>
                        <div class="col-1">:</div>
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm" placeholder="Masukkan NIK"
                                name="nik" required>
                        </div>
                    </div>
                    <div class="form-group row mt-2">
                        <label class="col-3 col-form-label required_field">Jabatan</label>
                        <div class="col-1">:</div>
                        <div class="col-8">
                            <select class="form-control form-control-sm select2" name="jabatan" required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach ($employee_positions as $position)
                                    <option value="{{ $position->name }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mt-2">
                        <label class="col-3 col-form-label required_field">Cabang/Depo/CP</label>
                        <div class="col-1">:</div>
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $armadaticket->salespoint->name }}" name="salespoint_name" readonly>
                            <input type="hidden" name="salespoint_id" value="{{ $armadaticket->salespoint->id }}">
                        </div>
                    </div>
                    <span>Dengan ini mengajukan perpanjangan / penghentian sewa armada sebagai berikut :</span>
                    <div class="form-group row mt-2">
                        <div class="col-1">1.</div>
                        <div class="col-2 small required_field">Armada</div>
                        <div class="col-1 small">:</div>
                        <div class="col-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $armadaticket->isNiaga ? 'niaga' : 'nonniaga' }}" name="armada_type" readonly>
                        </div>
                    </div>

                    <div class="form-group row mt-2">
                        <div class="col-1">2.</div>
                        <div class="col-2 small required_field">Jenis Kendaraan</div>
                        <div class="col-1 small">:</div>
                        <div class="col-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $armadaticket->armada_type->name }}" name="jenis_kendaraan" readonly>
                        </div>
                    </div>

                    <div class="form-group row mt-2">
                        <div class="col-1">3.</div>
                        <div class="col-2 small required_field">Nopol</div>
                        <div class="col-1 small">:</div>
                        <div class="col-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $armadaticket->armada->plate ?? $pomanual->plate() }}" name="nopol" readonly>
                        </div>
                    </div>

                    <div class="form-group row mt-2">
                        <div class="col-1">4.</div>
                        <div class="col-2 small required_field">Unit</div>
                        <div class="col-1 small">:</div>
                        <div class="col-7">
                            <select class="form-control form-control-sm" name="unit" required>
                                <option value="">-- Pilih Unit --</option>
                                <option value="GS">GS</option>
                                <option value="GT">GT</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mt-2">
                        <div class="col-1">5.</div>
                        <div class="col-2 small required_field">Vendor</div>
                        <div class="col-1 small">:</div>
                        <div class="col-7">
                            <input class="form-control form-control-sm" type="text" name="vendor_name"
                                value="{{ $po->sender_name ?? $pomanual->vendor_name }}" readonly>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-1 small">6.</div>
                        <div class="col-10 small">Status</div>
                    </div>

                    <div class="form-group row mt-2">
                        <div class="offset-1 col-2 small">
                            <input type="radio" name="form_type" id="perpanjangan_radio" value="perpanjangan" required
                                @if ($po_before_end_date == true) disabled @endif>
                            <label for="perpanjangan_radio">Perpanjangan</label>
                        </div>
                        <div class="col-1 small">:</div>
                        <div class="col-4">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" min="1" name="perpanjangan_length"
                                    id="perpanjangan_length" disabled>
                                <div class="input-group-append">
                                    <span class="input-group-text">bulan</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-3 small">(diisi berapa bulan akan diperpanjang)</div>
                    </div>
                    <div class="form-group row mt-2">
                        <div class="offset-1 col-2 small">
                            <input type="radio" name="form_type" id="stopsewa_radio" value="stopsewa"
                                @if ($po_before_end_date == true) checked="true" @endif>
                            <label for="stopsewa_radio">Stop Sewa</label>
                        </div>
                        <div class="col-1">:</div>
                        <div class="col-5">
                            <input type="date" class="form-control form-control-sm" name="stopsewa_date"
                                id="stopsewa_date" disabled>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-center">
                    <span class="required_field mr-2">Alasan</span>

                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="alasan" value="replace"
                                id="replace_radio" disabled>Replace
                        </label>
                    </div>

                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="alasan" value="renewal"
                                id="renewal_radio" disabled>Renewal
                        </label>
                    </div>

                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="alasan" value="end" id="end_radio"
                                disabled>End Kontrak
                        </label>
                    </div>
                </div>
                <div class="col-12 pt-3">
                    Pernyataan ini dibuat dengan sebenar-benarnya, jika ada perubahan kerugian akan dibebankan kepada
                    masing-masing personal.
                </div>
                <div class="col-12 pt-2">
                    {{-- old matriks approval select --}}
                    {{-- <div class="form-group">
                <label class="required_field">Pilih Matriks Approval</label>
                <select class="form-control authorization"
                name="authorization_id" required>
                    <option value="">-- Pilih Matriks Approval --</option>
                    @foreach ($formperpanjangan_authorizations as $authorization)
                    @php
                    $list= $authorization->authorization_detail;
                    $string = "";
                    foreach ($list as $key=>$author){
                        $author->employee_position->name;
                        $string = $string.$author->employee->name;
                        if(count($list)-1 != $key){
                            $string = $string.' -> ';
                        }
                    }
                    $string .= " || ".$authorization->notes;
                    @endphp
                    <option value="{{ $authorization->id }}"
                        data-list = "{{ $list }}">
                        {{$string}}</option>
                    @endforeach
                </select>
                </div> --}}
                    {{-- autoselect --}}
                    <div class="form-group">
                        <label class="required_field">Matriks Approval</label>
                        @php
                            $is_niaga_text = $armadaticket->isNiaga ? 'Niaga' : 'Non-Niaga';
                            $authorization = $formperpanjangan_authorizations->where('notes', $is_niaga_text)->first();
                            if (isset($authorization)) {
                                $list = $authorization->authorization_detail;
                                $string = '';
                                foreach ($list as $key => $author) {
                                    $author->employee_position->name;
                                    $string = $string . $author->employee->name;
                                    if (count($list) - 1 != $key) {
                                        $string = $string . ' -> ';
                                    }
                                }
                                $string .= ' || ' . $authorization->notes;
                            }
                        @endphp
                        @isset($authorization)
                            <input type="hidden" name="authorization_id" value="{{ $authorization->id }}">
                            <textarea class="form-control" style="resize: none;" disabled>{{ $string }}</textarea>
                        @else
                            <input class="form-control" value="" placeholder="Matriks Approval terkait tidak ditemukan"
                                disabled>
                        @endisset
                    </div>
                </div>
                <div class="col-12 pt-2">
                    <table class="table table-bordered authorization_table">
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
            <div class="col-12 d-flex flex-column mt-2 ba_renewal" name="ba_renewal" id="ba_renewal"
                style="display:none !important">
            </div>
        </form>
    @endisset
@endif

<div class="modal fade" id="uploadBAPerpanjanganModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload BA Perpanjangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/armadaticketing/armada_ticket_code/uploadBAPerpanjangan" method="post"
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


@section('perpanjangan-js')
    {{-- form perpanjangan perhentian --}}
    <script>
        let formperpanjangan = $('#formperpanjangan');
        $(document).ready(function() {
            formperpanjangan.find('.authorization').change(function() {
                let list = $(this).find('option:selected').data('list');
                if (list == null) {
                    formperpanjangan.find('.authorization_table').hide();
                    return;
                }
                formperpanjangan.find('.authorization_table').show();
                let table_string = '<tr>';
                let temp = '';
                let col_count = 1;
                // authorization header
                list.forEach((item, index) => {
                    if (index > 0) {
                        if (temp == item.sign_as) {
                            col_count++;
                        } else {
                            table_string += '<td class="small" colspan="' + col_count + '">' +
                                temp + '</td>';
                            temp = item.sign_as;
                            col_count = 1;
                        }
                    } else {
                        temp = item.sign_as;
                    }
                    if (index == list.length - 1) {
                        table_string += '<td class="small" colspan="' + col_count + '">' + temp +
                            '</td>';
                    }
                });
                table_string += '</tr><tr>';
                list.forEach((item, index) => {
                    table_string +=
                        '<td width="20%" class="align-bottom small" style="height: 80px"><b>' + item
                        .employee.name + '</b><br>' + item.employee_position.name + '</td>';
                });
                table_string += '</tr>';

                formperpanjangan.find('.authorization_table tbody').empty();
                formperpanjangan.find('.authorization_table tbody').append(table_string);
            });

            $('input[type=radio][name=form_type]').on('change', function() {
                $('#stopsewa_date').val("");
                $('#perpanjangan_length').val("");
                $('#replace_radio').prop('checked', false);
                $('#renewal_radio').prop('checked', false);
                $('#end_radio').prop('checked', false);

                $('#stopsewa_date').prop('disabled', true);
                $('#stopsewa_date').prop('required', false);
                $('#perpanjangan_length').prop('disabled', true);
                $('#perpanjangan_length').prop('required', false);

                $('#replace_radio').prop('disabled', true);
                $('#renewal_radio').prop('disabled', true);
                $('#end_radio').prop('disabled', true);
                $('#replace_radio').prop('required', false);

                switch ($(this).val()) {
                    case 'perpanjangan':
                        $('#perpanjangan_length').prop('disabled', false);
                        $('#perpanjangan_length').prop('required', true);
                        $('#ba_renewal').attr('style', 'display: none !important');
                        $("#ba_renewal").empty();
                        break;
                    case 'stopsewa':
                        $('#stopsewa_date').prop('disabled', false);
                        $('#stopsewa_date').prop('required', true);
                        $('#replace_radio').prop('disabled', false);
                        $('#renewal_radio').prop('disabled', false);
                        $('#end_radio').prop('disabled', false);
                        $('#replace_radio').prop('required', true);
                        $('#ba_renewal').attr('style', 'display: none !important');
                        $("#ba_renewal").empty();
                        break;
                }
            });
            $('input[type=radio][name=form_type]').trigger('change');

            $("input[type=radio][name=alasan]").change(function() {
                let value = $(this).val();
                if (value == "renewal") {
                    $("#ba_renewal").show();
                    $("#ba_renewal").append(
                        `<div class="row mt-2"> 
                            <div class="col-2 required_field font-weight-bold">BA Renewal</div>
                            <div class="col-1">:</div>
                            <div class="col-7">
                                <input type="file" class="form-control-file form-control-sm validatefilesize"
                                name="upload_ba_renewal" required>
                            </div>
                        </div>`);
                } else {
                    $('#ba_renewal').attr('style', 'display: none !important');
                    $("#ba_renewal").empty();
                }
            });
        });

        function perpanjanganapprove(perpanjangan_form_id) {
            $('#submitform').prop('action', '/approveperpanjanganform');
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="perpanjangan_form_id" value="' +
                perpanjangan_form_id + '">');
            $('#submitform').submit();
        }

        function perpanjanganreject(perpanjangan_form_id) {
            var reason = prompt("Harap memasukan alasan reject formulir");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }
                $('#submitform').prop('action', '/rejectperpanjanganform');
                $('#submitform').prop('method', 'POST');
                $('#submitform').find('div').append('<input type="hidden" name="perpanjangan_form_id" value="' +
                    perpanjangan_form_id + '">');
                $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#submitform').submit();
            }
        }

        function uploadPerpanjanganBA(armada_ticket_code, po_number) {
            $('#uploadBAPerpanjanganModal input[name="ticket_code"]').val(armada_ticket_code);
            $('#uploadBAPerpanjanganModal input[name="po_number"]').val(po_number);
            $('#uploadBAPerpanjanganModal form').prop('action', "/armadaticketing/" + armada_ticket_code +
                "/uploadBAPerpanjangan");
            $('#uploadBAPerpanjanganModal').modal('show');
        }
    </script>
@endsection
