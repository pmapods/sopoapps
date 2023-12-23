@extends('Layout.app')
@section('local-css')
<style>
</style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Kelengkapan Berkas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item active">Kelengkapan Berkas</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="row">
        @foreach ($categories as $category)
            <div class="col-md-6">
                <h4>{{$category->name}}</h4>
                <ul>
                    @foreach ($category->file_completements as $file)
                    <li>{{$file->name}}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>

@endsection
@section('local-js')
<script>
</script>
@endsection
