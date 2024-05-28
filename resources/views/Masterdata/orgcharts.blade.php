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

            <a href="/employee" class="btn btn-success ml-2">Organization Chart</a>

        </div>
    </div>
</div>

@endsection
@section('local-js')
<script>

</script>
@endsection