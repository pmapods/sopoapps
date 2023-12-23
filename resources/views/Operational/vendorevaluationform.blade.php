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

        .tdbreak {
            /* word-break : break-all; */
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Add New Vendor Evaluation</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item">Vendor Evaluation</li>
                        <li class="breadcrumb-item active">Add New Vendor Evaluation</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
            </div>
        </div>
    </div>
    <div class="content-body">
        <form action="/vendor-evaluation/addvendorevaluation" method="post" enctype="multipart/form-data">
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
                        <label class="required_field">Pilihan Area / SalesPoint</label>
                        <select class="form-control select2 salespoint_select2" name="salespoint_id" required>
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
                        <label class="required_field">Vendor</label>
                        <select class="form-control vendor_select" name="vendor" required>
                            <option value="">-- Pilih Vendor --</option>
                            <option value="0">Pest Control</option>
                            <option value="1">CIT</option>
                            {{-- <option value="2">Si Cepat</option> --}}
                            <option value="3">Ekspedisi</option>
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">Start Periode Penilaian</label>
                        <input type="date" class="form-control start_periode_penilaian" name="start_periode_penilaian"
                            required disabled>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="required_field">End Periode Penilaian</label>
                        <input type="date" class="form-control end_periode_penilaian" name="end_periode_penilaian"
                            required disabled>
                    </div>
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
            $('.start_periode_penilaian').val(moment().subtract(1, 'months').startOf('month').format('YYYY-MM-DD'));
            $('.end_periode_penilaian').val(moment().subtract(1, 'months').endOf('month').format('YYYY-MM-DD'));
        });
    </script>
@endsection
