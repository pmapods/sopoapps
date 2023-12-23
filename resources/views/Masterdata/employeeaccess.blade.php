@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Akses Karyawan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Akses Karyawan</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="table-responsive">
        <table id="employeeaccessDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th width="42%">Akses Lokasi</th>
                    <th width="42%">Akses Menu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $key=>$employee)
                    <tr data-employee="{{$employee}}">
                        <td>{{$key+1}}</td>
                        <td class="text-nowrap">{{$employee->code}}</td>
                        <td class="text-nowrap">{{$employee->name}}</td>
                        <td class="small text-justify">{{ $employee->location_access_text() }}</td>
                        <td>{{ ($employee->menu_access) ? $employee->menu_access->access_list_text() : "" }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var table = $('#employeeaccessDT').DataTable(datatable_settings);
        $('#employeeaccessDT tbody').on('click', 'tr', function () {
            let data = $(this).data('employee');
            window.location.href ='/employeeaccess/'+data['code']+'/';
        });
    })
</script>
@endsection
