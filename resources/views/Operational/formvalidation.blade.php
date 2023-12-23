@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Form Validation</h1>
                <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area. 
                    <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item active">
                        Form Validation
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
        <table id="formValidationDT" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
                <tr role="row">
                    <th>#</th>
                    <th>Kode Tiket</th>
                    <th>SalesPoint</th>
                    <th>Status Tiket</th>
                    <th>Jenis Form</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 1 @endphp
                @foreach ($data as $key => $item)
                <tr data-formdata="{{ $item->data }}" 
                    data-itemtype="{{ $item->type }}">
                    <td>{{$count++}}</td>
                    <td>{{$item->armada_ticket->code}}</td>
                    <td>{{$item->salespoint->name}}</td>
                    <td>{{$item->armada_ticket->status()}}</td>
                    <td>{{ucwords(str_replace("_"," ",$item->type))}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>


@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        $('#formValidationDT').DataTable(datatable_settings);
        $('#formValidationDT tbody').on('click', 'tr', function () {
            const type = $(this).data("itemtype");
            const formdata = JSON.stringify($(this).data("formdata"));
            window.location.href = '/form-validation/validate?type='+type+'&formdata='+formdata;
        });
    })
</script>
@endsection
