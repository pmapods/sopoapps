@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Approval Vendor Register</h1>
                <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area. 
                    <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operational</li>
                    <li class="breadcrumb-item active">
                        Approval Vendor Register
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
@php
    // dd($data[0]->data);
@endphp
<div class="content-body px-4">
        <table id="vendorregisterDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th>#</th>
                    <th>Jenis Usaha</th>
                    <th>Nama Perusahaan</th>
                    <th>Kota</th>
                    <th>Status Perusahaan</th>
                    <th>Bentuk Badan Hukum</th>
                    <th>Status Kepemilikan</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 1 @endphp
                @foreach ($data as $key => $item)
                <tr data-id="{{ $item->id }}">
                    <td>{{$count++}}</td>
                    <td>{{$item->type}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->nama_city}}</td>
                    <td>{{$item->ownership_status}}</td>
                    <td>{{$item->legal_form}}</td>
                    <td>{{$item->company_status}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>


@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        $('#vendorregisterDT').DataTable(datatable_settings);
        $('#vendorregisterDT tbody').on('click', 'tr', function () {
            const id = $(this).data("id");
            if(id){
                window.location.href = '/vendor-approve-register-detail?id='+id;
            }
        });
    })
</script>
@endsection