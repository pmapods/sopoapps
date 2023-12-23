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
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        Pengadaan Armada @isset($armadaticket)
                            ({{ $armadaticket->code }})
                        @endisset
                    </h1>
                    @if ($armadaticket->status == -1)
                        <h5 class="text-danger">( Alasan Pembatalan : {{ $armadaticket->termination_reason }})</h5>
                    @endif
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Pengadaan</li>
                        <li class="breadcrumb-item active">Pengadaan Armada @isset($armadaticket)
                                ({{ $armadaticket->code }})
                            @endisset
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="row">
            @if ($armadaticket->budget_upload != null)
                <div class="col-md-12">
                    <button type="button" class="btn btn-warning float-right" id="oldbudget_button" data-toggle="modal"
                        data-target="#oldbudget_modal">
                        Tampilkan Budget Aktif
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="oldbudget_modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $armadaticket->budget_upload->code }}</h5>
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
                                                            <td class="status">{{ $armadaticket->budget_upload->status() }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="font-weight-bold">Periode</td>
                                                            <td class="period">
                                                                {{ $armadaticket->budget_upload->created_at->translatedFormat('F Y') }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="font-weight-bold">Tahun</td>
                                                            <td class="year">
                                                                {{ $armadaticket->budget_upload->year }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-12 d-flex flex-column">
                                                <table class="table table-bordered list_table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Tipe Armada</th>
                                                            <th>Kode Vendor</th>
                                                            <th>Nama Vendor</th>
                                                            <th>Qty</th>
                                                            <th>Value</th>
                                                            <th>Amount</th>
                                                            <th>Pending</th>
                                                            <th>Terpakai</th>
                                                            <th>Sisa</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($armadaticket->budget_upload->budget_detail as $detail)
                                                            <tr>
                                                                <td>{{ $detail->armada_type_name }}</td>
                                                                <td>{{ $detail->vendor_code }}</td>
                                                                <td>{{ $detail->vendor_name }}</td>
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
            @endif
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" class="form-control" value="{{ $armadaticket->created_at->format('Y-m-d') }}"
                        readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Setup</label>
                    <input type="date" class="form-control requirement_date"
                        value="{{ $armadaticket->requirement_date }}" @if ($armadaticket->status > 0) readonly @endif>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>SalesPoint</label>
                    <input type="text" class="form-control" value="{{ $armadaticket->salespoint->name }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Jenis Armada</label>
                    <input type="text" class="form-control" value="{{ $armadaticket->isNiaga ? 'Niaga' : 'Non Niaga' }}"
                        readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Jenis Pengadaan</label>
                    <input type="text" class="form-control" value="{{ $armadaticket->type() }}" readonly>
                </div>
            </div>
            @if ($armadaticket->ticketing_type == 0)
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <input type="text" class="form-control"
                            value="{{ $armadaticket->armada_type->name }} {{ $armadaticket->armada_type->brand_name }}"
                            readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jenis Budget</label>
                        <input type="text" class="form-control"
                            value="{{ $armadaticket->isBudget ? 'Budget' : 'Non Budget' }}" readonly>
                    </div>
                </div>
            @endif

            @if (in_array($armadaticket->ticketing_type, [1, 2, 3, 4]))
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Pilihan PO</label>
                        @php
                            $po = \App\Models\Po::where('no_po_sap', $armadaticket->po_reference_number)->first();
                            $po_manual = \App\Models\PoManual::where('po_number', $armadaticket->po_reference_number)->first();
                            $po_end_date = '';
                            if (isset($po)) {
                                $po_end_date = $po->end_date;
                            }
                            if (isset($po_manual)) {
                                $po_end_date = $po_manual->end_date;
                            }
                        @endphp
                        <input type="text" class="form-control"
                            value="{{ $armadaticket->po_reference_number }} (End Date : {{ $po_end_date }})" readonly>
                    </div>
                </div>
            @endif
            <div class="col-md-4">
                <div class="form-group">
                    <label for="vendor_recommendation_name">Rekomendasi Vendor</label>
                    <input type="text" id="vendor_recommendation_name" class="form-control" readonly
                        value="{{ $armadaticket->vendor_recommendation_name }}">
                </div>
            </div>
            @php
                $mutation_salespoint_id = $armadaticket->mutation_salespoint_id != null ? $armadaticket->mutation_salespoint_id : -1;
                $mutation_salespoint = \App\Models\SalesPoint::find($mutation_salespoint_id);
            @endphp
            @if ($mutation_salespoint)
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Mutasi Perpanjangan</label>
                        <input type="text" class="form-control" readonly value="{{ $mutation_salespoint->name }}">
                    </div>
                </div>
            @else
                @if (Auth::user()->id == 1 && $armadaticket->status == 6)
                    {{-- superadmin only --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info form-control" onclick="setMutasiLocation()">Set
                                Mutasi Perpanjangan</button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
        <div class="row">
            <div class="col-md-2">
                <b>PR</b>
                @if ($armadaticket->pr)
                    <div><a onclick="window.open('/printPR/{{ $armadaticket->code }}')">Tampilkan PR Manual</a></div>
                @else
                    <div>-</div>
                @endif
            </div>
            <div class="col-md-4">
                <b>PO</b>
                @if ($armadaticket->po->count() < 1)
                    <div>-</div>
                @endif
                <div>
                    @foreach ($armadaticket->po as $po)
                        @if ($po->external_signed_filepath != null || $po->internal_signed_filepath != null)
                            <a href='#'
                                onclick='window.open("/storage/{{ $po->external_signed_filepath ?? $po->internal_signed_filepath }}")'>
                                {{ $po->no_po_sap }} (last updated at : {{ $po->updated_at->format('d-m-Y H:i:s') }})
                            </a><br>
                        @endif
                    @endforeach
                </div>

                {{-- <div class="d-flex">
                <button type="button"
                    onclick="issuePO()"
                    class="btn btn-warning btn-sm">Laporkan Kesalahan PO</button>
            </div> --}}
            </div>
            {{-- <div class="col-md-4">
            <b>Issue PO</b>
            @php
                $issued_po = [];
                foreach($armadaticket->po as $po) {
                    if($po->issue){
                        array_push($issued_po,$po->issue);
                    }
                }
                $issued_po = collect($issued_po);
            @endphp
            @if ($issued_po->count() < 1)
                <div>-</div>
            @endif
            <div>
                @foreach ($issued_po as $issue)
                    <a onclick='window.open("/storage/{{$issue->ba_file}}")'>
                        BA ISSUE PO {{ $issue->po_number }} (issued on : {{ $po->created_at->format('d-m-Y') }})
                    </a>
                    <br>
                @endforeach
            </div>
        </div> --}}
        </div>
        <hr>
        <div class="row">
            @php
                $isRequirementFinished = true;
            @endphp
            @if ($armadaticket->isNiaga == false && $armadaticket->ticketing_type == 0)
                <div class="col-md-6">
                    @php
                        if (($armadaticket->facility_form->status ?? -1) != 1) {
                            $isRequirementFinished = false;
                        }
                    @endphp
                    @include('Operational.Armada.formfasilitas')
                </div>
            @endif
            @if ($armadaticket->ticketing_type == 1 || $armadaticket->ticketing_type == 4)
                <div class="col-md-6">
                    @include('Operational.Armada.formperpanjanganperhentian')
                </div>
            @endif
            @if ($armadaticket->ticketing_type == 2)
                <div class="col-md-6">
                    @include('Operational.Armada.formmutasi')
                </div>
            @endif
            @if ($available_armadas->count() > 0 && $armadaticket->ticketing_type == 0 && in_array($armadaticket->status, [-1, 7]))
                @php
                    $isRequirementFinished = false;
                @endphp
                <div class="col-md-6">
                    @include('Operational.Armada.availablearmadas')
                </div>
            @endif
            @if ($armadaticket->status == 5)
                <div class="col-md-6 pl-3">
                    @if (in_array($armadaticket->type(), [
                            'Pengadaan',
                            'Replace',
                            'Renewal',
                            'Mutasi',
                            'Percepatan Replace',
                            'Percepatan Renewal',
                            'Percepatan End Kontrak',
                            'Percepatan Replace/Renewal/Stop Sewa',
                        ]))
                        <h5>Upload Dokumen Serah Terima</h5>
                    @elseif($armadaticket->type() == 'Perpanjangan')
                        <h5>Verifikasi PO</h5>
                    @else
                        <h5>Upload Dokumen Penyerahan</h5>
                    @endif
                    @if (($armadaticket->po->first()->status ?? -1) == 3 || $armadaticket->status == 5)
                        @php
                            if ($armadaticket->type() == 'Perpanjangan') {
                                $action = '/verifyPO';
                            } else {
                                $action = '/uploadbastk';
                            }
                        @endphp
                        <form action="{{ $action }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
                            @if (in_array($armadaticket->type(), [
                                    'Pengadaan',
                                    'Replace',
                                    'Renewal',
                                    'Mutasi',
                                    'End Kontrak',
                                    'Percepatan Replace',
                                    'Percepatan Renewal',
                                    'Percepatan End Kontrak',
                                    'Percepatan Replace/Renewal/Stop Sewa',
                                ]))
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required_field">Pilih File BASTK lengkap dengan ttd</label>
                                            <input type="file" class="form-control-file validatefilesize"
                                                name="bastk_file" accept="image/*,application/pdf" required>
                                            <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (in_array($armadaticket->type(), ['Replace', 'Renewal', 'Percepatan Replace', 'Percepatan Renewal']))
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Armada Lama</label>
                                            <input type="text" class="form-control"
                                                value="{{ $po->armada_ticket->armada_type->brand_name ?? $pomanual->armada_brand_name }} {{ $po->armada_ticket->armada_type->name ?? $pomanual->armada_name }}"
                                                readonly>
                                            <small class="text-danger">*Armada lama akan terhapus dari sistem secara
                                                otomatis</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Nopol Lama</label>
                                            <input type="text" class="form-control"
                                                value="{{ $po->armada_ticket->armada->plate ?? $pomanual->plate() }}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (in_array($armadaticket->type(), ['Pengadaan', 'Renewal', 'Replace', 'Percepatan Replace', 'Percepatan Renewal']))
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Tipe Armada</label>
                                            <input type="text" class="form-control"
                                                value="{{ $armadaticket->armada_type->brand_name }} {{ $armadaticket->armada_type->name }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="required_field">Nopol</label>
                                            <input type="text" class="form-control" name="plate"
                                                placeholder="Masukan Nopol"
                                                @if ($armadaticket->type() == 'Perpanjangan') readonly
                                            value="{{ $armadaticket->perpanjangan_form->nopol }}"
                                        @else
                                            required @endif>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label class="required_field">Tahun Kendaraan</label>
                                            <input type="number" class="form-control autonumber" min="1900"
                                                max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                                name="vehicle_year" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="required_field">Unit</label>
                                            <select class="form-control" name="type" required>
                                                <option value="">-- Pilih Unit --</option>
                                                <option value="gs">GS</option>
                                                <option value="gt">GT</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="optional_field" for="booked_by">Di Booking Oleh</label>
                                            <input type="text" class="form-control" name="booked_by" id="booked_by"
                                                placeholder="Masukan Nama"
                                                @if ($armadaticket->type() == 'Perpanjangan') value="{{ $armadaticket->po_reference->armada_ticket->armada->booked_by }}" @endif>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($armadaticket->type() == 'Mutasi')
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label>Tipe Unit</label>
                                            <input type="text" class="form-control"
                                                value="{{ $armadaticket->armada_type->brand_name }} {{ $armadaticket->armada_type->name }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="required_field">Nopol</label>
                                            <input type="text" class="form-control" name="plate"
                                                placeholder="Masukan Nopol" readonly
                                                value="{{ $armadaticket->mutasi_form->nopol }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="required_field">Salespoint Awal</label>
                                            <input type="text" class="form-control" name="sender_salespoint_name"
                                                placeholder="Masukan Nopol" readonly
                                                value="{{ $armadaticket->mutasi_form->sender_salespoint_name }}">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="required_field">Salespoint Tujuan</label>
                                            <input type="text" class="form-control" name="receiver_salespoint_name"
                                                placeholder="Masukan Nopol" readonly
                                                value="{{ $armadaticket->mutasi_form->receiver_salespoint_name }}">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($armadaticket->type() == 'Perpanjangan')
                                <button type="submit" class="btn btn-primary mt-3">Verifikasi PO</button>
                            @else
                                <button type="submit" class="btn btn-primary mt-3">Submit Dokumen Penerimaan</button>
                            @endif
                        </form>

                        @if (
                            $armadaticket->type() == 'Replace' ||
                                ($armadaticket->type() == 'Percepatan Replace' &&
                                    $armadaticket->perpanjangan_form->stopsewa_reason == 'replace'))
                            @if ($armadaticket->bastk_replace_filepath == null)
                                <form action="/uploadoldbastk/{{ $armadaticket->id }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <br><br>
                                    <h5>Menunggu Upload Berkas Penyerahan (Replace dan Percepatan Replace)</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="required_field">Pilih File BASTK Lama lengkap dengan
                                                    ttd</label>
                                                <input type="file" class="form-control-file validatefilesize"
                                                    name="bastk_old_file" accept="image/*,application/pdf" required>
                                                <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3">Submit BASTK Lama</button>
                                </form>
                            @endif
                        @endif

                        @if ($armadaticket->bastk_replace_filepath)
                            <br><br>
                            <h5>Dokumen Penyerahan (BASTK Lama) <span class="text-success">(Selesai)</span></h5>
                            <div class="row">
                                <div class="col-md-1">
                                    <b>BASTK</b>
                                </div>
                                <div class="col-md-11 d-flex flex-row">
                                    <span>: </span>

                                    <a class="text-primary font-weight-bold ml-1" style="cursor: pointer;"
                                        onclick='window.open("/storage/{{ $armadaticket->bastk_replace_filepath }}")'>Tampilkan
                                        BASTK</a>

                                    <a class="text-info font-weight-bold ml-2" style="cursor: pointer;"
                                        onclick="$('#reviseOldBASTKmodal').modal('show');">Revisi BASTK</a>

                                    <div class="modal fade" id="reviseOldBASTKmodal" tabindex="-1" role="dialog"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Revisi BASTK Lama</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="/reviseoldbastk" method="post"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="armada_ticket_id"
                                                        value="{{ $armadaticket->id }}">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="file_old_bastk">Pilih File BASTK Lama</label>
                                                            <input type="file" class="form-control-file"
                                                                name="file_old_bastk" id='file_old_bastk' required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-primary">Upload</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @endif
                </div>
            @endif

            @if ($armadaticket->status == 6)
                <div class="col-md-6 pl-3">
                    <h5>Dokumen Penerimaan <span class="text-success">(Selesai)</span></h5>
                    <div class="row">
                        @if ($armadaticket->type() != 'Perpanjangan')
                            <div class="col-md-1">
                                <b>BASTK</b>
                            </div>
                            <div class="col-md-11 d-flex flex-row">
                                <span>: </span>

                                <a class="text-primary font-weight-bold ml-1" style="cursor: pointer;"
                                    onclick='window.open("/storage/{{ $armadaticket->bastk_path }}")'>Tampilkan BASTK</a>

                                <a class="text-info font-weight-bold ml-2" style="cursor: pointer;"
                                    onclick="$('#reviseBASTKmodal').modal('show');">Revisi BASTK</a>

                                <div class="modal fade" id="reviseBASTKmodal" tabindex="-1" role="dialog"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Revisi BASTK</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="/revisebastk" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="armada_ticket_id"
                                                    value="{{ $armadaticket->id }}">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="file_bastk">Pilih File BASTK</label>
                                                        <input type="file" class="form-control-file" name="file_bastk"
                                                            id='file_bastk' required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-primary">Upload</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if (
                        $armadaticket->type() == 'Replace' ||
                            ($armadaticket->type() == 'Percepatan Replace' &&
                                $armadaticket->perpanjangan_form->stopsewa_reason == 'replace'))
                        @if ($armadaticket->bastk_replace_filepath == null)
                            <form action="/uploadoldbastk/{{ $armadaticket->id }}" method="post"
                                enctype="multipart/form-data">
                                @csrf

                                <br><br>
                                <h5>Menunggu Upload Berkas Penyerahan (Replace dan Percepatan Replace)</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required_field">Pilih File BASTK Lama lengkap dengan
                                                ttd</label>
                                            <input type="file" class="form-control-file validatefilesize"
                                                name="bastk_old_file" accept="image/*,application/pdf" required>
                                            <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Submit BASTK Lama</button>
                            </form>
                        @endif
                    @endif

                    @if ($armadaticket->bastk_replace_filepath)
                        <br><br>
                        <h5>Dokumen Penyerahan (BASTK Lama) <span class="text-success">(Selesai)</span></h5>
                        <div class="row">
                            <div class="col-md-1">
                                <b>BASTK</b>
                            </div>
                            <div class="col-md-11 d-flex flex-row">
                                <span>: </span>

                                <a class="text-primary font-weight-bold ml-1" style="cursor: pointer;"
                                    onclick='window.open("/storage/{{ $armadaticket->bastk_replace_filepath }}")'>Tampilkan
                                    BASTK</a>

                                <a class="text-info font-weight-bold ml-2" style="cursor: pointer;"
                                    onclick="$('#reviseOldBASTKmodal').modal('show');">Revisi BASTK</a>

                                <div class="modal fade" id="reviseOldBASTKmodal" tabindex="-1" role="dialog"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Revisi BASTK Lama</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="/reviseoldbastk" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="armada_ticket_id"
                                                    value="{{ $armadaticket->id }}">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="file_old_bastk">Pilih File BASTK Lama</label>
                                                        <input type="file" class="form-control-file"
                                                            name="file_old_bastk" id='file_old_bastk' required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-primary">Upload</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (in_array($armadaticket->type(), ['Pengadaan', 'Replace', 'Renewal', 'Percepatan Replace', 'Percepatan Renewal']))
                        @if ($armadaticket->gt_plate == null)
                            <form action="/uploadbastkgt" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required_field">Pilih File BASTK GT lengkap dengan ttd</label>
                                            <input type="file" class="form-control-file validatefilesize"
                                                name="bastk_file" accept="image/*,application/pdf" required>
                                            <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="required_field">Nomor Kendaraan GS</label>
                                            <input type="text" class="form-control"
                                                value="{{ $armadaticket->gs_plate }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label class="required_field">Nomor Kendaraan GT</label>
                                            <input type="text" class="form-control"
                                                placeholder="Masukkan Nomor Kendaraan GT" name="gt_plate" required>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label class="required_field">Tahun Kendaraan</label>
                                            <input type="number" class="form-control autonumber" min="1900"
                                                max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                                name="vehicle_year" required>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <button type="submit" class="btn btn-primary">Submit GT</button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nomor Kendaraan GT</label>
                                        <input type="text" class="form-control" value="{{ $armadaticket->gt_plate }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Booked By</label>
                                        <input type="text" class="form-control"
                                            value="{{ \App\Models\Armada::where('plate', $armadaticket->gt_plate)->first()->booked_by }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
        @if ($armadaticket->ticketing_type == 0 && $armadaticket->isNiaga == true)
            <div class="row mt-3">
                <div class="col-md-12 d-flex flex-row justify-content-center align-items-center">
                    @foreach ($armadaticket->authorizations as $authorization)
                        <div class="d-flex text-center flex-column mr-3">
                            <div class="font-weight-bold">{{ $authorization->as }}</div>
                            @if (($armadaticket->current_authorization()->id ?? -1) == $authorization->id && $armadaticket->status > 0)
                                <div class="text-warning">Pending</div>
                            @endif

                            @if ($authorization->status == -1)
                                <div class="text-danger d-flex flex-column">
                                    <span>Reject {{ $authorization->updated_at->format('Y-m-d (H:i)') }}</span>
                                    <span>Alasan : {{ $authorization->reject_notes }}</span>

                                </div>
                            @endif

                            @if ($authorization->status == 1)
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
            @if ($armadaticket->status == 0)
                <div class="text-center mt-3 d-flex flex-row justify-content-center">
                    <button type="button" class="btn btn-primary mr-2"
                        @if (!$isRequirementFinished) disabled
                @else
                    onclick="startAuthorization('{{ $armadaticket->id }}', '{{ $armadaticket->updated_at }}')" @endif>Mulai
                        Approval</button>
                    <button type="button" class="btn btn-danger mr-2"
                        onclick="terminateTicketing('{{ $armadaticket->id }}', '{{ $armadaticket->updated_at }}')">Batalkan
                        Pengadaan</button>
                </div>
                {{-- <div class="text-danger small text-center mt-1">*approval dapat dimulai setelah melengkapi kelengkapan</div> --}}
            @endif
            @if ($armadaticket->status == 1 && ($armadaticket->current_authorization()->employee_id ?? -1) == Auth::user()->id)
                <div class="text-center mt-3 d-flex flex-row justify-content-center">
                    <button type="button" class="btn btn-success mr-2"
                        onclick="approveAuthorization('{{ $armadaticket->id }}')">Approve</button>
                    <button type="button" class="btn btn-danger mr-2"
                        onclick="rejectAuthorization('{{ $armadaticket->id }}')">Reject</button>
                </div>
            @endif
        @endif

        @if (
            (Auth::user()->id == 1 && $armadaticket->status != -1 && $armadaticket->status != 6) ||
                (Auth::user()->id == 115 && $armadaticket->status != -1 && $armadaticket->status != 6) ||
                (Auth::user()->id == 116 && $armadaticket->status != -1 && $armadaticket->status != 6) ||
                (Auth::user()->id == 117 && $armadaticket->status != -1 && $armadaticket->status != 6) ||
                (Auth::user()->id == 197 && $armadaticket->status != -1 && $armadaticket->status != 6) ||
                (Auth::user()->id == 118 && $armadaticket->status != -1 && $armadaticket->status != 6) ||
                (Auth::user()->id == 717 && $armadaticket->status != -1 && $armadaticket->status != 6))
            <center class="mt-2">
                <button type="button" class="btn btn-danger mr-2"
                    onclick="terminateTicketing('{{ $armadaticket->id }}', '{{ $armadaticket->updated_at }}')">Batalkan
                    Pengadaan (Superadmin & Purchasing Only)</button>
            </center>
        @endif
    </div>
    <form method="post" id="submitform">
        @csrf
        <div></div>
    </form>

    <div class="modal fade" id="mutasiLocationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Set Mutasi Perpanjangan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/armadaticketing/{{ $armadaticket->code }}/setMutasiLocation" method="post">
                    @csrf
                    <input type="hidden" name="armada_ticket_id" value="{{ $armadaticket->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Salespoint Awal</label>
                                    <input type="text" class="form-control" readonly name="source_salespoint_id"
                                        value="{{ $armadaticket->salespoint->name }}">
                                </div>
                            </div>
                            @php
                                $salespoints = \App\Models\SalesPoint::where('id', '!=', $armadaticket->salespoint_id)->get();
                            @endphp
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="required_field">Salespoint Tujuan</label>
                                    <select class="form-control select2" name="to_salespoint_id" required>
                                        <option value="">-- Pilih Salespoint --</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('.validatefilesize').change(function(event) {
                if (!validatefilesize(event)) {
                    $(this).val('');
                }
            });
            $('.autonumber').change(function(event) {
                autonumber($(this));
            });
        });

        function startAuthorization(armada_ticket_id, updated_at) {
            $('#submitform').prop('action', '/startarmadaauthorization');
            $('#submitform').prop('method', 'POST');
            $('#submitform').find('div').append('<input type="hidden" name="requirement_date" value="' + $(
                '.requirement_date').val() + '">');
            $('#submitform').find('div').append('<input type="hidden" name="armada_ticket_id" value="' + armada_ticket_id +
                '">');
            $('#submitform').find('div').append('<input type="hidden" name="updated_at" value="' + updated_at + '">');
            $('#submitform').submit();
        }

        function terminateTicketing(armada_ticket_id, updated_at) {
            var reason = prompt("Masukkan alasan pembatalan");
            $('#submitform').find('div').empty();
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return
                }
                $('#submitform').prop('action', '/terminateArmadaTicket');
                $('#submitform').prop('method', 'POST');
                $('#submitform').find('div').append('<input type="hidden" name="armada_ticket_id" value="' +
                    armada_ticket_id + '">');
                $('#submitform').find('div').append('<input type="hidden" name="updated_at" value="' + updated_at + '">');
                $('#submitform').find('div').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#submitform').submit();
            }
        }

        function approveAuthorization(armada_ticket_id) {
            $('#submitform').prop('action', '/approvearmadaauthorization');
            $('#submitform').find('div').append('<input type="hidden" name="armada_ticket_id" value="' + armada_ticket_id +
                '">');
            $('#submitform').prop('method', 'POST');
            $('#submitform').submit();
        }

        function rejectAuthorization(armada_ticket_id) {
            var reason = prompt("Masukan alasan Reject");
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return;
                }
                $('#submitform').prop('action', '/rejectarmadaauthorization');
                $('#submitform').find('div').append('<input type="hidden" name="armada_ticket_id" value="' +
                    armada_ticket_id + '">');
                $('#submitform').find('div').append('<input type="hidden" name="reject_notes" value="' + reason + '">');
                $('#submitform').prop('method', 'POST');
                $('#submitform').submit();
            }
        }

        function setMutasiLocation() {
            $('#mutasiLocationModal').modal('show');
            $('#mutasiLocationModal form').get(0).reset();
        }
    </script>
    @yield('mutasi-js')
    @yield('perpanjangan-js')
    @yield('fasilitas-js')
@endsection
