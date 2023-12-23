@if ($armadaticket->status != -1)
    @isset($armadaticket->mutasi_form)
        @php
            $mutasiform = $armadaticket->mutasi_form;
        @endphp
        <h5>Formulir Mutasi</h5>
        <div class="border p-2 border-dark">
            <h5 class="text-center">BERITA ACARA MUTASI INTERNAL ARMADA NIAGA/NON NIAGA</h5>
            <table class="table table-bordered table-sm">
                <tbody>
                    <tr>
                        <td width="25%">No. BA Mutasi</td>
                        <td width="75%" colspan="3">
                            {{ $mutasiform->code }}
                        </td>
                    </tr>
                    <tr>
                        <td width="25%">PMA Pengirim</td>
                        <td width="25%">
                            {{ $mutasiform->sender_salespoint_name }}
                        </td>
                        <td width="25%">PMA Penerima</td>
                        <td width="25%">
                            {{ $mutasiform->receiver_salespoint_name }}
                        </td>
                    </tr>
                    <tr>
                        <td width="25%">Tgl Mutasi</td>
                        <td width="25%">
                            {{ $mutasiform->mutation_date }}
                        </td>
                        <td width="25%">Tgl Terima</td>
                        <td width="25%">
                            {{ $mutasiform->received_date }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <span class="align-self-start small">* No. BA Mutasi hanya berlaku untuk satu dokumen</span>
            <p class="small">Sehubungan dengan adanya perubahan Cabang/Depo/CP, maka dilakukan mutasi armada dengan
                rincian data armada sebagai berikut:</p>
            <div class="row">
                <!-- <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Alasan Mutasi</div>
                <div class="col-8 small">
                    {{ $mutasiform->alasan_mutasi }}
                </div> -->
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>No. Polisi</div>
                <div class="col-8 small">
                    {{ $mutasiform->nopol }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Nama Pemilik (Vendor)</div>
                <div class="col-8 small">
                    {{ $mutasiform->vendor_name }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Merk Kendaraan</div>
                <div class="col-8 small">
                    {{ $mutasiform->brand_name }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Tipe/Jenis Kendaraan</div>
                <div class="col-8 small">
                    {{ $mutasiform->jenis_kendaraan }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>No. Rangka</div>
                <div class="col-8 small">
                    {{ $mutasiform->nomor_rangka }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>No. Mesin</div>
                <div class="col-8 small">
                    {{ $mutasiform->nomor_mesin }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Tahun Pembuatan</div>
                <div class="col-8 small">
                    {{ $mutasiform->tahun_pembuatan }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Masa Berlaku STNK</div>
                <div class="col-8 small">
                    {{ $mutasiform->stnk_date }}
                </div>
            </div>
            <p class="small">Kelengkapan kendaraan: </p>
            <div class="row">
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Kotak P3k</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->p3k ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Segitiga Darurat</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->segitiga ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Dongkrak</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->dongkrak ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Tool Kit Standar</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->toolkit ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Ban Serep</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->ban ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Kunci Gembok</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->gembok ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2 "><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Ijin Bongkar</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->bongkar ? 'Ya' : 'Tidak' }}
                </div>
                <div class="col-4 small py-2"><i class="fa fa-circle fa-xs mr-1" style="font-size : 0.5rem"
                        aria-hidden="true"></i>Buku Keur</div>
                <div class="col-8 py-2 small">
                    {{ $mutasiform->buku ? 'Ya' : 'Tidak' }}
                </div>
            </div>
            <p class="small">Semikian berita acara mutasi armada ini kamu buat untuk dapat digunakan sebagaimana
                mestinya. Terimakasih atas perhatian dan kerjasamanya.</p>
            <div class="row">
                {{ $mutasiform->nama_tempat }}, {{ $mutasiform->created_at->translatedFormat('d F Y') }}
            </div>
            <style>
                .sign_space {
                    height: 125px;
                }
            </style>
            <div class="form-group mt-3">
            </div>

            <table class="table table-bordered authorization_table">
                <tbody>
                    @php
                        $count = $mutasiform->authorizations->count();
                        $headers_name = [];
                        $headers_colspan = [];
                        foreach ($mutasiform->authorizations as $authorization) {
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
                        @foreach ($mutasiform->authorizations as $authorization)
                            <td width="{{ 100 / $count }}%" class="align-bottom small" style="height: 80px">
                                @if (($mutasiform->current_authorization()->id ?? -1) == $authorization->id)
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
            <div class="col-md-8">
                <div class="form-group">
                    <div class="row">
                        <label>Alasan Mutasi</label>
                        <input type="input" class="form-control" name="alasan_mutasi"
                            value="{{ $mutasiform->alasan_mutasi ?? '-' }}" readonly>
                    </div>
                </div>
            </div>
            <div class="text-center">
                @if (($mutasiform->current_authorization()->employee_id ?? '-1') == Auth::user()->id)
                    <button type="button" class="btn btn-success"
                        onclick="mutasiapprove({{ $mutasiform->id }})">Approve</button>
                    <button type="button" class="btn btn-danger"
                        onclick="mutasireject({{ $mutasiform->id }})">Reject</button>
                @endif
            </div>
            @if ($mutasiform->status == 1)
                <div class="d-flex justify-content-center">
                    <a class="btn btn-primary btn-sm" href="/printmutasiform/{{ $armadaticket->code }}"
                        role="button">Cetak</a>
                </div>
            @endif
        </div>
    @else
        <form id="formmutasi" method="post" action="/addmutasiform">
            @csrf
            <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
            <input type="hidden" name="armada_id" value="{{ $armadaticket->armada_id }}">
            <h5>Formulir Mutasi</h5>
            @isset($armadaticket->last_rejected_mutasi_form)
                Di Reject Oleh : <span
                    class="text-danger">{{ $armadaticket->last_rejected_mutasi_form->terminated_by_employee->name ?? '-' }}</span><br>
                Alasan Reject : <span
                    class="text-danger">{{ $armadaticket->last_rejected_mutasi_form->termination_reason ?? '-' }}</span>
            @endisset
            <div class="border p-2 border-dark">
                <h5 class="text-center">BERITA ACARA MUTASI INTERNAL ARMADA NIAGA/NON NIAGA</h5>
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <td width="25%" class="required_field">No. BA Mutasi</td>
                            <td width="75%" colspan="3">
                                <input type="text" class="form-control form-control-sm"
                                    value="akan dibuat oleh sistem" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td width="25%" class="required_field">PMA Pengirim</td>
                            <td width="25%">
                                <input type="hidden" name="sender_salespoint_id"
                                    value="{{ $armadaticket->salespoint_id }}">
                                <input type="text" class="form-control" name="sender_salespoint_name"
                                    value="{{ $armadaticket->salespoint->name }}" readonly>
                            </td>
                            <td width="25%" class="required_field">PMA Penerima</td>
                            <td width="25%">
                                <div class="form-group">
                                    <select class="form-control select2" name="receive_salespoint_id" required>
                                        <option value="">Pilih Tujuan</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}"
                                                @if ($armadaticket->salespoint_id == $salespoint->id) disabled @endif>{{ $salespoint->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="25%" class="required_field">Tgl Mutasi</td>
                            <td width="25%">
                                <input type="date" class="form-control form-control-sm" name="mutation_date" required>
                            </td>
                            <td width="25%" class="required_field">Tgl Terima</td>
                            <td width="25%">
                                <input type="date" class="form-control form-control-sm" name="received_date" required>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <span class="align-self-start small">* No. BA Mutasi hanya berlaku untuk satu dokumen</span>
                <p class="small">Sehubungan dengan adanya perubahan Cabang/Depo/CP, maka dilakukan mutasi armada
                    dengan rincian data armada sebagai berikut:</p>
                <div class="row">
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>No. Polisi</div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm" name="nopol"
                            value="{{ $armadaticket->armada->plate ?? $pomanual->plate() }}" readonly>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Nama Pemilik (Vendor)</div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm" name="vendor_name"
                            value="{{ $po->sender_name ?? $pomanual->vendor_name }}" required>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Merk Kendaraan</div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm"
                            value="{{ $armadaticket->armada_type->brand_name }}" name="merk" readonly>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Tipe/Jenis Kendaraan</div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm" name="jenis_kendaraan"
                            value="{{ $armadaticket->armada_type->name }}" readonly>
                    </div>
                    <div class="col-4 small py-2 optional_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>No. Rangka</div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm" name="nomor_rangka">
                    </div>
                    <div class="col-4 small py-2 optional_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>No. Mesin</div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm" name="nomor_mesin">
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Tahun Pembuatan</div>
                    <div class="col-8">
                        <input type="number" class="form-control form-control-sm autonumber" min="1970"
                            max="{{ now()->format('Y') }}" name="tahun_pembuatan" required>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Masa Berlaku STNK</div>
                    <div class="col-8">
                        <input type="date" class="form-control form-control-sm" name="stnk_date" required>
                    </div>
                </div>
                <p class="small">Kelengkapan kendaraan: </p>
                <div class="row">
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Kotak P3k</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="p3k" value="1" id="p3k_ada" required>
                            <label class="mb-0 mr-2 font-weight-normal" for="p3k_ada">Ada</label>
                            <input type="radio" class="mr-1" name="p3k" value="0" id="p3k_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="p3k_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Segitiga Darurat</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="segitiga" value="1" id="segitiga_ada"
                                required>
                            <label class="mb-0 mr-2 font-weight-normal" for="segitiga_ada">Ada</label>
                            <input type="radio" class="mr-1" name="segitiga" value="0" id="segitiga_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="segitiga_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Dongkrak</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="dongkrak" value="1" id="dongkrak_ada"
                                required>
                            <label class="mb-0 mr-2 font-weight-normal" for="dongkrak_ada">Ada</label>
                            <input type="radio" class="mr-1" name="dongkrak" value="0" id="dongkrak_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="dongkrak_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Tool Kit Standar</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="toolkit" value="1" id="toolkit_ada"
                                required>
                            <label class="mb-0 mr-2 font-weight-normal" for="toolkit_ada">Ada</label>
                            <input type="radio" class="mr-1" name="toolkit" value="0" id="toolkit_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="toolkit_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Ban Serep</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="ban" value="1" id="ban_ada" required>
                            <label class="mb-0 mr-2 font-weight-normal" for="ban_ada">Ada</label>
                            <input type="radio" class="mr-1" name="ban" value="0" id="ban_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="ban_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Kunci Gembok</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="gembok" value="1" id="gembok_ada"
                                required>
                            <label class="mb-0 mr-2 font-weight-normal" for="gembok_ada">Ada</label>
                            <input type="radio" class="mr-1" name="gembok" value="0" id="gembok_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="gembok_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Ijin Bongkar</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="bongkar" value="1" id="bongkar_ada"
                                required>
                            <label class="mb-0 mr-2 font-weight-normal" for="bongkar_ada">Ada</label>
                            <input type="radio" class="mr-1" name="bongkar" value="0" id="bongkar_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="bongkar_tidak">Tidak</label>
                        </div>
                    </div>
                    <div class="col-4 small py-2 required_field"><i class="fa fa-circle fa-xs mr-1"
                            style="font-size : 0.5rem" aria-hidden="true"></i>Buku Keur</div>
                    <div class="col-8 py-2">
                        <div class="d-flex small align-items-center">
                            <input type="radio" class="mr-1" name="buku" value="1" id="buku_ada" required>
                            <label class="mb-0 mr-2 font-weight-normal" for="buku_ada">Ada</label>
                            <input type="radio" class="mr-1" name="buku" value="0" id="buku_tidak">
                            <label class="mb-0 mr-2 font-weight-normal" for="buku_tidak">Tidak</label>
                        </div>
                    </div>
                </div>
                <p class="small">Semikian berita acara mutasi armada ini kamu buat untuk dapat digunakan
                    sebagaimana mestinya. Terimakasih atas perhatian dan kerjasamanya.</p>
                <div class="row">
                    <div class="col-3">
                        <input type="text" class="form-control form-control-sm" id="nama_tempat" name="nama_tempat"
                            placeholder="isi nama tempat" required>
                    </div>
                    <div class="col-3">
                        <span class="align-middle">,{{ now()->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                <style>
                    .sign_space {
                        height: 125px;
                    }
                </style>
                {{-- <div class="form-group mt-3">
                    <label class="required_field">Pilih Matriks Approval</label>
                    <select class="form-control authorization" name="authorization_id" required>
                        <option value="">Pilih Matriks Approval</option>
                        @foreach ($formmutasi_authorizations as $authorization)
                            @php
                                $list = $authorization->authorization_detail;
                                $string = '';
                                foreach ($list as $key => $author) {
                                    $author->employee_position->name;
                                    $string = $string . $author->employee->name;
                                    if (count($list) - 1 != $key) {
                                        $string = $string . ' -> ';
                                    }
                                }
                            @endphp
                            <option value="{{ $authorization->id }}" data-list="{{ $list }}">
                                {{ $string }}</option>
                        @endforeach
                    </select>
                </div> --}}

                {{-- autoselect --}}
                <div class="form-group mt-3">
                    <label class="required_field">Matriks Approval</label>
                    @php
                        $is_niaga_text = $armadaticket->isNiaga ? 'Niaga' : 'Non-Niaga';
                        $authorization = $formmutasi_authorizations->where('notes', $is_niaga_text)->first();
                        $string = '';
                        if (isset($authorization)) {
                            $list = $authorization->authorization_detail;
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
                <table class="table table-bordered table-sm mt-3 authorization_table">
                    <tbody></tbody>
                </table>
                <span class="small">FRM-HCD-107 REV 04</span>
            </div>

            <br>

            <div class="col-md-8">
                <div class="form-group">
                    <div class="row">
                        <label class="required_field">Alasan Mutasi</label>
                        <input type="input" class="form-control" name="alasan_mutasi">
                    </div>
                    <br>
                </div>
            </div>

            <div class="col-md-8">
                <div class="form-group">
                    <div class="row">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    <br>
                </div>
            </div>
        </form>
    @endisset
@endif
@section('mutasi-js')
    {{-- form mutasi --}}
    <script>
        let formmutasi = $('#formmutasi');
        $(document).ready(function() {
            formmutasi.find('.authorization').change(function() {
                let list = $(this).find('option:selected').data('list');
                if (list == null) {
                    formmutasi.find('.authorization_table').hide();
                    return;
                }
                formmutasi.find('.authorization_table').show();
                let table_string = '<tr>';
                let temp = '';
                let col_count = 1;
                // authorization header
                list.forEach((item, index) => {
                    if (index > 0) {
                        if (temp == item.sign_as) {
                            col_count++;
                        } else {
                            table_string +=
                                '<td class="align-middle small table-secondary" colspan="' +
                                col_count + '">' + temp + '</td>';
                            temp = item.sign_as;
                            col_count = 1;
                        }
                    } else {
                        temp = item.sign_as;
                    }
                    if (index == list.length - 1) {
                        table_string += '<td class="align-middle small table-secondary" colspan="' +
                            col_count + '">' + temp + '</td>';
                    }
                });
                table_string += '</tr><tr>';
                // authorization body
                let width = 100 / list.length;
                list.forEach((item, index) => {
                    table_string += '<td width="' + width +
                        '%" class="align-bottom small" style="height: 120px"><b>' + item.employee
                        .name + '</b><br>' + item.employee_position.name + '</td>';
                });
                table_string += '</tr>';

                formmutasi.find('.authorization_table tbody').empty();
                formmutasi.find('.authorization_table tbody').append(table_string);
            });
        });

        function mutasiapprove(mutasi_form_id) {
            $('#submitform').prop('action', '/approvemutasiform');
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="mutasi_form_id" value="' + mutasi_form_id +
                '">');
            $('#submitform').submit();
        }

        function mutasireject(mutasi_form_id) {
            var reason = prompt("Harap memasukan alasan reject formulir");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }
                $('#submitform').prop('action', '/rejectmutasiform');
                $('#submitform').prop('method', 'POST');
                $('#submitform').find('div').append('<input type="hidden" name="mutasi_form_id" value="' + mutasi_form_id +
                    '">');
                $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#submitform').submit();
            }
        }
    </script>
@endsection