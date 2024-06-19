@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Organization Chart</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Karyawan</li>
                    <li class="breadcrumb-item active">Organization Chart</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">

            <a href="/employee" class="btn btn-success ml-2">Masterdata Karyawan</a>

        </div>
    </div>
</div>

<div class="content-body px-4">
    <div class="table-responsive">
        <table id="orgHeaderTable" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th>#</th>
                    <th>Kode</th>
                    <th>NIK</th>
                    <th>Nama RBM</th>
                    <th>Job Position</th>
                    <th>Region</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rbm_data as $key => $rbm_data)
                    <tr data-employee="{{$rbm_data}}">
                        <td>{{ $key+1 }}</td>
                        <td>{{ $rbm_data->code }}</td>
                        <td>{{ $rbm_data->nik }}</td>
                        <td>{{ $rbm_data->emp_name }}</td>
                        <td>{{ $rbm_data->job_title }}</td>
                        <td>{{ $rbm_data->reg_name }}</td>
                        <td><a href="#" class="text-primary font-weight-bold"
                            onclick="window.open('/orgcharts/' + {{ $rbm_data->nik }} + '', '_self')"
                            >Detail Structure</a></td>
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
        var table = $('#orgHeaderTable').DataTable(datatable_settings);
    })
</script>
@endsection