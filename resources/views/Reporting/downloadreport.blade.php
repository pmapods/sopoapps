@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Download Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Reporting</li>
                        <li class="breadcrumb-item active">Download Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body px-4">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="report_type_select"></label>
                    <select class="form-control select2" id="report_type_select">
                        <option value="">-- Pilih Report --</option>
                        <option value="ho_budget">Budget HO (Budget vs Actual)</option>
                        <option value="area_budget">Budget Area (Budget vs Actual)</option>
                        <option value="non_budget">Non Budget</option>
                        <option value="po_report">Purchase Order Report</option>
                        <option value="ticket_report">Report Ticketing</option>
                        <option value="report_monitoring">Report Monitoring</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- HO BUDGET --}}
        <div class="row d-none field" id="ho_budget_field">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Salespoint HO</label>
                    <select class="form-control salespoint_select">
                        <option value="">-- Pilih Salespoint -- </option>
                        @foreach ($salespoints->where('status', 5) as $salespoint)
                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">Divisi</label>
                    <select class="form-control division_select">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach (config('customvariable.division') as $division)
                            <option value="{{ $division }}">{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">Tahun</label>
                    <select class="form-control year_select">
                        <option value="">-- Pilih Tahun --</option>
                        @for ($i = 0; $i < 5; $i++)
                            <option value="{{ now()->subYears(1)->format('Y') + $i }}">
                                {{ now()->subYears(1)->format('Y') + $i }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-3"></div>
            <div class="col-md-1 budget_ho_aktif_group">
                <button type="button" class="btn btn-primary form-control"
                    onclick="exporthobudget('ho active')">Export</button>
            </div>
            <div class="col-md-1 budget_ho_non_aktif_group" style="display: none">
                <button type="button" class="btn btn-primary form-control"
                    onclick="exporthobudget('ho non active')">Export</button>
            </div>
        </div>

        {{-- AREA BUDGET --}}
        <div class="row d-none field" id="area_budget_field">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Salespoint</label>
                    <select class="form-control select2 salespoint_select">
                        <option value="">-- Pilih Salespoint -- </option>
                        @foreach ($salespoints->where('status', '!=', 5) as $salespoint)
                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="required_field">Jenis Budget</label>
                    <select class="form-control budget_type_select">
                        <option value="">-- Pilih Jenis Budget --</option>
                        <option value="inventory">Inventory</option>
                        <option value="armada">Armada</option>
                        <option value="assumption">Assumption</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="required_field">Tahun</label>
                    <select class="form-control year_select">
                        <option value="">-- Pilih Tahun --</option>
                        @for ($i = 0; $i < 5; $i++)
                            <option value="{{ now()->subYears(1)->format('Y') + $i }}">
                                {{ now()->subYears(1)->format('Y') + $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-2 budget_aktif_group">
                <div class="form-group">
                    <label class="required_field">Budget Aktif</label>
                    <input class="form-control budget_code_input" type="text" disabled>
                </div>
            </div>
            <div class="col-md-1 mt-4 budget_aktif_group">
                <div class="mt-2">
                    <button type="button" class="btn btn-primary form-control"
                        onclick="exportareabudget('active')">Export</button>
                </div>
            </div>
            <div class="col-md-6 mt-4 budget_aktif_group">
            </div>

            {{-- Export tidak aktif --}}
            <div class="col-md-2 ml-6 budget_non_aktif_group" style="display: none">
                <div class="form-group">
                    <label>Budget Tahun Sebelumnya</label>
                    <input class="form-control budget_code_input" type="text" id="budget_area_non_active" disabled>
                </div>
            </div>
            <div class="col-md-2 mt-4 budget_non_aktif_group" style="display: none">
                <div class="mt-2">
                    <button type="button" class="btn btn-primary form-control" onclick="exportareabudget('non active')">
                        Export
                    </button>
                </div>
            </div>
        </div>

        {{-- NON BUDGET --}}
        <div class="row d-none field" id="non_budget_field">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">Salespoint</label>
                    <select class="form-control select2 salespoint_select">
                        <option value="">-- Pilih Salespoint -- </option>
                        @foreach ($salespoints as $salespoint)
                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">Start Date</label>
                    <input type="date" class="form-control start_date">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">End Date</label>
                    <input type="date" class="form-control end_date">
                </div>
            </div>
            <div class="col-md-3"></div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary form-control" onclick="exportnonbudget()">Export</button>
            </div>
        </div>

        {{-- PO REPORT --}}
        <div class="row d-none field" id="po_report_field">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">Salespoint</label>
                    <select class="form-control select2 salespoint_select">
                        <option value="">-- Pilih Salespoint -- </option>
                        <option value="all">All</option>
                        @foreach ($salespoints as $salespoint)
                            <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">Start Date</label>
                    <input type="date" class="form-control start_date">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="required_field">End Date</label>
                    <input type="date" class="form-control end_date">
                </div>
            </div>
            <div class="col-md-3"></div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary form-control" onclick="exportporeport()">Export</button>
            </div>
        </div>

        {{-- REPORT Ticketing --}}
        <div class="row d-none field" id="ticket_report_field">
            <div class="col-md-1">
                <button type="button" class="btn btn-primary form-control" onclick="exportreportticketing()">Export
                    Data</button>
            </div>
        </div>

        {{-- REPORT Monitoring --}}
        <div class="row d-none field" id="report_monitoring_field">
            <div class="col-md-2">
                <label>Filter Bulan Pengajuan</label>
                <select class="form-control select2 month_filter" name="month_filter" id="month_filter">
                    <option value="">-- Bulan Pengajuan --</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mey</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
                <br>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="required_field">Tahun</label>
                    <select class="form-control year_filter" name="year_filter" id="year_filter">
                        <option value="">-- Pilih Tahun --</option>
                        @for ($i = 0; $i < 5; $i++)
                            <option value="{{ now()->subYears(1)->format('Y') + $i }}">
                                {{ now()->subYears(1)->format('Y') + $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="required_field">End Period</label>
                    <input type="date" class="form-control end_period_filter"name="end_period_filter"
                        id="end_period_filter">
                </div>
            </div>
            <div class="col-md-2 mt-4">
                <button type="button" class="btn btn-primary form-control col-mb-4"
                    onclick="exportmonitoring()">Export</button>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            $('#report_type_select').change(function() {
                let type = $('#report_type_select').val();
                $('.field').addClass('d-none');
                if (type == "ho_budget") {
                    $('#ho_budget_field').removeClass('d-none');
                }
                if (type == "area_budget") {
                    $('#area_budget_field').removeClass('d-none');
                }
                if (type == "non_budget") {
                    $('#non_budget_field').removeClass('d-none');
                }
                if (type == "po_report") {
                    $('#po_report_field').removeClass('d-none');
                }
                if (type == "ticket_report") {
                    $('#ticket_report_field').removeClass('d-none');
                }
                if (type == "report_monitoring") {
                    $('#report_monitoring_field').removeClass('d-none');
                }
            });

            $('#area_budget_field .salespoint_select , #area_budget_field .budget_type_select , #area_budget_field .year_select')
                .change(function() {
                    let salespoint_id = $('#area_budget_field .salespoint_select').val();
                    let budget_type = $('#area_budget_field .budget_type_select').val();
                    let year = $('#area_budget_field .year_select').val();
                    if (year && new Date().getFullYear() > year) {
                        $('.budget_aktif_group').each(function() {
                            $(this).hide();
                        });
                        $('.budget_non_aktif_group').each(function() {
                            $(this).show();
                        });
                    } else {
                        $('.budget_aktif_group').each(function() {
                            $(this).show();
                        });
                        $('.budget_non_aktif_group').each(function() {
                            $(this).hide();
                        });
                    }
                    if (budget_type != "" && salespoint_id != "" && year != "") {
                        $.ajax({
                            type: "get",
                            url: "/downloadreport/getActiveBudget?salespoint_id=" + salespoint_id +
                                "&budget_type=" + budget_type + "&year=" + year,
                            success: function(response) {
                                if (!response.error) {
                                    $('#area_budget_field .budget_code_input').val(response.data
                                        .budget_code);
                                    alert(response.message);
                                } else {
                                    $('#area_budget_field .budget_code_input').val(response
                                        .message);
                                }
                            }
                        });

                        $.ajax({
                            type: "get",
                            url: "/downloadreport/getNonActiveBudget?salespoint_id=" + salespoint_id +
                                "&budget_type=" + budget_type + "&year=" + year,
                            success: function(response) {
                                if (!response.error) {
                                    $('#budget_area_non_active').val(response.data
                                        .budget_code);
                                    alert(response.message);
                                } else {
                                    $('#budget_area_non_active').val(response
                                        .message);
                                }
                            }
                        });
                    } else {
                        $('#area_budget_field .budget_code_select').val("");
                        $('#area_budget_field .budget_code_select').find('option[value!=""]').remove();
                    }
                })

            $('#ho_budget_field .year_select').change(function() {
                let year = $('#ho_budget_field .year_select').val();
                if (year && new Date().getFullYear() > year) {
                    $('.budget_ho_aktif_group').each(function() {
                        $(this).hide();
                    });
                    $('.budget_ho_non_aktif_group').each(function() {
                        $(this).show();
                    });
                } else {
                    $('.budget_ho_aktif_group').each(function() {
                        $(this).show();
                    });
                    $('.budget_ho_non_aktif_group').each(function() {
                        $(this).hide();
                    });
                }

            })
        });

        function exporthobudget(type) {
            let salespoint = $('#ho_budget_field .salespoint_select').val();
            let division = $('#ho_budget_field .division_select').val();
            let year = $('#ho_budget_field .year_select').val();
            if (salespoint == "") {
                alert('Salespoint belum dipilih');
            }
            if (division == "") {
                alert('Divisi belum dipilih');
            }
            if (year == "") {
                alert('Tahun belum dipilih');
            }
            if (type == 'ho non active') {
                window.open('/downloadreport/hobudgetnonactive?salespoint_id=' + salespoint + '&division=' + division +
                    '&year=' + year)
            } else {
                window.open('/downloadreport/hobudget?salespoint_id=' + salespoint + '&division=' + division + '&year=' +
                    year)
            }
        }

        function exportareabudget(type) {
            let salespoint = $('#area_budget_field .salespoint_select').val();
            let budget_type = $('#area_budget_field .budget_type_select').val();
            let budget_code = $('#area_budget_field .budget_code_input').val();
            let year = $('#area_budget_field .year_select').val();
            if (salespoint == "") {
                alert('Salespoint belum dipilih');
                return;
            }
            if (budget_type == "") {
                alert('Tipe Budget belum dipilih');
                return;
            }
            if (budget_code == "") {
                alert('Kode Budget belum dipilih');
                return;
            }
            if (year == "") {
                alert('Tahun belum dipilih');
            }
            if (type == 'non active') {
                window.open('/downloadreport/areabudgetnonactive?salespoint_id=' + salespoint + '&budget_type=' +
                    budget_type + '&budget_code=' + budget_code + '&year=' + year)
            } else {
                window.open('/downloadreport/areabudget?salespoint_id=' + salespoint + '&budget_type=' + budget_type +
                    '&budget_code=' + budget_code + '&year=' + year)
            }
        }

        function exportnonbudget() {
            let salespoint = $('#non_budget_field .salespoint_select').val();
            let start_date = $('#non_budget_field .start_date').val();
            let end_date = $('#non_budget_field .end_date').val();
            if (salespoint == "") {
                alert('Salespoint belum dipilih');
                return;
            }
            if (start_date == "") {
                alert('Tipe Budget belum dipilih');
                return;
            }
            if (end_date == "") {
                alert('Kode Budget belum dipilih');
                return;
            }
            if (start_date > end_date) {
                alert('Start date harus lebih terdahulu dari End Date');
                return;
            }
            window.open('/downloadreport/nonbudget?salespoint_id=' + salespoint + '&start_date=' + start_date +
                '&end_date=' + end_date)
        }

        function exportporeport() {
            let salespoint = $('#po_report_field .salespoint_select').val();
            let start_date = $('#po_report_field .start_date').val();
            let end_date = $('#po_report_field .end_date').val();
            if (salespoint == "") {
                alert('Salespoint belum dipilih');
                return;
            }
            if (start_date == "") {
                alert('Tipe Budget belum dipilih');
                return;
            }
            if (end_date == "") {
                alert('Kode Budget belum dipilih');
                return;
            }
            if (start_date > end_date) {
                alert('Start date harus lebih terdahulu dari End Date');
                return;
            }
            window.open('/downloadreport/poreport?salespoint_id=' + salespoint + '&start_date=' + start_date +
                '&end_date=' + end_date)
        }

        function exportreportticketing() {
            window.open('/downloadreport/exportreportticketing')
        }

        function exportmonitoring() {
            let month_filter = $('#report_monitoring_field .month_filter').val();
            let year_filter = $('#report_monitoring_field .year_filter').val();
            let end_period_filter = $('#report_monitoring_field .end_period_filter').val();

            if (month_filter == "") {
                alert('Bulan Belum Di Pilih');
                return;
            }
            if (year_filter == "") {
                alert('Tahun belum dipilih');
                return;
            }
            if (end_period_filter == "") {
                alert('End Period belum dipilih');
                return;
            }

            console.log(month_filter);
            console.log(year_filter);
            console.log(end_period_filter);

            window.open('/downloadreport/exportmonitoring?' + `month_filter=${month_filter}` +
                `&year_filter=${year_filter}` + `&end_period_filter=${end_period_filter}`)
        }
    </script>
@endsection
