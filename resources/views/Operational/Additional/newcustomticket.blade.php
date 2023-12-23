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
                    <h1 class="m-0 text-dark">Pengadaan Custom Ticket</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Pengadaan</li>
                        <li class="breadcrumb-item active">Pengadaan Custom Ticket</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
        <form action="/customticketing/ticket/create" id="customform" method="post" enctype="multipart/form-data">
            @csrf
            <div id="customdata"></div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Tanggal Pengajuan</label>
                        <input type="date" class="form-control created_date"
                            value="{{ now()->translatedFormat('Y-m-d') }}" disabled>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Tanggal Setup</label>
                        <input type="date" class="form-control requirement_date" name="requirement_date" required>
                        <small class="text-danger">*Tanggal pengadaan minimal 14 hari dari tanggal pengajuan</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Pilihan Area / SalesPoint</label>
                        <select class="form-control select2 salespoint_select2" name="salespoint_id" id="salespoint_id"
                            required>
                            <option value="" data-isjawasumatra="-1">-- Pilih SalesPoint --</option>
                            @foreach ($available_salespoints as $region)
                                <optgroup label="{{ $region->first()->region_name() }}">
                                    @foreach ($region as $salespoint)
                                        <option value="{{ $salespoint->id }}"
                                            data-isjawasumatra="{{ $salespoint->isJawaSumatra }}">{{ $salespoint->name }} --
                                            {{ $salespoint->jawasumatra() }} Jawa Sumatra</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small class="text-danger">* SalesPoint yang muncul berdasarkan hak akses tiap akun</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Pilih Jenis Pengadaan</label>
                        <select class="form-control custom_ticketing_select" id="custom_ticketing_id"
                            name="custom_ticketing_id" disabled required>
                            <option value="">-- Pilih Jenis Pengadaan --</option>
                            @foreach ($custom_ticketings->where('is_active', 1) as $custom_ticketing)
                                @php
                                    $ticket = json_decode($custom_ticketing->settings);
                                @endphp
                                <option value="{{ $custom_ticketing->id }}"
                                    data-data="{{ json_encode($custom_ticketing) }}">
                                    {{ $ticket->ticket_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Pilih Jenis Budget</label>
                        <select class="form-control budget_type" name="budget_type" disabled required>
                            <option value="">-- Pilih Jenis Budget --</option>
                            <option value="0">Budget</option>
                            <option value="1">Non Budget</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 border border-dark ml-2 my-2 p-3">
                    <h5>File yang perlu di Upload</h5>
                    <div class="d-flex flex-column" id="uploadFileRequirementField">
                        <span class="text-secondary">-</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Pilih Matriks Approval</label>
                        <select class="form-control" id="authorization" name="authorization_id" required disabled>
                            <option value="">-- Pilih Matriks Approval --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12 d-flex flex-row justify-content-center align-items-center" id="authorization_field">
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button type="submit" class="btn btn-primary">Buat Ticket</button>
            </div>
        </form>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('.autonumber').change(function() {
                autonumber($(this));
            });
        });

        // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
        $('.requirement_date').val(moment().add(14, 'days').format('YYYY-MM-DD'));
        $('.requirement_date').prop('min', moment().add(14, 'days').format('YYYY-MM-DD'));
        $('.requirement_date').trigger('change');

        $('.salespoint_select2').change(function() {
            if ($(this).val() != "") {
                $('.custom_ticketing_select').prop('disabled', false);
                $('.budget_type').prop('disabled', false);
                loadAuthorization($(this).val(), 0, "")
            } else {
                $('.custom_ticketing_select').prop('disabled', true);
                $('.budget_type').prop('disabled', true);
            }
            if ($('#custom_ticketing_id').val() != 13) {
                $('.custom_ticketing_select').val('');
                $('.custom_ticketing_select').trigger('change');
            }
        });
        $('.custom_ticketing_select').change(function() {
            $('#uploadFileRequirementField').empty();
            if ($(this).val() != "") {
                $('.budget_type').prop('disabled', false);
                uploadFileRequirement($(this).find('option:selected').data('data'));
            } else {
                $('.budget_type').prop('disabled', true);
                $('#uploadFileRequirementField').empty();
                $('#uploadFileRequirementField').append(
                    '<span class="text-secondary">-</span>');
            }
            $('.budget_type').val('');
            $('.budget_type').trigger('change');
        });

        $('#custom_ticketing_id').change(function() {
            let data = $(this).val();
            console.log(data);
            if (data == 12) {
                $('.budget_type').prop('disabled', true);
                $('.budget_type').prop('required', false);
            } else {
                $('.budget_type').prop('disabled', false);
                $('.budget_type').prop('required', true);
            }
            if (data == 13) {
                $('.salespoint_select2').val(251).change();
                $('.salespoint_select2').prop('disabled', true);
            } else {
                $('.salespoint_select2').prop('disabled', false);
            }
        });

        $('#authorization').change(function() {
            let list = $(this).find('option:selected').data('list');
            $('#authorization_field').empty();
            if (list !== undefined) {
                list.forEach(function(item, index) {
                    $('#authorization_field').append(
                        '<div class="d-flex text-center flex-column mr-3"><div class="font-weight-bold">' +
                        item.sign_as + '</div><div>' + item.employee.name +
                        '</div><div class="text-secondary">(' + item
                        .employee_position
                        .name + ')</div></div>');
                    if (index != list.length - 1) {
                        $('#authorization_field').append(
                            '<div class="mr-3"><i class="fa fa-chevron-right" aria-hidden="true"></i></div>'
                        );
                    }
                });
            }
        });

        function loadAuthorization(salespoint_id, form_type, notes) {
            $('#authorization').find('option[value!=""]').remove();
            $('#authorization').prop('disabled', true);
            if (salespoint_id == "") {
                return;
            }
            $.ajax({
                type: "get",
                url: '/getAuthorization?salespoint_id=' + salespoint_id + '&form_type=' + form_type +
                    '&notes=' +
                    notes,
                success: function(response) {
                    let data = response.data;
                    if (data.length == 0) {
                        alert(
                            'Matriks Approval Barang Jasa tidak tersedia untuk salespoint yang dipilih, silahkan mengajukan Matriks Approval ke admin'
                        );
                        return;
                    }
                    data.forEach(item => {
                        let namelist = item.list.map(a => a.employee_name);
                        let option_text = '<option value="' + item.id + '">' + namelist
                            .join(" -> ") +
                            '</option>';
                        if (item.notes != "") {
                            option_text += ' (' + item.notes + ')'
                        }
                        $('#authorization').append(option_text);
                    });
                    $('#authorization').prop('disabled', false);
                },
                error: function(response) {
                    alert('load data failed. Please refresh browser or contact admin');
                    $('#authorization').prop('disabled', true);
                },
                complete: function() {
                    $('#authorization').val("");
                    $('#authorization').trigger('change');
                }
            });
        }

        function uploadFileRequirement(data) {
            let settings = JSON.parse(data.settings);
            console.log(settings);
            $('#uploadFileRequirementField').empty();
            for (let index = 0; index < settings.create_ticket_file.length; index++) {
                const filename = settings.create_ticket_file[index];
                $('#uploadFileRequirementField').append(
                    '<div class="form-group mr-2"><label class="required_field">' +
                    filename +
                    '</label><input type="file" class="form-control-file" name="file[]" onclick="this.value=null;" required/></div>'
                );
                $('#uploadFileRequirementField').append(
                    '<input type="hidden" name="filename[]" class="form-control" value="' + filename +
                    '" />');
            }
        }
    </script>
@endsection
