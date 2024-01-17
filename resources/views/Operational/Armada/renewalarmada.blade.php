@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Peremajaan Armada</h1>
                    {{-- <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                    <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                </div> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operational</li>
                        <li class="breadcrumb-item active">Peremajaan Armada</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addRenewalArmadaModal">
                    Request Renewal Armada
                </button>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="renewalArmadaDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint Awal</th>
                        <th>SalesPoint Baru</th>
                        <th>Jenis Kendaraan</th>
                        <th>Nomor Kendaaraan Lama</th>
                        <th>Nomor Kendaaraan Baru</th>
                        <th>Request By</th>
                        <th>Status Approval</th>

                </thead>
                <tbody>
                    @foreach ($renewalarmadas as $key => $renewalarmada)
                        <tr data-armada="{{ $renewalarmada }}">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @if ($renewalarmada->last_salespoint() != null)
                                    {{ $renewalarmada->last_salespoint()->name }}
                                @endif
                            </td>
                            <td>
                                @if ($renewalarmada->new_salespoint() != null)
                                    {{ $renewalarmada->new_salespoint()->name }}
                                @endif
                            </td>
                            <td>{{ $renewalarmada->armada_type->name }}</td>
                            <td class="text-uppercase">{{ $renewalarmada->old_plate }}</td>
                            <td class="text-uppercase">{{ $renewalarmada->new_plate }}</td>
                            <td>
                                @if ($renewalarmada->created_by_employee() != null)
                                    {{ $renewalarmada->created_by_employee()->name }}
                                @endif
                            </td>
                            <td>
                                <div class="d-flex text-center flex-column mr-3">
                                    @if ($renewalarmada->status == 0)
                                        <div class="text-warning">Waiting for approval</div>
                                        <div>{{ $renewalarmada->approved_by_employee()->name }}</div>
                                    @endif

                                    @if ($renewalarmada->status == -1)
                                        <div class="text-danger d-flex flex-column">
                                            <span>Terminate {{ $renewalarmada->updated_at->format('Y-m-d') }}</span>
                                            <span>Alasan : {{ $renewalarmada->terminated_reason }}</span>
                                        </div>
                                        <div>{{ $renewalarmada->terminated_by_employee()->name }}</div>
                                    @endif

                                    @if ($renewalarmada->status == 1)
                                        <div class="text-success">Approved {{ $renewalarmada->finished_date }}
                                        </div>
                                        <div>{{ $renewalarmada->approved_by_employee()->name }}</div>
                                    @endif

                                    @if ($renewalarmada->status == 2)
                                        <div class="text-danger d-flex flex-column">
                                            <span>Reject {{ $renewalarmada->updated_at->format('Y-m-d') }}</span>
                                            <span>Alasan : {{ $renewalarmada->reject_reason }}</span>
                                        </div>
                                        <div>{{ $renewalarmada->rejected_by_employee()->name }}</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <div class="modal fade" id="addRenewalArmadaModal" tabindex="-1" data-backdrop="static" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Renewal Armada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/addrenewalarmada" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Pilih File BASTK lengkap dengan ttd</label>
                                    <input type="file" class="form-control-file validatefilesize"
                                        name="bastk_file" accept="image/*,application/pdf" required>
                                    <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">SalesPoint Awal</label>
                                    <select class="form-control select2 salespoint_select2" name="last_salespoint_id" required>
                                        <option value="">-- Pilih SalesPoint --</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>

                                    <label class="required_field">SalesPoint Akhir</label>
                                    <select class="form-control new_salespoint" name="new_salespoint_id">
                                        <option value="">-- Pilih SalesPoint --</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Armada</label>
                                    <select class="form-control armada_type" name="armada_type_plate" required>
                                            <option>-- Pilih Jenis Kendaraan --</option>
                                    </select>
                                    <small class="text-danger">*Armada lama akan terhapus dari sistem secara otomatis</small>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Nopol Kendaraan Lama</label>
                                    <input type="text" class="form-control plate" name="old_plate"
                                        placeholder="Masukkan Nopol Kendaraan" id="forbiddenChar" required>

                                    <label class="required_field">Nopol Kendaraan Baru</label>
                                    <input type="text" class="form-control" name="new_plate"
                                        placeholder="Masukkan Nopol Kendaraan" id="forbiddenChar" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="required_field">Tahun Kend. Lama</label>
                                    <input type="number" class="form-control vehicle_year" min="1900"
                                        max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                        name="old_vehicle_year" required>

                                    <label class="required_field">Tahun Kend. Baru</label>
                                    <input type="number" class="form-control autonumber" min="1900"
                                        max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                        name="new_vehicle_year" required>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Pilih Approval</label>
                                    <select class="form-control select2" id="approval" name="approved_by" required>
                                        <option value="">-- Pilih Approval --</option>
                                        @foreach ($authorizations->where('form_type', 16) as $authorization)
                                            @foreach ($authorization_details->where('authorization_id', $authorization->id) as $approval)
                                                @if ($employee = $employees->find($approval->employee_id))
                                                    <option value="{{ $employee->id }}">{{ Auth::user()->name }} -> {{ $employee->name }}</option>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label>Jabatan</label>
                                    <select class="form-control select2 employee_positions" name="position" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach ($employee_positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Renewal Armada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailRenewalArmadaModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Renewal Armada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/updateRenewalArmada" method="post" enctype="multipart/form-data" id="detailrenewalarmadaform">
                    @csrf
                    @method('post')
                    <input type="hidden" name="id" class="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">SalesPoint Awal</label>
                                    <select class="form-control select2 salespoint" name="last_salespoint_id" disabled>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>

                                    <label class="required_field">SalesPoint Akhir</label>
                                    <select class="form-control select2 salespoint" name="new_salespoint_id" disabled>
                                        @foreach ($salespoints as $salespoint)
                                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Armada</label>
                                    <select class="form-control armada_type" name="armada_type_id" disabled>
                                        @foreach ($armada_types as $type)
                                            <option data-niaga="{{ $type->isNiaga }}" value="{{ $type->id }}">
                                                {{ $type->brand_name }} {{ $type->name }}
                                                ({{ $type->isNiaga() }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger">*Armada lama akan terhapus dari sistem secara otomatis</small>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Nopol Kendaraan Lama</label>
                                    <input type="text" class="form-control" name="old_plate" disabled>

                                    <label class="required_field">Nopol Kendaraan Baru</label>
                                    <input type="text" class="form-control" name="new_plate" disabled>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="required_field">Tahun Kend. Lama</label>
                                    <input type="number" class="form-control autonumber" min="1900"
                                        max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                        name="old_vehicle_year" disabled>

                                    <label class="required_field">Tahun Kend. Baru</label>
                                    <input type="number" class="form-control autonumber" min="1900"
                                        max="{{ now()->format('Y') }}" value="{{ now()->format('Y') }}"
                                        name="new_vehicle_year" disabled>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required_field">Status</label>
                                    <select class="form-control status" name="status" disabled>
                                        <option value="1">Approved</option>
                                        <option value="0">Waiting for Approval</option>
                                        <option value="2">Rejected</option>
                                        <option value="-1">Terminated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-8">
                                    <div class="form-group">
                                        <label class="required_field">File</label>
                                        <div id="linkContainer"></div>
                                    </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label class="required_field">Approval</label>
                                    <select class="form-control select2 approval" name="approved_by" disabled>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div id="buttonContainer"></div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form action="/renewalarmada/confirm" method="post" id="confirmform">
        @csrf
        <div class="input_list">
        </div>
    </form>
    <form action="/renewalarmada/reject" method="post" id="rejectform">
        @csrf
        <div class="input_list">
        </div>
    </form>
    <form action="/renewalarmada/terminate" method="post" id="terminateform">
        @csrf
        <div class="input_list">
        </div>
    </form>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('.autonumber').change(function() {
                autonumber($(this));
            });

            var user_login = {{ $user_login }};
            console.log("user_login");
            console.log(user_login);
            var table = $('#renewalArmadaDT').DataTable(datatable_settings);
            $('#renewalArmadaDT tbody').on('click', 'tr', function() {
                let modal = $('#detailRenewalArmadaModal');
                let data = $(this).data('armada');
                let newyear = data['new_vehicle_year'].split('-')[0];
                let oldyear = data['old_vehicle_year'].split('-')[0];
                let bastkPath = data['bastk_path'];
                let user_approved = data['approved_by'];
                let status = data['status'];
                let linkContainer = $('#linkContainer');
                let buttonContainer = $('#buttonContainer');
                buttonContainer.empty();
                linkContainer.empty();

                if ((user_login == 1 ||  user_login == 115 ||
                    user_login == user_approved) && status == 0)
                    {
                        let rejectButton = $('<button>', {
                            type: 'button',
                            class: 'btn btn-danger',
                            text: 'Reject',
                            click: rejectRenewal
                        });
                        buttonContainer.append(rejectButton);

                        let confirmButton = $('<button>', {
                            type: 'button',
                            class: 'btn btn-success',
                            text: 'Confirm',
                            click: confirmRenewal
                        });
                        buttonContainer.append(confirmButton);

                } else if ((user_login == 1 || user_login == 115 || user_login == 117 ||
                            user_login == 118 || user_login == 120 || user_login == 809)
                            && status == 0)
                    {
                        let terminateButton = $('<button>', {
                            type: 'button',
                            class: 'btn btn-warning',
                            text: 'Batalkan Peremajaan',
                            click: terminateRenewal
                        });
                        buttonContainer.append(terminateButton);
                }



                if (bastkPath) {
                    linkContainer.html('<a href="#" onclick="window.open(\'/storage/' + bastkPath + '\')">Tampilkan File BA</a>');
                }

                modal.find('input[name="id"]').val(data['id']);
                modal.find('select[name="bastk_path"]').val(data['bastk_path']);
                modal.find('select[name="last_salespoint_id"]').val((data['last_salespoint_id'] == null) ? '' : data[
                    'last_salespoint_id']);
                modal.find('select[name="last_salespoint_id"]').trigger('change');
                modal.find('select[name="new_salespoint_id"]').val((data['new_salespoint_id'] == null) ? '' : data[
                    'new_salespoint_id']);
                modal.find('select[name="new_salespoint_id"]').trigger('change');
                modal.find('select[name="armada_type_id"]').val(data['armada_type_id']);
                modal.find('select[name="armada_type_id"]').trigger('change');
                modal.find('input[name="old_plate"]').val(data['old_plate']);
                modal.find('input[name="new_plate"]').val(data['new_plate']);
                modal.find('input[name="new_vehicle_year"]').val(newyear);
                modal.find('input[name="old_vehicle_year"]').val(oldyear);
                modal.find('select[name="approved_by"]').val(data['approved_by']);
                modal.find('select[name="approved_by"]').trigger('change');
                modal.find('select[name="status"]').val(data['status']);
                modal.find('select[name="status"]').trigger('change');
                modal.data('created_by', data['created_by']);
                modal.data('approved_by', data['approved_by']);
                modal.data('current-id', data['id']);
                modal.data('new_plate', data['new_plate']);
                modal.data('new_salespoint_id', data['new_salespoint_id']);
                modal.data('new_vehicle_year', data['new_vehicle_year']);

                modal.modal('show');
            });

            $('.approval').change(function() {
                var modal = $('#detailRenewalArmadaModal');
                var created_by = modal.data('created_by');
                var approved_by = modal.data('approved_by');

                let option_text = '<option>' + created_by + '->' + approved_by +'</option>';
                $('#approval').append(option_text);
            });

            $('.salespoint_select2').change(function() {
                let salespointId = $(this).val();
                let armadaTypeSelect = $('#addRenewalArmadaModal').find('.armada_type');

                armadaTypeSelect.prop('disabled', true);

                armadaTypeSelect.empty();

                let optionText = '<option value="">-- Pilih Armada --</option>';
                armadaTypeSelect.append(optionText);
                armadaTypeSelect.val("").prop('required', false);

                $.ajax({
                    type: "get",
                    url: '/getarmadabysalespoint/' + salespointId,
                    success: function(response) {
                        let data = response.data;
                        if (data && data.length > 0) {
                            data.forEach(item => {
                                $.ajax({
                                    type: "get",
                                    url: '/getarmadatype/' + item.armada_type_id,
                                    success: function(response) {
                                        let dataType = response.data;
                                        dataType.forEach(type => {
                                            let optionText = '<option value="' + item.plate + '">' +
                                                item.plate + '-' + type.name + '(' + type.brand_name + ')</option>';
                                            armadaTypeSelect.append(optionText);
                                        });

                                        armadaTypeSelect.val("").prop('required', true);
                                        armadaTypeSelect.trigger('change');
                                        armadaTypeSelect.prop('disabled', false);
                                    },
                                    error: function(response) {
                                        alert('Load data failed. Please refresh the browser or contact admin');
                                    },
                                });
                            });
                        } else {
                            armadaTypeSelect.prop('disabled', true);
                            // Handle the case where data is empty
                        }
                    },
                    error: function(response) {
                        alert('Load data failed. Please refresh the browser or contact admin');
                    },
                });
            });


            $('.armada_type').change(function() {
                let armadaTypeId = $(this).val();
                let plateInput = $('#addRenewalArmadaModal').find('.plate');
                let vehicleYearInput = $('#addRenewalArmadaModal').find('.vehicle_year');

                plateInput.prop('disabled', false);
                vehicleYearInput.prop('disabled', false);

                plateInput.val("");
                vehicleYearInput.val("");

                if (armadaTypeId) {
                    $.ajax({
                        type: "get",
                        url: '/getarmadabyplate/' + armadaTypeId,
                        success: function(response) {
                            let dataType = response.data;
                            if (dataType && dataType.length > 0) {
                                dataType.forEach(item => {
                                    let yearPart = item.vehicle_year.substring(0, 4);

                                    plateInput.val(item.plate);
                                    vehicleYearInput.val(yearPart);

                                })

                                plateInput.prop('disabled', false);
                                vehicleYearInput.prop('disabled', false);
                            } else {
                                // Handle the case where data is empty
                            }
                        },
                        error: function(response) {
                            alert('Load data failed. Please refresh the browser or contact admin');
                        },
                    });
                }
            });

            $('.status').on('change', function() {
                let modal = $(this).closest('.modal');
                let status = $(this).val();
                modal.find('.approved_by').val("");
                modal.find('.approved_by_field').hide();
                modal.find('.approved_by').prop('required', false);
                if (status == 1) {
                    modal.find('.approved_by_field').show();
                    modal.find('.approved_by').prop('required', true);
                }
            });

            $('#addRenewalArmadaModal').on('show.bs.modal', function() {
                $('#addRenewalArmadaModal form').trigger('reset');
                $('#addRenewalArmadaModal .salespoint').trigger('change');
                $('#addRenewalArmadaModal .status').trigger('change');
            });

            $('#forbiddenChar').on('input', function (event) {
                if ($(this).val().includes(' ')) {
                    event.preventDefault();
                    alert('Spaces are not allowed in this field.');
                    $(this).val($(this).val().replace(/\s/g, '')); // Remove spaces from the input value
                }
            });
        })

        function confirmRenewal() {
            var modal = $('#detailRenewalArmadaModal');
            var id = modal.data('current-id');
            var new_salespoint_id = modal.data('new_salespoint_id');
            var new_plate = modal.data('new_plate');
            var new_vehicle_year = modal.data('new_vehicle_year');

            $('#confirmform .input_list').empty();
            $('#confirmform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
            $('#confirmform .input_list').append('<input type="hidden" name="new_salespoint_id" value="' + new_salespoint_id + '">');
            $('#confirmform .input_list').append('<input type="hidden" name="new_plate" value="' + new_plate + '">');
            $('#confirmform .input_list').append('<input type="hidden" name="new_vehicle_year" value="' + new_vehicle_year + '">');
            $('#confirmform').submit();
        }


        function terminateRenewal(renewal_armada_id) {
            var reason = prompt("Masukkan alasan pembatalan");
            var modal = $('#detailRenewalArmadaModal');
            var id = modal.data('current-id');
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return
                }
                $('#terminateform .input_list').empty();
                $('#terminateform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
                $('#terminateform .input_list').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#terminateform').submit();
            }
        }

        function rejectRenewal(renewal_armada_id) {
            var reason = prompt("Masukkan alasan reject");
            var modal = $('#detailRenewalArmadaModal');
            var id = modal.data('current-id');
            if (reason != null) {
                if (reason.trim() == '') {
                    alert("Alasan Harus diisi");
                    return
                }
                $('#rejectform .input_list').empty();
                $('#rejectform .input_list').append('<input type="hidden" name="id" value="' + id + '">');
                $('#rejectform .input_list').append('<input type="hidden" name="reason" value="' + reason + '">');
                $('#rejectform').submit();
            }
        }

    </script>
@endsection