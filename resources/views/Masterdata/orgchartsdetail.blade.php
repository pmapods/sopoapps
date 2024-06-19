@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Organization Chart Detail ({{ $nik }})</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Karyawan</li>
                    <li class="breadcrumb-item active">Organization Chart</li>
                    <li class="breadcrumb-item active">Organization Chart Detail ({{ $nik }})</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">

            <a href="/orgcharts" class="btn btn-success ml-2">Master Organization Chart</a>

        </div>
    </div>
</div>

<div class="content-body px-4">
    <div class="table-responsive">
        <table id="orgDetailTable" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th>#</th>
                    <th>Region</th>
                    <th>Salespoint</th>
                    <th>NIK RBM</th>
                    <th>Nama RBM</th>
                    <th>Email RBM</th>
                    <th>NIK BM</th>
                    <th>Nama BM</th>
                    <th>Email BM</th>
                    <th>NIK ROM</th>
                    <th>Nama ROM</th>
                    <th>Email ROM</th>
                    <th>NIK AOS</th>
                    <th>Nama AOS</th>
                    <th>Email AOS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 0;
                @endphp
                @foreach ($orgDataDetail as $orgDataDetail)
                    <tr data-employee="{{$orgDataDetail->slp_name}}">
                        <td>{{ $no+1 }}</td>
                        <td>{{ $orgDataDetail->region_name }}</td>
                        <td>{{ $orgDataDetail->slp_name }}</td>
                        <td>{{ $orgDataDetail->rbm_code }}</td>
                        <td>{{ $orgDataDetail->rbm_name }}</td>
                        <td>{{ $orgDataDetail->rbm_email }}</td>
                        <td>{{ $orgDataDetail->bm_code }}</td>
                        <td>{{ $orgDataDetail->bm_name }}</td>
                        <td>{{ $orgDataDetail->bm_email }}</td>
                        <td>{{ $orgDataDetail->rom_code }}</td>
                        <td>{{ $orgDataDetail->rom_name }}</td>
                        <td>{{ $orgDataDetail->rom_email }}</td>
                        <td>{{ $orgDataDetail->aos_code }}</td>
                        <td>{{ $orgDataDetail->aos_name }}</td>
                        <td>{{ $orgDataDetail->aos_email }}</td>
                    </tr> 
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
@section('local-js')
<script>
    $(document).ready(function() {
        var table = $('#orgDetailTable').DataTable(datatable_settings);
    })
</script>
@endsection