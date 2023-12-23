@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">{{ $code }}</li>
                        <li class="breadcrumb-item active">Data Compare</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-2">
        @if (isset($armadaticket))
            <div class="row">
                <div class="col-md-6">
                    <h5>PO BARU</h5>
                    @foreach ($armadaticket->po as $po)
                        <div class="row">
                            <div class="d-flex flex-column">
                                <div class="row">
                                    <div class="col">
                                        <h4>{{ $po->sender_name }}</h4>
                                    </div>
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
                                        <label>Alamat Vendor</label>
                                        <span>{{ $po->sender_address }}</span>
                                    </div>
                                    <div class="col-6 d-flex flex-column text-right">
                                        <label>Alamat Kirim / SalesPoint</label>
                                        <span>{{ $po->send_address }}</span>
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
                                                    @php $subtotal += $po_detail->qty*$po_detail->item_price; @endphp
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
                                            <td class="rupiah text-right" id="subtotal">{{ $subtotal }}</td>
                                        </tr>
                                        @if ($po->status == -1)
                                            <tr>
                                                <td class="d-flex align-items-center">
                                                    <input type="checkbox" name="has_ppn" class="mr-1"
                                                        id="ppn_check">
                                                    <span class="mr-1">PPN</span>
                                                    <div class="input-group input-group-sm mr-1" style="width: 5em">
                                                        <input type="number" class="form-control" step="0.1" name="ppn_percentage"
                                                            id="ppn_percentage" value="11" disabled>
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
                                            <td class="font-weight-bold rupiah text-right" id="total">
                                                {{ $total }}
                                            </td>
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
                                                value="{{ $po->created_at->format('Y-m-d') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label>Pembayaran / Payment</label>
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
                                            <label>Reminder Start Date</label>
                                            <input type="date" class="form-control" name="start_date"
                                                value="{{ $po->start_date }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Reminder End Date</label>
                                            <input type="date" class="form-control" name="end_date"
                                                value="{{ $po->end_date }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="optional_field">Notes</label>
                                            <textarea class="form-control" placeholder="notes" name="notes" rows="3" maxlength="200" readonly>{{ $po->notes }}</textarea>
                                        </div>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="col-md-6 pl-4">
                    @if (isset($armadaticket->po_reference_number))
                    <div class="row d-flex flex-column border border-dark p-3 mb-2">
                        <h5>PO SEBELUMNYA</h5>
                        @php
                            $po = App\Models\Po::where('no_po_sap',$armadaticket->po_reference_number)->first();
                            $pomanual = App\Models\PoManual::where('po_number',$armadaticket->po_reference_number)->first();
                        @endphp
                        @if (isset($po))
                            <h5>{{ $po->no_po_sap }} (PO PODS)</h5>
                        @endif
                        @if (isset($pomanual))
                            <h5>{{ $pomanual->po_number }} (PO MANUAL)</h5>
                            <table class="table table-sm table-borderless small">
                                <tbody>
                                    <tr><td class="font-weight-bold">Nama Salespoint</td><td>: {{ $pomanual->salespoint_name }}</td></tr>
                                    <tr><td class="font-weight-bold">Category Name</td><td>: {{ $pomanual->category_name }}</td></tr>
                                    <tr><td class="font-weight-bold">Vendor Name</td><td>: {{ $pomanual->vendor_name }}</td></tr>
                                    <tr><td class="font-weight-bold">GS Plate</td><td>: {{ $pomanual->gs_plate }}</td></tr>
                                    <tr><td class="font-weight-bold">GT Plate</td><td>: {{ $pomanual->gt_plate }}</td></tr>
                                    <tr><td class="font-weight-bold">Tipe Niaga</td><td>: {{ ($pomanual->is_niaga) ? "Niaga" : ($pomanual->is_niaga == 0) ? "Non-Niaga" : "-" }}</td></tr>
                                    <tr><td class="font-weight-bold">Nama Armada</td><td>: {{ $pomanual->armada_name }}</td></tr>
                                    <tr><td class="font-weight-bold">Merk Armada</td><td>: {{ $pomanual->armada_brand_name }}</td></tr>
                                    <tr><td class="font-weight-bold">Qty</td><td>: {{ $pomanual->qty }}</td></tr>
                                    <tr><td class="font-weight-bold">Start Date</td><td>: {{ $pomanual->start_date }}</td></tr>
                                    <tr><td class="font-weight-bold">End Date</td><td>: {{ $pomanual->end_date }}</td></tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                    @endif
                    @if (isset($armadaticket->facility_form))
                        @include('Operational.Armada.formfasilitas')
                    @endif
                    @if (isset($armadaticket->perpanjangan_form))
                        @include('Operational.Armada.formperpanjanganperhentian')
                    @endif
                    @if (isset($armadaticket->mutasi_form))
                        @include('Operational.Armada.formmutasi')
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
@section('local-js')
    <script></script>
@endsection
