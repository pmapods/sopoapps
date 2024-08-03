@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Form Validation Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Form Validation</li>
                    <li class="breadcrumb-item active">Form Validation Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-4">
    @if ($type == "perpanjangan_form" || $type == "percepatan_replace_form" || $type == "percepatan_renewal_form" || $type == "percepatan_end_kontrak_form")
        <div class="row">
            <div class="col-md-6">
                @include("Operational.Armada.formperpanjanganperhentian")
                <div class="d-flex justify-content-center mt-3">
                    <button type="button" class="btn btn-danger mr-1" onclick="rejectForm('{{ $type }}',{{ $armadaticket->perpanjangan_form->id }})">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approveForm('{{ $type }}',{{ $armadaticket->perpanjangan_form->id }})">Approve</button>
                </div>
            </div>
        </div>
    @endif
    @if ($type == "mutasi_form")
        <div class="row">
            <div class="col-md-6">
                @include("Operational.Armada.formmutasi")
                <div class="d-flex justify-content-center mt-3">
                    <button type="button" class="btn btn-danger mr-1" onclick="rejectForm('{{ $type }}',{{ $armadaticket->mutasi_form->id }})">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approveForm('{{ $type }}',{{ $armadaticket->mutasi_form->id }})">Approve</button>
                </div>
            </div>
        </div>
    @endif
    @if ($type == "facility_form")
        <div class="row">
            <div class="col-md-6">
                @include("Operational.Armada.formfasilitas")
                <div class="d-flex justify-content-center mt-3">
                    <button type="button" class="btn btn-danger mr-1" onclick="rejectForm('{{ $type }}',{{ $armadaticket->facility_form->id }})">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approveForm('{{ $type }}',{{ $armadaticket->facility_form->id }})">Approve</button>
                </div>
            </div>
        </div>
    @endif
</div>
<form id="submitform" method="post" enctype="multipart/form-data">
    @csrf
    <div></div>
</form>
@endsection
@section('local-js')
<script>
    function approveForm(type,id){
        $('#submitform').prop('action', '/form-validation/approve');
        $('#submitform').find('div').empty();
        $('#submitform').find('div').append('<input type="hidden" name="type" value="'+type+'">');
        if(type == "perpanjangan_form" || type == "percepatan_replace_form" || type == "percepatan_renewal_form" || type == "percepatan_end_kontrak_form"){
            $('#submitform').find('div').append('<input type="hidden" name="perpanjangan_form_id" value="'+id+'">');
        }
        if(type == "mutasi_form"){
            $('#submitform').find('div').append('<input type="hidden" name="mutasi_form_id" value="'+id+'">');
        }
        if(type == "facility_form"){
            $('#submitform').find('div').append('<input type="hidden" name="facility_form_id" value="'+id+'">');
        }
        $('#submitform').submit();
    }

    function rejectForm(type,id){
        let reason = prompt("Masukan alasan Reject");
        if (reason != null) {
            if(reason.trim() == ''){
                alert("Alasan Harus diisi");
                return;
            }
            $('#submitform').prop('action', '/form-validation/reject');
            $('#submitform').find('div').append('<input type="hidden" name="reason" value="'+reason+'">');
            $('#submitform').find('div').append('<input type="hidden" name="type" value="'+type+'">');
            if(type == "perpanjangan_form" || type == "percepatan_replace_form" || type == "percepatan_renewal_form" || type == "percepatan_end_kontrak_form"){
                $('#submitform').find('div').append('<input type="hidden" name="perpanjangan_form_id" value="'+id+'">');
            }
            if(type == "mutasi_form"){
                $('#submitform').find('div').append('<input type="hidden" name="mutasi_form_id" value="'+id+'">');
            }
            if(type == "facility_form"){
                $('#submitform').find('div').append('<input type="hidden" name="facility_form_id" value="'+id+'">');
            }
            $('#submitform').submit();
        }
    }
</script>
@endsection
