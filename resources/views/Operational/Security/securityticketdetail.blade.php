@extends('Layout.app')
@section('local-css')
    <style>
        .bottom_action button {
            margin-right: 1em;
        }

        .box {
            background: #FFF;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
            border: 1px solid;
            border-color: gainsboro;
            border-radius: 0.5em;
        }

        .select2-results__option--disabled {
            display: none;
        }

        .remove_attachment {
            margin-left: 2em;
            font-weight: bold;
            cursor: pointer;
            color: red;
        }

        .tdbreak {
            /* word-break : break-all; */
        }

        .other_attachments tr td:first-of-type {
            overflow-wrap: anywhere;
            max-width: 300px;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex flex-column">
                    <h1 class="m-0 text-dark">Pengadaan Security ({{ $securityticket->code }})</h1>
                    <h5><b>Status : </b>{{ $securityticket->status() }}</h5>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Pengadaan</li>
                        <li class="breadcrumb-item active">Pengadaan Security ({{ $securityticket->code }})</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
        @if ($securityticket->budget_upload != null)
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-warning float-right" id="oldbudget_button" data-toggle="modal"
                        data-target="#oldbudget_modal">
                        Tampilkan Budget Terkait
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="oldbudget_modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $securityticket->budget_upload->code }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-4">
                                                <table class="table table-borderless table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td class="font-weight-bold">Status</td>
                                                            <td class="status">
                                                                {{ $securityticket->budget_upload->status() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="font-weight-bold">Periode</td>
                                                            <td class="period">
                                                                {{ $securityticket->budget_upload->created_at->translatedFormat('F Y') }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-12 d-flex flex-column">
                                                <table class="table table-bordered list_table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Kode</th>
                                                            <th>Kategori</th>
                                                            <th>Nama</th>
                                                            <th>Qty</th>
                                                            <th>Value</th>
                                                            <th>Amount</th>
                                                            <th>Pending</th>
                                                            <th>Terpakai</th>
                                                            <th>Sisa</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($securityticket->budget_upload->budget_detail as $detail)
                                                            <tr>
                                                                <td>{{ $detail->code }}</td>
                                                                <td>{{ $detail->group }}</td>
                                                                <td>{{ $detail->name }}</td>
                                                                <td>{{ $detail->qty }}</td>
                                                                <td class="rupiah">{{ $detail->value }}</td>
                                                                <td class="rupiah">{{ $detail->qty * $detail->value }}</td>
                                                                <td>{{ $detail->pending_quota }}</td>
                                                                <td>{{ $detail->used_quota }}</td>
                                                                <td>{{ $detail->qty - $detail->pending_quota - $detail->used_quota }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" class="form-control created_date"
                        value="{{ $securityticket->created_at->format('Y-m-d') }}" readonly>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Tanggal Setup</label>
                    <input type="date" class="form-control requirement_date" name="requirement_date"
                        value="{{ $securityticket->requirement_date }}" @if ($securityticket->status > 0) readonly @endif>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>SalesPoint</label>
                    <input type="text" class="form-control" value="{{ $securityticket->salespoint->name }}" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <div class="form-group">
                    <label>Tipe Pengadaan Security</label>
                    <input class="form-control" type="text" value="{{ $securityticket->type() }}" readonly>
                </div>
            </div>
            @if ($securityticket->po_reference_number)
                <div class="col-4" id="po_field">
                    <div class="form-group">
                        <label>Pilihan PO Sebelumnya</label>
                        <input type="text" class="form-control" value="{{ $securityticket->po_reference_number }}"
                            readonly>
                    </div>
                </div>
            @endif
            @if (!empty($securityticket->personil_count))
                <div class="col-4" id="personil_count_field">
                    <div class="form-group">
                        <div class="form-group">
                            <label class="required_field">Jumlah Personil</label>
                            <div class="input-group ">
                                <input type="number" class="form-control" name="personil_count"
                                    value="{{ $securityticket->personil_count }}" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">Personil</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @if (isset($securityticket->reason))
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Alasan Pengadaan</label>
                        <textarea class="form-control" rows="3" style="resize: none;" disabled>{{ $securityticket->reason }}</textarea>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-2">
                <b>PR</b>
                @if ($securityticket->pr)
                    <div><a onclick="window.open('/printPR/{{ $securityticket->code }}')">Tampilkan PR Manual</a></div>
                @else
                    <div>-</div>
                @endif
            </div>
            <div class="col-md-4">
                <b>PO</b>
                @if ($securityticket->po->count() < 1)
                    <div>-</div>
                @endif
                <div>
                    @foreach ($securityticket->po as $po)
                        @if ($po->external_signed_filepath != null || $po->internal_signed_filepath != null)
                            <a
                                onclick='window.open("/storage/{{ $po->external_signed_filepath ?? $po->internal_signed_filepath }}")'>
                                {{ $po->no_po_sap }} (last updated at : {{ $po->updated_at->format('d-m-Y H:i:s') }})
                            </a><br>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row mt-3">
            @php
                $isRequirementFinished = true;
                switch ($securityticket->ticketing_type) {
                    case 0:
                        $securityevaluationform = false;
                        $uploadendkontrak = false;
                        break;
                    case 1:
                        $securityevaluationform = true;
                        $uploadendkontrak = false;
                        break;
                    case 2:
                        $securityevaluationform = true;
                        $uploadendkontrak = false;
                        break;
                    case 3:
                        $securityevaluationform = true;
                        $uploadendkontrak = true;
                        break;
                    case 4:
                        $securityevaluationform = false;
                        $uploadendkontrak = false;
                        break;
                    case 5:
                        $securityevaluationform = true;
                        $uploadendkontrak = false;
                        break;
                    case 6:
                        $securityevaluationform = true;
                        $uploadendkontrak = true;
                        break;
                    default:
                        break;
                }
            @endphp
            @if ($securityevaluationform)
                {{-- new evaluasi modal --}}
                <div class="modal fade" id="newEvaluasiFormModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Form Evaluasi Baru</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                @include('Operational.Security.newformevaluasi')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-between">
                    @if ($securityticket->status == 0)
                        <a class="font-weight-bold text-primary" style="cursor: pointer" data-toggle="modal"
                            data-target="#newEvaluasiFormModal">
                            + Tambah Form Evaluasi
                        </a>
                    @else
                        <div></div>
                    @endif
                    <div>
                        <a href="#carousel" class="font-weight-bold" role="button" data-slide="prev">
                            <span>Previous</span>
                        </a>
                        <a href="#carousel" class="ml-2 font-weight-bold" role="button" data-slide="next">
                            <span>Next</span>
                        </a>
                    </div>
                </div>
                @if ($securityticket->ticketing_type == 5 || $securityticket->ticketing_type == 6)
                    <div class="col-sm-6">
                        <h5 class="m-0 font-weight-bold">
                            <a class="uploaded_file small text-primary"
                                onclick='window.open("/storage/{{ $securityticket->ba_upload_security_ticket }}")'>Tampilkan
                                BA Percepatan</a>
                        </h5>
                    </div>
                @endif
                <div id="carousel" class="carousel slide col-12" data-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($securityticket->evaluasi_form as $key => $evaluasiform)
                            <div class="carousel-item @if ($key == 0) active @endif">
                                @include('Operational.Security.formevaluasi')
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if (in_array($securityticket->type(), ['Pengadaan Lembur', 'Pengadaan', 'Replace']))
                @if ($securityticket->status == 5)
                    <div class="col-6 d-flex flex-column">
                        <form action="/uploadsecuritylpb" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="security_ticket_id" value="{{ $securityticket->id }}">
                            <h5>Upload Berkas Penerimaan Security</h5>
                            @foreach ($securityticket->po as $po)
                                <div>
                                    <b>PO {{ $po->no_po_sap }} </b> <a class="font-weight-bold" href="#"
                                        onclick="window.open('/storage/{{ $po->external_signed_filepath }}')">Tampilkan
                                        PO</a>
                                </div>
                            @endforeach
                            <div class="form-group">
                                <label class="required_field">Pilih File LPB lengkap dengan ttd</label>
                                <input type="file" class="form-control-file validatefilesize" name="lpb_file"
                                    accept="image/*,application/pdf" required>
                                <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">Upload LPB</button>
                            </div>
                        </form>
                    </div>
                @endif

                @if ($securityticket->status == 6)
                    <div class="col-6 d-flex flex-column">
                        <h5>Dokumen penerimaan</h5>
                        <div>
                            <b>Dokumen penerimaan LPB </b> <a href="#" class="font-weight-bold"
                                onclick="window.open('/storage/{{ $securityticket->lpb_path }}')">Tampilkan</a>
                        </div>
                        @foreach ($securityticket->po as $po)
                            <div>
                                <b>PO {{ $po->no_po_sap }} </b> <a class="font-weight-bold" href="#"
                                    onclick="window.open('/storage/{{ $po->external_signed_filepath }}')">Tampilkan PO</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            @if (in_array($securityticket->type(), ['End Kontrak', 'Percepatan End Kontrak']))
                @if ($securityticket->status == 5)
                    <div class="col-6 d-flex flex-column">
                        <form action="/uploadsecurityendkontrak" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="security_ticket_id" value="{{ $securityticket->id }}">
                            <h5>Surat Pemutusan Kerjasama</h5>
                            <div>
                                @if (isset($securityticket->po_reference->external_signed_filepath))
                                    <b>PO sebelumnya {{ $securityticket->po_reference_number }} </b><a
                                        class="font-weight-bold" href="#"
                                        onclick="window.open('/storage/{{ $securityticket->po_reference->external_signed_filepath }}')">Tampilkan
                                        PO</a>
                                @else
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="required_field">Pilih File End Kontrak lengkap dengan ttd</label>
                                <input type="file" class="form-control-file validatefilesize" name="endkontrak_file"
                                    accept="image/*,application/pdf" required>
                                <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">Upload Surat Pemutusan Kerjasama</button>
                            </div>
                        </form>
                    </div>
                @endif
            @endif

            @if ($securityticket->status == 6 && $securityticket->endkontrak_path != null)
                <div class="col-6 d-flex flex-column">
                    <h5>Dokumen Upload</h5>
                    <div>
                        <b>Surat Pemutusan Kerjasama </b> <a href="#" class="font-weight-bold"
                            onclick="window.open('/storage/{{ $securityticket->endkontrak_path }}')">Tampilkan</a>
                    </div>
                </div>
            @endif
        </div>
        @if (in_array($securityticket->ticketing_type, [0, 4]))
            <div class="row mt-3">
                <div class="col-12 d-flex flex-row justify-content-center align-items-center" id="authorization_field">
                    @foreach ($securityticket->authorizations as $authorization)
                        <div class="d-flex text-center flex-column mr-3">
                            <div class="font-weight-bold">{{ $authorization->as }}</div>
                            @if (($securityticket->current_authorization()->id ?? -1) == $authorization->id && $securityticket->status > 0)
                                <div class="text-warning">Pending</div>
                            @endif

                            @if ($authorization->status == -1)
                                <div class="text-danger d-flex flex-column">
                                    <span>Reject {{ $authorization->updated_at->format('Y-m-d (H:i)') }}</span>
                                    <span>Alasan : {{ $authorization->reject_notes }}</span>
                                </div>
                            @endif

                            @if ($authorization->status == 1 && $securityticket->status > 0)
                                <div class="text-success">Approved {{ $authorization->updated_at->format('Y-m-d (H:i)') }}
                                </div>
                            @endif
                            <div>{{ $authorization->employee_name }} ({{ $authorization->employee_position }})</div>
                        </div>
                        @if (!$loop->last)
                            <div class="mr-3">
                                <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if ($securityticket->status == 0)
            <div class="text-center mt-3 d-flex flex-row justify-content-center">
                @if (in_array($securityticket->ticketing_type, [0, 4]))
                    <button type="button" class="btn btn-primary mr-2"
                        @if (!$isRequirementFinished) disabled
                @else
                    onclick="startAuthorization('{{ $securityticket->id }}', '{{ $securityticket->updated_at }}')" @endif>Mulai
                        Approval</button>
                @endif
                <button type="button" class="btn btn-danger mr-2"
                    onclick="terminateTicketing('{{ $securityticket->id }}', '{{ $securityticket->updated_at }}')">Batalkan
                    Pengadaan</button>
            </div>
            <div class="text-danger small text-center mt-1">*approval dapat dimulai setelah melengkapi kelengkapan</div>
        @endif

        @if ($securityticket->status == 1 && ($securityticket->current_authorization()->employee_id ?? -1) == Auth::user()->id)
            <div class="text-center mt-3 d-flex flex-row justify-content-center">
                <button type="button" class="btn btn-success mr-2"
                    onclick="approveAuthorization('{{ $securityticket->id }}')">Approve</button>
                <button type="button" class="btn btn-danger mr-2"
                    onclick="rejectAuthorization('{{ $securityticket->id }}')">Reject</button>
            </div>
        @endif

        @if (
            (Auth::user()->id == 1 && $securityticket->status != -1 && $securityticket->status != 6) ||
                (Auth::user()->id == 115 && $securityticket->status != -1 && $securityticket->status != 6) ||
                (Auth::user()->id == 116 && $securityticket->status != -1 && $securityticket->status != 6) ||
                (Auth::user()->id == 117 && $securityticket->status != -1 && $securityticket->status != 6) ||
                (Auth::user()->id == 197 && $securityticket->status != -1 && $securityticket->status != 6) ||
                (Auth::user()->id == 118 && $securityticket->status != -1 && $securityticket->status != 6) ||
                (Auth::user()->id == 717 && $securityticket->status != -1 && $securityticket->status != 6))
            <center>
                <button type="button" class="btn btn-danger mt-3"
                    onclick="terminateTicketing('{{ $securityticket->id }}', '{{ $securityticket->updated_at }}')">Batalkan
                    Pengadaan (Superadmin & Purchasing Only)</button>
            </center>
        @endif
    </div>

    <form method="post" id="submitform">
        @csrf
        <div></div>
    </form>


@endsection
@section('local-js')
    <script>
        $(document).ready(function() {});

        function startAuthorization(security_ticket_id, updated_at) {
            $('#submitform').prop('action', '/startsecurityauthorization');
            $('#submitform').prop('method', 'POST');

            $('#submitform').find('div').append('<input type="hidden" name="requirement_date" value="' + $(
                '.requirement_date').val() + '">');
            $('#submitform').find('div').append('<input type="hidden" name="security_ticket_id" value="' +
                security_ticket_id + '">');
            $('#submitform').find('div').append('<input type="hidden" name="updated_at" value="' + updated_at + '">');
            $('#submitform').submit();
        }

        function approveAuthorization(security_ticket_id) {
            $('#submitform').prop('action', '/approvesecurityauthorization');
            $('#submitform').find('div').append('<input type="hidden" name="security_ticket_id" value="' +
                security_ticket_id + '">');
            $('#submitform').prop('method', 'POST');
            $('#submitform').submit();
        }

        function rejectAuthorization(security_ticket_id) {
            var reason = prompt("Masukan alasan Reject");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }
                $('#submitform').prop('action', '/rejectsecurityauthorization');
                $('#submitform').find('div').append('<input type="hidden" name="security_ticket_id" value="' +
                    security_ticket_id + '">');
                $('#submitform').find('div').append('<input type="hidden" name="reject_notes" value="' + reason + '">');
                $('#submitform').prop('method', 'POST');
                $('#submitform').submit();
            }
        }

        function terminateTicketing(security_ticket_id, updated_at) {
            var reason = prompt("Masukkan alasan pembatalan");
            $('#submitform').find('div').empty();
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return
                }
                $('#submitform').prop('action', '/terminatesecurityticketing');
                $('#submitform').prop('method', 'POST');
                $('#submitform').find('div').append('<input type="hidden" name="security_ticket_id" value="' +
                    security_ticket_id + '">');
                $('#submitform').find('div').append('<input type="hidden" name="updated_at" value="' + updated_at + '">');
                $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#submitform').submit();
            }
        }
    </script>

    @yield('newevaluasiform-js')
    @yield('evaluasiform-js')
@endsection
