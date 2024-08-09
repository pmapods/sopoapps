@extends('Layout.app')
@section('local-css')
    <style>
        .box {
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
            border: 1px solid;
            border-color: #dcdcdc;
            background-color: #FFF;
            border-radius: 0.5em;
        }

        hr {
            border: 1px solid rgb(0, 0, 0) !important;
            margin: 0 !important;
        }

        .sign_space {
            height: 100px !important
        }

        .textarea_text {
            white-space: pre-line;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        @php
            if (!empty($ticket)) {
                $code = $ticket->code;
            }
            if (!empty($armadaticket)) {
                $code = $armadaticket->code;
            }
            if (!empty($securityticket)) {
                $code = $securityticket->code;
            }
        @endphp
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Pembuatan PO ({{ $code }})</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Purchase Requisition</li>
                        <li class="breadcrumb-item active">PO ({{ $code }})</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="text-right"><button type="button" class="btn btn-info mr-2" id="cetak_all_po_button">Cetak All PO</button>
    </div>
    <div class="content-body">
        @php
            $pos = '';
            $type = '';
            if (isset($ticket)) {
                $pos = $ticket->po;
                $type = 'barangjasa';
            }
            if (isset($armadaticket)) {
                $pos = $armadaticket->po;
                $type = 'armada';
            }
            if (isset($securityticket)) {
                $pos = $securityticket->po;
                $type = 'security';
            }
        @endphp
        <div class="row">
            @foreach ($pos as $po)
                <div class="col-md-6 col-12 p-2 po_field">
                    <form action="/submitPO" method="post" enctype="multipart/form-data">
                        @method('post')
                        @csrf
                        <input type="hidden" name="po_id" value="{{ $po->id }}">
                        <input type="hidden" name="updated_at" value="{{ $po->updated_at }}">
                        <div class="box d-flex flex-column p-3">
                            <div class="row">
                                <div class="col">
                                    <h4>{{ $po->sender_name }}</h4>
                                </div>
                                @if ($po->status == 0 && $po->issue != null)
                                    <div class="col table-warning">
                                        <a href='/storage/{{ $po->issue->ba_file }}'>Tampilkan BA Issue PO</a><br>
                                        <span class="text-secondary small">notes issue: {{ $po->issue->notes }}</span>
                                    </div>
                                @endif
                                {{-- jika ada reject dari vendor --}}
                                @if ($po->status == -1 && isset($po->po_upload_request))
                                    <div class="col-12 table-danger">
                                        <b>Laporan Kesalahan PO:</b><br>
                                        <div class="row">
                                            <div class="col-4">Tanggal Reject</div>
                                            <div class="col-8">:
                                                {{ \Carbon\Carbon::parse($po->po_upload_request->rejected_at)->translatedFormat('d F Y (H:i)') }}
                                            </div>
                                            <div class="col-4">Pelapor</div>
                                            <div class="col-8">: {{ $po->po_upload_request->rejected_by }}</div>
                                            <div class="col-4">Notes Kesalahan</div>
                                            <div class="col-8">: {{ $po->po_upload_request->reject_notes }}</div>

                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-6 d-flex flex-column">
                                    <b>Nama Vendor</b>
                                    <span>{{ $po->sender_name }}</span>
                                </div>
                                <div class="col-6 d-flex flex-column text-right">
                                    <b>Nama Salespoint</b>
                                    <span>{{ $po->send_name }}</span>
                                </div>

                                <div class="col-6 d-flex flex-column">
                                    <label class="required_field">Alamat Vendor</label>
                                    @php
                                        $temp_sender_address = $po->sender_address;
                                    @endphp
                                    @if ($po->status != -1)
                                        <span>{{ $po->sender_address }}</span>
                                    @else
                                        <textarea class="form-control" rows="3" placeholder="Masukkan Alamat vendor" name="sender_address" required>{{ $temp_sender_address }}</textarea>
                                    @endif
                                </div>
                                <div class="col-6 d-flex flex-column text-right">
                                    <label class="required_field">Alamat Kirim / SalesPoint</label>
                                    @php
                                        $temp_send_address = $po->send_address;
                                    @endphp
                                    @if ($po->status != -1)
                                        <span>{{ $po->send_address }}</span>
                                    @else
                                        <textarea class="form-control" rows="3" name="send_address" placeholder="Masukkan Alamat Kirim" required>{{ $temp_send_address }}</textarea>
                                    @endif
                                </div>
                            </div>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th width="25%">Nama Barang</th>
                                            <th width="10%">Jumlah</th>
                                            <th width="20%">Harga/Unit</th>
                                            <th width="20%" class="text-right">Total</th>
                                            <th width="25%" class="text-center">Tanggal Kirim</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total = 0;
                                            $subtotal = 0;
                                            $ppn = 0;
                                            $groups = $po->po_detail->groupBy('item_number');
                                            $groupPoDetail = $groups->map(function ($group) {
                                                return (object) [
                                                    'item_number' => $group->first()['item_number'],
                                                    'item_name' => $group->first()['item_name'],
                                                    'item_description' => $group->first()['item_description'],
                                                    'qty' => $group->sum('qty'),
                                                    'uom' => $group->first()['uom'],
                                                    'item_price' => $group->first()['item_price'],
                                                    'delivery_notes' => $group->implode('delivery_notes', "\n"),
                                                ];
                                            });
                                        @endphp
                                        @foreach ($groupPoDetail as $key => $po_detail)
                                            <tr>
                                                <td>
                                                    {{ $po_detail->item_name }}<br>
                                                    <span class="small text-secondary">
                                                        {!! nl2br(e($po_detail->item_description)) !!}
                                                    </span>
                                                </td>
                                                <td>{{ $po_detail->qty }} {{ $po_detail->uom }}</td>
                                                <td class="rupiah">{{ $po_detail->item_price }}</td>
                                                <td class="rupiah text-right">
                                                    {{ $po_detail->qty * $po_detail->item_price }}
                                                </td>
                                                <td class="text-center small">
                                                    <span>{!! nl2br(e($po_detail->delivery_notes)) !!}</span>
                                                </td>
                                                @php
                                                    $subtotal += $po_detail->qty * $po_detail->item_price;
                                                    
                                                @endphp

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @php
                                $ppn = 0;
                                if ($po->has_ppn && $po->status != -1) {
                                    $ppn = ($po->ppn_percentage / 100) * $subtotal;
                                }
                                $total = $subtotal + $ppn;
                            @endphp
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td>Subtotal</td>
                                        @if ($po->status == -1)
                                            <td class="rupiah text-right" id="subtotal">{{ $subtotal }}</td>
                                        @else
                                            <td class="rupiah text-right">{{ $subtotal }}</td>
                                        @endif
                                    </tr>
                                    @if ($po->status == -1)
                                        <tr>
                                            <td class="d-flex align-items-center">
                                                <input type="checkbox" name="has_ppn" class="mr-1" id="ppn_check">
                                                <span class="mr-1">PPN</span>
                                                <div class="input-group input-group-sm mr-1" style="width: 7em">
                                                    <input type="number" class="form-control" step="0.1"
                                                        name="ppn_percentage" id="ppn_percentage" value="11" disabled>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-right" id="ppn_value">-</td>
                                        </tr>
                                    @else
                                        @if ($po->has_ppn)
                                            <tr>
                                                <td>PPN ({{ $po->ppn_percentage }}%)</td>
                                                <td class="rupiah text-right">{{ $ppn }}</td>
                                            </tr>
                                        @endif
                                    @endif
                                    <tr>
                                        <td>Total</td>
                                        @if ($po->status == -1)
                                            <td class="font-weight-bold rupiah text-right" id="total">
                                                {{ $total }}</td>
                                        @else
                                            <td class="font-weight-bold rupiah text-right">{{ $total }}</td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                            <h5>Kelengkapan data PO</h5>
                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label>No PR SAP</label>
                                        <input type="text" class="form-control" name="no_pr_sap"
                                            value="{{ $po->no_pr_sap }}" readonly="readonly">
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label>No PO SAP</label>
                                        <input type="text" class="form-control" name="no_po_sap"
                                            value="{{ $po->no_po_sap }}" readonly="readonly">
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label>Tanggal Buat</label>
                                        <input type="date" class="form-control" name="date_po_sap"
                                            value="{{ $po->status != -1 ? $po->created_at->format('Y-m-d') : now()->format('Y-m-d') }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="required_field">Pembayaran / Payment</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" placeholder="Hari"
                                            name="payment_days" min="0" value="{{ $po->payment_days }}" readonly>
                                        <div class="input-group-append">
                                            <div class="input-group-text">Hari / Days</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label>Gunakan Reminder?</label>
                                        <select class="form-control" name="has_reminder"
                                            @if ($po->status != -1) readonly @endif>
                                            <option value="1" @if ($po->status != -1 && ($po->start_date && $po->end_date)) selected @endif>Ya
                                            </option>
                                            <option value="0" @if ($po->status != -1 && (!$po->start_date || !$po->end_date)) selected @endif>Tidak
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12"></div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label>Reminder Start Date</label>
                                        <input type="date" class="form-control" id="reminder_start_select"
                                            min="{{ now()->format('Y-m-d') }}" name="start_date"
                                            value="{{ $po->status != -1 ? $po->start_date : null }}"
                                            @if ($po->status != -1) readonly @else required @endif>
                                        <small class="text-danger">*hanya untuk reminder tidak tercantum di PO</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label>Reminder End Date</label>
                                        <input type="date" class="form-control" id="reminder_end_select"
                                            min="{{ now()->format('Y-m-d') }}" name="end_date"
                                            value="{{ $po->status != -1 ? $po->end_date : null }}"
                                            @if ($po->status != -1) readonly @else required @endif>
                                        <small class="text-danger">*hanya untuk reminder tidak tercantum di PO</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="optional_field">Notes</label>
                                        <textarea class="form-control" placeholder="notes" name="notes" rows="3"
                                            @if ($po->status != -1) readonly @endif>{{ $po->status != -1 ? $po->notes : '' }}</textarea>
                                    </div>
                                </div>

                                @if ($po->status == -1)
                                    <br>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" required>
                                            Apakah Nama Salespoint
                                        </label>
                                        <span class="font-weight-bolder">
                                            PODS
                                        </span>
                                        Sudah Sesuai Dengan Nama Salespoint
                                        <span class="font-weight-bolder">SAP</span>
                                        ?
                                    </div>
                                    <br>
                                    <br>
                                @endif
                            </div>
                            @if ($po->status == -1)
                                <div class="form-group">
                                    <label class="required_field">Pilih Matriks Approval</label>
                                    <select class="form-control authorization_select2" name="authorization_id" required>
                                        <option value="">Pilih Matriks Approval</option>
                                        @foreach ($authorization_list as $auth_select)
                                            @php
                                                $list = $auth_select->authorization_detail;
                                                $string = '';
                                                foreach ($list as $key => $author) {
                                                    $string = $string . $author->employee->name;
                                                    $open = $author->employee_position;
                                                    if (count($list) - 1 != $key) {
                                                        $string = $string . ' -> ';
                                                    }
                                                }
                                            @endphp
                                            <option value="{{ $auth_select->id }}"
                                                data-list="{{ $auth_select->list() }}">{{ $string }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="authorization_select_field row">
                                    <div class="col-md-4 px-1">
                                        <div class="border border-dark d-flex flex-column">
                                            <div class="text-center small">
                                                Dibuat Oleh<br>
                                                Created by</i>
                                                <hr>
                                            </div>
                                            <div class="sign_space"></div>
                                            <span class="align-self-center text-uppercase name1">&nbsp</span>
                                            <span class="align-self-center position1">&nbsp</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 px-1">
                                        <div class="border border-dark d-flex flex-column">
                                            <div class="text-center small">
                                                Diperiksa dan disetujui oleh<br>
                                                Checked and Approval by</i>
                                                <hr>
                                            </div>
                                            <div class="sign_space"></div>
                                            <span class="align-self-center text-uppercase name2">&nbsp</span>
                                            <span class="align-self-center position2">&nbsp</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 px-1">
                                        <div class="border border-dark d-flex flex-column">
                                            <div class="text-center small">
                                                Konfirmasi Supplier<br>
                                                Supplier Confirmation</i>
                                                <hr>
                                            </div>
                                            <div class="sign_space"></div>
                                            <input type="text" class="form-control form-control-sm text-center"
                                                name="supplier_pic_name" maxlength="30"
                                                placeholder="Masukkan nama PIC (optional)">
                                            <input type="text" class="form-control form-control-sm text-center"
                                                name="supplier_pic_position" maxlength="30"
                                                placeholder="Masukkan posisi PIC (optional)">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    @php
                                        $names = ['Dibuat Oleh', 'Diperiksa dan disetujui oleh', 'Konfirmasi Supplier'];
                                        $enames = ['Created by', 'Checked and Approval by', 'Supplier Confirmation'];
                                        $po_authorizations = $po->po_authorization;
                                        $authorizations = [];
                                        foreach ($po_authorizations as $po_authorization) {
                                            $auth = new \stdClass();
                                            $auth->employee_name = $po_authorization->employee_name;
                                            $auth->employee_position = $po_authorization->employee_position;
                                            array_push($authorizations, $auth);
                                        }
                                        $auth = new \stdClass();
                                        $auth->employee_name = $po->supplier_pic_name;
                                        $auth->employee_position = $po->supplier_pic_position;
                                        array_push($authorizations, $auth);
                                    @endphp
                                    @foreach ($authorizations as $key => $authorization)
                                        <div class="col-md-4 px-1">
                                            <div class="border border-dark d-flex flex-column">
                                                <div class="text-center small">
                                                    {{ $names[$key] }}<br>
                                                    <i>{{ $enames[$key] }}</i>
                                                    <hr>
                                                </div>
                                                <div class="sign_space"></div>
                                                <span class="align-self-center text-uppercase">
                                                    {{ $authorization->employee_name }}
                                                    @if ($authorization->employee_name == '')
                                                        {!! '&nbsp;' !!}
                                                    @endif
                                                </span>
                                                <span class="align-self-center">
                                                    {{ $authorization->employee_position }}
                                                    @if ($authorization->employee_position == '')
                                                        {!! '&nbsp;' !!}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($po->status == 0)
                                <small class="text-danger">*harap melakukan upload dokumen yang sudah ditanda tangan
                                    basah oleh tim internal</small>
                            @endif

                            @if ($po->status == 1)
                                <small class="text-info">*Menunggu supplier untuk melakukan upload file po yang sudah
                                    dilengkapi tanda tangan basah dari supplier bersangkutan</small>
                            @endif

                            @if ($po->status == 3)
                                <small class="text-info">*Menunggu penerimaan barang oleh salespoint/area
                                    bersangkutan</small>
                            @endif

                            <div class="display_field my-1 d-flex flex-column">
                                @if ($po->status == 1)
                                    @php
                                        $filename = explode('/', $po->internal_signed_filepath);
                                        $filename = $filename[count($filename) - 1];
                                    @endphp
                                    <a class="uploaded_file text-primary" style="cursor:pointer"
                                        onclick='window.open("/storage/{{ $po->internal_signed_filepath }}")'>Tampilkan
                                        dokumen Internal Signed</a>
                                    @if ($po->additional_po_filepath)
                                        <a class="uploaded_file text-primary" style="cursor:pointer"
                                            onclick='window.open("/storage/{{ $po->additional_po_filepath }}")'>Tampilkan
                                            dokumen Additional attachment</a>
                                    @endif
                                    <small><b>Status</b> :
                                        {{ $po->po_upload_request->isOpened == false ? 'Link Upload File belum dibuka oleh Vendor' : 'Link Upload File sudah dibuka oleh Vendor' }}</small>
                                    <small><b>Last Mail Send to</b> : {{ $po->last_mail_send_to }}</small>
                                    <small><b>Last Mail CC to</b> : {{ $po->last_mail_cc_to }}</small>
                                    <small><b>Last Mail Subject</b> : {{ $po->last_mail_subject }}</small>
                                    <small><b>Last Mail Text</b> : {{ $po->last_mail_text }}</small>
                                @endif
                                @if ($po->status == 2)
                                    <a class="uploaded_file text-primary font-weight-bold" style="cursor: pointer;"
                                        onclick='window.open("/storage/{{ $po->po_upload_request->filepath }}")'>
                                        Cek dokumen dengan tanda tangan supplier
                                    </a>
                                @endif
                                @if ($po->status == 3)
                                    @if ($po->external_signed_filepath)
                                        <a class="uploaded_file text-primary font-weight-bold" style="cursor: pointer;"
                                            onclick='window.open("/storage/{{ $po->external_signed_filepath }}")'>Dokumen
                                            PO dengan Tanda Tangan Vendor
                                        </a>
                                    @elseif ($po->internal_signed_filepath)
                                        <a class="uploaded_file text-primary font-weight-bold" style="cursor: pointer;"
                                            onclick='window.open("/storage/{{ $po->internal_signed_filepath }}")'>Dokumen
                                            PO dengan Tanda Tangan
                                        </a>
                                    @else
                                    @endif

                                    @if ($po->additional_po_filepath)
                                        <a class="uploaded_file text-primary font-weight-bold" style="cursor: pointer;"
                                            onclick='window.open("/storage/{{ $po->additional_po_filepath }}")'>Dokumen Additional attachment
                                        </a>
                                    @else
                                    @endif
                                @endif
                            </div>

                            @php
                                $toEmail = implode(', ' . "\n", $po->sender_email()) ?? '';
                                $cc = implode(', ' . "\n", $po->cc());
                                $mail_subject = 'Keperluan Upload Tanda tangan Basah';
                                try {
                                    $mail_subject = 'PO ';
                                    if (isset($groupPoDetail->sortByDesc('item_price')->first()->item_name)) {
                                        $mail_subject .= $groupPoDetail->sortByDesc('item_price')->first()->item_name . ' ';
                                    }
                                    $mail_subject .= $po->send_name . ' ';
                                    $mail_subject .= $po->no_po_sap . ' ';
                                    $mail_subject .= ' - ' . $po->sender_name . ' ';
                                } catch (\Throwable $th) {
                                }
                            @endphp

                            @if ($po->status == 0)
                                <div class="form-group">
                                    <label class="required_field">Email Subject</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $mail_subject }}" placeholder="SUBJECT PO" name="mail_subject"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label class="required_field">Email To</label>
                                    <textarea class="form-control form-control-sm" rows="5" name="email" required>{{ $toEmail }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="optional_field">Email Cc</label>
                                    <textarea class="form-control form-control-sm" rows="5" name="cc">{{ $cc }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="required_field">Email Text</label>
                                    <textarea class="form-control form-control-sm" name="email_text" rows="6">{{ $po->email_template() }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Signature (sesuai akun login)</label>
                                    @if (Auth::user()->signature_filepath)
                                        <div class="card">
                                            <img class="img-fluid" src="/storage/{{ Auth::user()->signature_filepath }}">
                                        </div>
                                    @else
                                        <div>Tidak ada signature</div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label class="required_field">Pilih File PO yang sudah di Tanda tangan Internal</label>
                                    <input type="file" class="form-control-file validatefilesize"
                                        name="internal_signed_file" accept="image/*,application/pdf" required>
                                    <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                </div>
                                <div class="form-group">
                                    <label>Pilih Additional attachment (Optional)</label>
                                    <input type="file" class="form-control-file validatefilesize"
                                        name="additional_po_file" accept="image/*,application/pdf">
                                    <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input needVendorConfirmation_check"
                                            checked> Membutuhkan Konfirmasi Vendor ?
                                    </label>
                                </div>
                                <input type="hidden" class="needVendorConfirmation_hidden" name="needVendorConfirmation"
                                    value="1">
                            @endif

                            @if ($po->status >= 0)
                                <div class="d-flex justify-content-start">
                                    <a href="#" onclick="window.open('/po/{{ $code }}/compare')">Compare
                                        Data</a>
                                </div>
                            @endif

                            @if ($po->status == 0)
                                <br>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" required>
                                        Apakah Nama Salespoint
                                    </label>
                                    <span class="font-weight-bolder">
                                        PODS
                                    </span>
                                    Sudah Sesuai Dengan Nama Salespoint
                                    <span class="font-weight-bolder">SAP</span>
                                    ?
                                </div>
                            @endif

                            <div class="align-self-center mt-3 button_field">
                                @if ($po->status != -1)
                                    @if ($po->status == 0)
                                        <button type="button" class="btn btn-secondary"
                                            onclick="revisedata({{ $po->no_po_sap }})">Revisi Data</button>
                                        <button type="button" class="btn btn-info cetak_button"
                                            data-url="{{ url('') }}/printPO?code={{ $po->no_po_sap }}"
                                            onclick="window.open('/printPO?code={{ $po->no_po_sap }}')">Cetak
                                            PO</button>
                                        <button type="button" class="btn btn-success" onclick="uploadfile(this)">Upload
                                            File</button>
                                    @endif

                                    @if ($po->status == 1)
                                        <button type="button" class="btn btn-info cetak_button"
                                            data-url="{{ url('') }}/printPO?code={{ $po->no_po_sap }}"
                                            onclick="window.open('/printPO?code={{ $po->no_po_sap }}')">Cetak
                                            PO</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="revisedata({{ $po->no_po_sap }})">Revisi Data</button>
                                        <button type="button" class="btn btn-info"
                                            data-to="{{ $po->last_mail_send_to }}" data-cc="{{ $po->last_mail_cc_to }}"
                                            data-text="{{ $po->last_mail_text }}"
                                            data-subject="{{ $po->last_mail_subject }}"
                                            onclick="send_email({{ $po->id }},'{{ $po->no_po_sap }}',this)">Kirim Ulang
                                            Email</button>
                                    @endif

                                    @if ($po->status == 2)
                                        <button type="button" class="btn btn-danger"
                                            onclick="reject({{ $po->id }},'{{ $po->no_po_sap }}','{{ $po->po_upload_request->id }}')">Reject</button>
                                        <button type="button" class="btn btn-success"
                                            onclick="confirm({{ $po->id }})">Confirm</button>
                                    @endif

                                    @if ($po->status == 3)
                                        <button type="button" class="btn btn-info"
                                            onclick="reminderUpdate({{ $po->id }},'{{ $po->no_po_sap }}','{{ $po->start_date }}','{{ $po->end_date }}')">Update
                                            Reminder</button>
                                    @endif

                                    @if (in_array($po->status, [1, 3]) && $po->po_upload_request)
                                        <button type="submit" formaction="/cancelvendorconfirmation/{{ $po->id }}"
                                            class="btn btn-danger" name="cancel_vendor_confirmation"
                                            id="cancel_vendor_confirmation">Batalkan
                                            Konfirmasi Vendor</button>
                                    @endif

                                    @if (in_array($po->status, [1, 3]) && $po->po_upload_request == null)
                                        <button type="submit"
                                            formaction="/confirmvendorconfirmation/{{ $po->id }}"
                                            class="btn btn-danger" name="confirm_vendor_confirmation"
                                            id="confirm_vendor_confirmation">Confirm
                                            Konfirmasi Vendor</button>
                                    @endif

                                    <button type="submit" class="d-none"></button>
                                @else
                                    <button type="submit" class="btn btn-primary">Terbitkan PO</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="row d-flex justify-content-center">
            @php
                $canRevise = true;
                $canTerminate = false;
                $type = '';
                if (isset($ticket)) {
                    $type = 'barangjasa';
                    $id = $ticket->id;
                }
                if (isset($armadaticket)) {
                    $type = 'armada';
                    $id = $armadaticket->id;
                    if (in_array($armadaticket->status, [4, 5])) {
                        $canTerminate = true;
                    }
                }
                if (isset($securityticket)) {
                    $type = 'security';
                    $id = $securityticket->id;
                    if (in_array($securityticket->status, [4, 5])) {
                        $canTerminate = true;
                    }
                }
                
            @endphp
            @if ($canRevise)
                <button type="button" class="btn btn-danger mr-2"
                    onclick="doRevisePO('{{ $type }}',{{ $id }})">Revisi PO</button>
            @endif
            @if ($canTerminate)
            @endif
        </div>
    </div>
    <form action="" id="submitform" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="updated_at" value="{{ $po->updated_at }}">
        <div></div>
    </form>
    <form action="/uploadinternalsignedfile" method="post" enctype="multipart/form-data" id="uploadsignedform">
        @method('patch')
        @csrf
        <div class="input_field"></div>
    </form>

    <form action="/confirmposigned" method="post" enctype="multipart/form-data" id="confirmsignedform">
        @method('patch')
        @csrf
        <input type="hidden" name="po_id">
    </form>

    <div class="modal fade" id="sendEmailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="/sendemail" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="po_id">
                    <div class="modal-header table-info">
                        <h5 class="modal-title">Kirim Ulang</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5>NOMOR PO SAP : <span class="no_sap">no_sap</span></h5>
                        <div class="form-group">
                            <label class="required_field">Email Subject</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $mail_subject }}"
                                placeholder="SUBJECT PO" name="mail_subject" required>
                        </div>
                        <div class="form-group">
                            <label class="required_field">Email To</label>
                            <textarea class="form-control form-control-sm" rows="5" name="email" required>{{ $toEmail }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="optional_field">Email Cc</label>
                            <textarea class="form-control form-control-sm" rows="5" name="cc">{{ $cc }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="required_field">Email Text</label>
                            <textarea class="form-control form-control-sm" name="email_text" rows="6">{{ $po->email_template() }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-info">Kirim Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectsignedpo" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header table-danger">
                    <h5 class="modal-title">Reject PO (<span class="no_po_sap"></span>) External Signed</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/rejectposigned" method="post">
                    @csrf
                    @method('patch')
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="required_field">Alasan penolakan</label>
                            <textarea class="form-control" name="reason" rows="3" required></textarea>
                        </div>
                        <div class="text-danger font-weight-bold">* Link baru untuk perbaikan data akan dikirimkan ke email
                            yang di input sebelumnya beserta dengan alasan</div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="po_id">
                        <input type="hidden" name="po_upload_request_id">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @isset($armadaticket)
        <div class="modal fade" id="terminateTicketingModal" data-static="backdrop" tabindex="-1" role="dialog"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">Batalkan Pengadaan Armada</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="/terminateArmadaTicket" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="required_field">Alasan Pembatalan</label>
                                <textarea class="form-control" name="cancel_notes" style="resize: none" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="optional_field">Email Vendor</label>
                                <input type="email" class="form-control" name="email_vendor" placeholder="Email Vendor">
                                <small class="form-text text-warning">* Masukan email vendor untuk memberikan notifikasi
                                    pembatalan</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Batalkan Pengadaan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endisset

    @isset($securityticket)
        <div class="modal fade" id="terminateTicketingModal" data-static="backdrop" tabindex="-1" role="dialog"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">Batalkan Pengadaan Security</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="/terminateSecurityTicket" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="security_ticket_id" value="{{ $securityticket->id }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="required_field">Alasan Pembatalan</label>
                                <textarea class="form-control" name="cancel_notes" style="resize: none" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="optional_field">Email Vendor</label>
                                <input type="email" class="form-control" name="email_vendor" placeholder="Email Vendor">
                                <small class="form-text text-warning">* Masukan email vendor untuk memberikan notifikasi
                                    pembatalan</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Batalkan Pengadaan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endisset

    <div class="modal fade" id="reminderUpdateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Reminder PO <span class="po_number"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/po/{{ $code }}/reminderupdate" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="po_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Gunakan Reminder ?</label>
                                    <select class="form-control" name="has_reminder">
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update Reminder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $("#ppn_check,#ppn_percentage").change(function() {
                let has_ppn = $("#ppn_check").prop('checked');
                let ppn_percentage = $("#ppn_percentage").val();
                let subtotal = $("#subtotal").text();
                console.log(subtotal);
                subtotal = AutoNumeric.unformat(subtotal, autonum_setting);
                if (has_ppn) {
                    $("#ppn_percentage").prop('disabled', false);
                    let ppn_value = parseFloat(ppn_percentage) / 100 * parseFloat(subtotal);
                    $('#ppn_value').text(AutoNumeric.format(ppn_value, autonum_setting));
                    $('#total').text(AutoNumeric.format(parseFloat(subtotal) + ppn_value, autonum_setting));
                } else {
                    $("#ppn_percentage").prop('disabled', true);
                    $('#ppn_value').text('-');
                    $('#total').text(AutoNumeric.format(parseFloat(subtotal), autonum_setting));
                }
            });
            $('.authorization_select2').change(function() {
                let field = $(this).closest('.box').find('.authorization_select_field');
                let selected_option = $(this).find('option:selected').data('list');
                if (selected_option == undefined) {
                    field.find('.name1').text('\xa0');
                    field.find('.position1').text('\xa0');
                    field.find('.name2').text('\xa0');
                    field.find('.position2').text('\xa0');
                } else {
                    for (let i = 0; i < selected_option.length; i++) {
                        field.find('.name' + (i + 1)).text(selected_option[i].name);
                        field.find('.position' + (i + 1)).text(selected_option[i].position);
                    }
                }
            });
            $('.validatefilesize').change(function(event) {
                if (!validatefilesize(event)) {
                    $(this).val('');
                }
            });
            $('#reminder_start_select').change(function(event) {
                let date = $(this).val();
                $('#reminder_end_select').prop('min', date);
                $('#reminder_end_select').val("");
            });
            $('#cetak_all_po_button').click(function() {
                let urls = [];
                $('.cetak_button').each(function() {
                    urls.push($(this).data('url'));
                });
                console.log(urls);
                for (let i = 0; i < urls.length; i++) {
                    window.open(urls[i]);
                };
            });
            $('.needVendorConfirmation_check').change(function() {
                $(this).closest('.po_field').find(".needVendorConfirmation_hidden").val(($(this).prop(
                    'checked')) ? 1 : 0);
            });
            $('.needVendorConfirmation_check').trigger('change');
        });

        function revisedata(po_number) {
            $('#submitform').prop('action', '/revisepodata');
            $('#submitform div').empty();
            $('#submitform div').append('<input type="hidden" name="po_number" value="' + po_number + '">');
            $('#submitform').submit();
        }

        function uploadfile(el) {
            $(el).closest('form').prop('action', '/uploadinternalsignedfile');
            $(el).closest('form').prop('method', 'POST');
            $(el).closest('form').find('input[name="_method"]').val('PATCH');
            $(el).closest('form').find('button[type="submit"]').trigger('click');
        }

        function confirm(po_id) {
            $('#confirmsignedform').find('input[name="po_id"]').val(po_id);
            $('#confirmsignedform').submit();
        }

        function reject(po_id, no_po_sap, po_upload_request_id) {
            $('#rejectsignedpo textarea[name="reason"]').val('');
            $('#rejectsignedpo input[name="reason"]').val('');
            $('#rejectsignedpo .no_po_sap').text(no_po_sap);
            $('#rejectsignedpo input[name="po_id"]').val(po_id);
            $('#rejectsignedpo input[name="po_upload_request_id"]').val(po_upload_request_id);
            $('#rejectsignedpo').modal('show');
        }

        function send_email(po_id, no_sap, el) {
            $('#sendEmailModal input[name="po_id"]').val(po_id);
            $('#sendEmailModal .no_sap').text(no_sap);
            $("#sendEmailModal input[name='mail_subject']").val($(el).data('subject'));
            $("#sendEmailModal textarea[name='email']").val($(el).data('to'));
            $("#sendEmailModal textarea[name='cc']").val($(el).data('cc'));
            $("#sendEmailModal textarea[name='email_text']").val($(el).data('text'));
            $('#sendEmailModal').modal('show');
        }

        function doTerminateTicketing() {
            $('#terminateTicketingModal').modal('show');
        }

        function doRevisePO(type, id) {
            var reason = prompt("Masukan alasan Revisi");

            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Revisi Harus diisi");
                    return;
                }
                $('#submitform').prop('action', '/revisePO');
                $('#submitform div').empty();
                $('#submitform div').append('<input type="hidden" name="type" value="' + type + '">');
                $('#submitform div').append('<input type="hidden" name="id" value="' + id + '">');
                $('#submitform div').append('<input type="hidden" name="revise_notes" value="' + reason + '">');
                $('#submitform').submit();
            }
        }

        function reminderUpdate(po_id, no_po_sap, start_date, end_date) {
            $('#reminderUpdateModal input[name="po_id"]').val(po_id);
            $('#reminderUpdateModal .po_number').text(no_po_sap);

            $('#reminderUpdateModal input[name="start_date"]').val(start_date);
            $('#reminderUpdateModal input[name="end_date"]').val(end_date);
            $('#reminderUpdateModal input[name="start_date"]').data("last_date", start_date);
            $('#reminderUpdateModal input[name="end_date"]').data("last_date", end_date);

            if (start_date && end_date) {
                $('#reminderUpdateModal select[name="has_reminder"]').val("1");
            } else {
                $('#reminderUpdateModal select[name="has_reminder"]').val("0");
            }
            $('#reminderUpdateModal').modal('show');
            $('#reminderUpdateModal select[name="has_reminder"]').trigger('change');
        }

        $('#reminderUpdateModal select[name="has_reminder"]').change(function() {
            if ($(this).val() == "1") {
                $('#reminderUpdateModal input[name="start_date"]').prop('disabled', false);
                $('#reminderUpdateModal input[name="end_date"]').prop('disabled', false);
                $('#reminderUpdateModal input[name="start_date"]').val($(
                    '#reminderUpdateModal input[name="start_date"]').data('last_date'));
                $('#reminderUpdateModal input[name="end_date"]').val($(
                    '#reminderUpdateModal input[name="end_date"]').data('last_date'));
            } else {
                $('#reminderUpdateModal input[name="start_date"]').prop('disabled', true);
                $('#reminderUpdateModal input[name="end_date"]').prop('disabled', true);
                $('#reminderUpdateModal input[name="start_date"]').val('');
                $('#reminderUpdateModal input[name="end_date"]').val('');
            }
        });

        $('.po_field select[name="has_reminder"]').change(function() {
            let closest_po_field = $(this).closest('.po_field');
            if ($(this).val() == "1") {
                closest_po_field.find('input[name="start_date"]').prop('disabled', false);
                closest_po_field.find('input[name="end_date"]').prop('disabled', false);
            } else {
                closest_po_field.find('input[name="start_date"]').prop('disabled', true);
                closest_po_field.find('input[name="end_date"]').prop('disabled', true);
            }
            closest_po_field.find('input[name="start_date"]').val('');
            closest_po_field.find('input[name="end_date"]').val('');
        });

        $('#cetak_all_po_button').click(function() {
            let urls = [];
            $('.cetak_button').each(function() {
                urls.push($(this).data('url'));
            });
            for (let i = 0; i < urls.length; i++) {
                window.open(urls[i]);
            };
        });
    </script>
@endsection
