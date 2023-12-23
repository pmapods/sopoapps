@extends('Layout.app')
@section('local-css')
    <style>
        table tr, table td {
            border: 1px solid #000 !important;
        }
    </style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">BA Upload ({{$securityticket->code}})</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Purchase Requisition</li>
                    <li class="breadcrumb-item active">BA Upload ({{$securityticket->code}})</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="mx-2">
    @if ($securityticket->status < 4)
        <form action="/uploadsecurityba" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="updated_at" value="{{$securityticket->updated_at}}">
            <input type="hidden" name="security_ticket_id" value="{{$securityticket->id}}">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="required_field">Pilih File BA</label>
                        <input type="file" class="form-control-file validatefilesize" 
                        name="ba_file" accept="image/*,application/pdf" 
                        id="ba_file"
                        required>
                        <small class="text-danger">*jpg, jpeg, pdf (MAX 5MB)</small>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit BA</button>
                </div>
            </div>
    
        </form>
    @else
        <h4>Upload BA Pengadaan Lembur Security</h4>
        <span 
            onclick="window.open('/storage/{{ $securityticket->ba_path }}')" 
            class="font-weight-bold text-primary">Tampilkan File BA
        </span>
    @endif
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        $('#ba_file').on('change', function (event) {
        var reader = new FileReader();
        let value = $(this).val();
        if(validatefilesize(event)){
            reader.onload = function(e) {
                temp_olditem_file = e.target.result;
                temp_olditem_extension = value.split('.').pop().toLowerCase();
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
        }
    });
    });
</script>
@endsection
