@extends('Layout.app')
@section('local-css')
    <style>
        .float {
            position: fixed;
            bottom: 25px;
            right: 40px;
            text-align: center;
            /* box-shadow: 2px 2px 3px #999; */
        }

        .floating-button {
            box-shadow: 2px 2px 3px #999;
        }

    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Armada Accident ({{ $armada_accident->salespoint->name }})</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Reporting</li>
                        <li class="breadcrumb-item active">Armada Accident</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <form action="/armadaaccident/update" method="post" enctype="multipart/form-data" id="form_field">
        @csrf
        <input type="hidden" name="armada_accident_id" value="{{ $armada_accident->id }}">
        <div class="content-body px-2">
            <div class="row row-cols-md-2">
                <div class="col mb-4">
                    <div class="card h-100 p-3">
                        <h5>Accident Information</h5>
                        <div class="row accident_information">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulan Periode</label>
                                    <select class="form-control" name="armada_accident[periode][month]">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">
                                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tahun Periode</label>
                                    <select class="form-control" name="armada_accident[periode][year]">
                                        @for ($i = 0; $i <= 10; $i++)
                                            <option value="{{ date('Y') - $i }}">{{ date('Y') - $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" name="armada_accident[description]"
                                        rows="3">{{ $armada_accident->description }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12 pb-3">
                                <label>Accident Cause</label><br>
                                @php
                                    $accident_cause_list = ['Human Error', 'Machine Error', 'Third Party Error', 'Force Of Nature'];
                                @endphp
                                @foreach ($accident_cause_list as $key => $accident_cause)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox"
                                            name="armada_accident[accident_causes][]"
                                            id="accident_cause_{{ $key }}" value="{{ $accident_cause }}">
                                        <label class="form-check-label accident_cause"
                                            for="accident_cause_{{ $key }}">{{ $accident_cause }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-12 pb-3">
                                <label>Urgensi (Head Office-K3)</label><br>
                                @php
                                    $urgency_list = ['Mendesak', 'Penting'];
                                @endphp
                                @foreach ($urgency_list as $key => $urgency)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="armada_accident[urgency][]"
                                            id="urgency_{{ $key }}" value="{{ $urgency }}">
                                        <label class="form-check-label urgency"
                                            for="urgency_{{ $key }}">{{ $urgency }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Accident Level</label>
                                    <select class="form-control" name="armada_accident[accident_level]">
                                        <option value="">-- Pilih --</option>
                                        <option value="lite">Lite</option>
                                        <option value="medium">Medium</option>
                                        <option value="heavy">Heavy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Accident Consecuence</label>
                                    <select class="form-control" name="armada_accident[accident_consecuence]">
                                        <option value="">-- Pilih --</option>
                                        <option value="tpl">TPL</option>
                                        <option value="non tpl">Non TPL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Handling Start Date</label>
                                    <input type="date" class="form-control" name="armada_accident[handling_start_date]">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Handling End Date</label>
                                    <input type="date" class="form-control" name="armada_accident[handling_end_date]">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">

                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card h-100 p-3">
                        <h5>Accident Cost</h5>
                        <div class="row accident_cost">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Perobatan Korban Pihak Luar</label>
                                    <select class="form-control" name="accident_cost[perobatan_korban]">
                                        <option value="">-- Pilih --</option>
                                        <option value="Rawat Inap">Rawat Inap</option>
                                        <option value="Rawat Jalan">Rawat Jalan</option>
                                        <option value="Rawat Inap Rawat Jalan">Rawat Inap Rawat Jalan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominal</label>
                                    <input type="text" class="form-control rupiah"
                                        name="accident_cost[nominal_perobatan_korban]">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Santunan</label>
                                    <input type="text" class="form-control" name="accident_cost[santunan]">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominal</label>
                                    <input type="text" class="form-control rupiah" name="accident_cost[nominal_santunan]">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Unit Korban</label>
                                    <select class="form-control" name="accident_cost[biaya_unit_korban]">
                                        <option value="">-- Pilih --</option>
                                        <option value="perbaikan">Perbaikan</option>
                                        <option value="penggantian">Penggantian</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominal</label>
                                    <input type="text" class="form-control rupiah"
                                        name="accident_cost[nominal_biaya_unit_korban]">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Perkara</label>
                                    <select class="form-control" name="accident_cost[biaya_perkara]">
                                        <option value="">-- Pilih --</option>
                                        <option value="Laporan">Laporan</option>
                                        <option value="Unit">Unit</option>
                                        <option value="Cabut Perkara Unit">Cabut Perkara Unit</option>
                                        <option value="Laporan Cabut Perkara Unit">Laporan Cabut Perkara Unit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominal</label>
                                    <input type="text" class="form-control rupiah"
                                        name="accident_cost[nominal_biaya_perkara]">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h5>PIC Area (CB/DP/CP)</h5>
                        <div class="row pic_area">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="pic_area[nama]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jabatan</label>
                                    <select name="pic_area[jabatan]" class="form-control">
                                        <option value="">-- Pilih salah satu --</option>
                                        <option value="BM">BM</option>
                                        <option value="SBH">SBH</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>NIK</label>
                                    <input type="text" name="pic_area[nik]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="pic_area[phone]" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card h-100 p-3">
                        <h5>Vehicle Identity</h5>
                        <div class="row vehicle_and_driver_identity">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nomor Polisi</label>
                                    <select name="vehicle_identity[nopol]" class="form-control select2">
                                        <option value="">-- Pilih Nopol Armada -- </option>
                                        @foreach ($armadas as $armada)
                                            <option value="{{ $armada->plate }}"
                                                data-jenis_sewa="{{ $armada->isNiaga }}">{{ $armada->plate }}
                                                ({{ $armada->isNiaga ? 'Niaga' : 'Non Niaga' }}{{ $armada->armada_type->brand . ' ' . $armada->armada_type->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-danger">* kendaraan yang muncul merupakan kendaraan yang
                                        terkait dengan salespoint terpilih</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Kendaraan</label>
                                    <input type="text" name="vehicle_identity[jenis_kendaraan]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cabang</label>
                                    <input type="text" name="vehicle_identity[cabang]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status STNK</label>
                                    <select name="vehicle_identity[stnk_status]" class="form-control">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="berlaku">Berlaku</option>
                                        <option value="expired">Expired</option>
                                        <option value="tidak ada">Tidak Ada</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card h-100 p-3">
                        <h5>Driver Identity</h5>
                        <div class="row vehicle_and_driver_identity">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Nama Driver</label>
                                    <input type="text" class="form-control" name="driver_identity[nama]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>NIK</label>
                                    <input type="text" class="form-control" name="driver_identity[nik]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Jabatan</label>
                                    <input type="text" class="form-control" name="driver_identity[jabatan]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="driver_identity[status]">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="tetap">Tetap</option>
                                        <option value="kontrak">Kontrak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Jenis SIM</label>
                                    <select class="form-control" name="driver_identity[jenis_sim]">
                                        <option value="">-- Pilih Jenis SIM --</option>
                                        <option value="C">C</option>
                                        <option value="A">A</option>
                                        <option value="B1">B1</option>
                                        <option value="B2">B2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Status SIM</label>
                                    <select class="form-control" name="driver_identity[sim_status]">
                                        <option value="">-- Pilih Status SIM --</option>
                                        <option value="berlaku">Berlaku</option>
                                        <option value="expired">Expired</option>
                                        <option value="tidak ada">Tidak Ada</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card p-3 h-100">
                        <h5>Legal And Insurance Aspects</h5>
                        <div class="row legal_and_insurance_aspects">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Legal Status</label>
                                    <select class="form-control" name="legal_aspect[status]">
                                        <option value="">-- Pilih --</option>
                                        <option value="closed">Closed</option>
                                        <option value="on process">On Process</option>
                                        <option value="open">Open</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Legal Remark</label>
                                    <input type="text" class="form-control" name="legal_aspect[remarks]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Insurance Conclusion</label>
                                    <select class="form-control" name="insurance_aspect[conclusion]">
                                        <option value="">-- Pilih --</option>
                                        <option value="claimable">Claimable</option>
                                        <option value="unclaimable">Unclaimable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Insurance Status</label>
                                    <select class="form-control" name="insurance_aspect[status]">
                                        <option value="">-- Pilih --</option>
                                        <option value="closed">Closed</option>
                                        <option value="on process">On Process</option>
                                        <option value="open">Open</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Insurance Start Date SLA</label>
                                    <input type="date" class="form-control" name="insurance_aspect[start_date_sla]">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Insurance End Date SLA</label>
                                    <input type="date" class="form-control" name="insurance_aspect[end_date_sla]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card p-3 h-100">
                        <h5>Recovery Accident Cost</h5>
                        <div class="row recovery_cost">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Insurance Value</label>
                                    <input type="text" class="form-control rupiah" name="recovery_cost[insurance_value]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Employee Value</label>
                                    <input type="text" class="form-control rupiah" name="recovery_cost[employee_value]">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Total Claim</label>
                                    <input type="number" class="form-control" name="armada_accident[cost_remarks]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="height:8vh"></div>
            <div class="float d-flex justify-content-end" style="width:50%">
                @if ($armada_accident->status == 0)
                    @if (((Auth::user()->menu_access->reporting ?? 0) & 2) == true)
                        <button type="button" class="btn btn-danger floating-button mr-2" onclick="closeCase()">Close
                            Case</button>
                    @endif
                    <button type="submit" class="btn btn-primary floating-button">Simpan Perubahan</button>
                @else
                    @if (((Auth::user()->menu_access->reporting ?? 0) & 2) == true)
                        <button type="button" class="btn btn-success floating-button mr-2" onclick="openCase()">Open
                            Case</button>
                    @endif
                @endif
            </div>
        </div>
    </form>

    <form action="" id="submitform" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="updated_at" value="{{ $armada_accident->updated_at }}">
        <input type="hidden" name="armada_accident_id" value="{{ $armada_accident->id }}">
        <div></div>
    </form>
@endsection
@section('local-js')
    <script>
        let armada_accident = @json($armada_accident);
        let vehicle_identity = @json($armada_accident->vehicle_identity);
        let driver_identity = @json($armada_accident->driver_identity);
        let pic_area = @json($armada_accident->pic_area);
        let accident_cost = @json($armada_accident->accident_cost);
        let legal_aspect = @json($armada_accident->legal_aspect);
        let insurance_aspect = @json($armada_accident->insurance_aspect);
        let recovery_cost = @json($armada_accident->recovery_cost);
        $(document).ready(function() {
            if (armada_accident.status == 1) {
                $('#form_field').find('input,select').prop('disabled', true);
            } else {
                $('#form_field').find('input,select').prop('disabled', false);
            }

            // Armada Accident
            $('[name="armada_accident[accident_level]"]').val(armada_accident.accident_level);
            $('[name="armada_accident[accident_consecuence]"]').val(armada_accident.accident_consecuence);
            $('[name="armada_accident[handling_start_date]"]').val(armada_accident.handling_start_date);
            $('[name="armada_accident[handling_end_date]"]').val(armada_accident.handling_end_date);
            $('[name="armada_accident[cost_remarks]"]').val(armada_accident.cost_remarks);
            armada_accident.accident_causes = JSON.parse(armada_accident.accident_causes);
            if (armada_accident.accident_causes) {
                armada_accident.accident_causes.forEach((item) => {
                    $(".accident_cause").each(function(index, label) {
                        if ($(label).text() == item) {
                            let id = $(label).prop('for');
                            $('#' + id).prop('checked', true);
                        }
                    })
                });
            }
            armada_accident.urgency = JSON.parse(armada_accident.urgency);
            if (armada_accident.urgency) {
                armada_accident.urgency.forEach((item) => {
                    $(".urgency").each(function(index, label) {
                        if ($(label).text() == item) {
                            let id = $(label).prop('for');
                            $('#' + id).prop('checked', true);
                        }
                    })
                });
            }
            $('[name="armada_accident[description]"]').val(armada_accident.description);
            $('[name="armada_accident[periode][year]"]').val(parseInt(armada_accident.periode.split('-')[0]));
            $('[name="armada_accident[periode][month]"]').val(parseInt(armada_accident.periode.split('-')[1]));

            // Accident Cost
            $('[name="accident_cost[perobatan_korban]"]').val(accident_cost.perobatan_korban);
            $('[name="accident_cost[santunan]"]').val(accident_cost.santunan);
            $('[name="accident_cost[biaya_unit_korban]"]').val(accident_cost.biaya_unit_korban);
            $('[name="accident_cost[biaya_perkara]"]').val(accident_cost.biaya_perkara);

            $('[name="accident_cost[nominal_perobatan_korban]"]').prop('id', 'nominal_perobatan_korban');
            AutoNumeric.set('#nominal_perobatan_korban', accident_cost.nominal_perobatan_korban);
            $('[name="accident_cost[nominal_santunan]"]').prop('id', 'nominal_santunan');
            AutoNumeric.set('#nominal_santunan', accident_cost.nominal_santunan);
            $('[name="accident_cost[nominal_biaya_unit_korban]"]').prop('id', 'nominal_biaya_unit_korban');
            AutoNumeric.set('#nominal_biaya_unit_korban', accident_cost.nominal_biaya_unit_korban);
            $('[name="accident_cost[nominal_biaya_perkara]"]').prop('id', 'nominal_biaya_perkara');
            AutoNumeric.set('#nominal_biaya_perkara', accident_cost.nominal_biaya_perkara);

            // Vehicle Identity
            $('[name="vehicle_identity[nopol]"]').val(vehicle_identity.nopol);
            $('[name="vehicle_identity[nopol]"]').trigger('change');
            $('[name="vehicle_identity[jenis_kendaraan]"]').val(vehicle_identity.jenis_kendaraan);
            $('[name="vehicle_identity[cabang]"]').val(vehicle_identity.cabang);
            $('[name="vehicle_identity[stnk_status]"]').val(vehicle_identity.stnk_status);

            // driver identity
            $('[name="driver_identity[nama]"]').val(driver_identity.name);
            $('[name="driver_identity[nik]"]').val(driver_identity.nik);
            $('[name="driver_identity[jabatan]"]').val(driver_identity.jabatan);
            $('[name="driver_identity[status]"]').val(driver_identity.status);
            $('[name="driver_identity[jenis_sim]"]').val(driver_identity.jenis_sim);
            $('[name="driver_identity[sim_status]"]').val(driver_identity.sim_status);

            // PIC Area
            $('[name="pic_area[nama]"]').val(pic_area.nama);
            $('[name="pic_area[jabatan]"]').val(pic_area.jabatan);
            $('[name="pic_area[nik]"]').val(pic_area.nik);
            $('[name="pic_area[phone]"]').val(pic_area.phone);

            // Legal and insurance aspect
            $('[name="legal_aspect[status]"]').val(legal_aspect.status);
            $('[name="legal_aspect[remarks]"]').val(legal_aspect.remarks);

            $('[name="insurance_aspect[conclusion]"]').val(insurance_aspect.conclusion);
            $('[name="insurance_aspect[status]"]').val(insurance_aspect.status);
            $('[name="insurance_aspect[start_date_sla"]').val(insurance_aspect.start_date_sla);
            $('[name="insurance_aspect[end_date_sla]"]').val(insurance_aspect.end_date_sla);

            // Recovery
            $('[name="recovery_cost[insurance_value]"]').prop('id', 'insurance_value');
            AutoNumeric.set('#insurance_value', recovery_cost.insurance_value);
            $('[name="recovery_cost[employee_value]"]').prop('id', 'employee_value');
            AutoNumeric.set('#employee_value', recovery_cost.employee_value);
            $('[name="armada_accident[cost_remarks]"]').val(armada_accident.cost_remarks);
        });

        function openCase() {
            $('#submitform').prop('action', '/armadaaccident/opencase');
            $('#submitform').submit()
        }

        function closeCase() {
            $('#submitform').prop('action', '/armadaaccident/closecase');
            $('#submitform').submit()
        }
    </script>
@endsection
