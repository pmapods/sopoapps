@extends('Layout.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">PR Manual ({{$ticket->code}})</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Operasional</li>
                    <li class="breadcrumb-item">Purchase Requisition</li>
                    <li class="breadcrumb-item">PR Manual ({{$ticket->code}})</li>
                    <li class="breadcrumb-item active">Update Nomor Asset</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<form action="/pr/{{ $ticket->code }}/updateassetnumber/update" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="ticket_id" value="{{$ticket->id}}">
    <input type="hidden" name="pr_id" value="{{$ticket->pr->id}}">
    <input type="hidden" name="updated_at" value="{{$ticket->pr->updated_at->translatedFormat('Y-m-d H:i:s')}}">
    
    <table class="table table-bordered table-sm" id="pr_table">
        <thead>
            <tr>
                <th width="2%">No</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Jenis Asset</th>
                <th width="50%">Informasi Nomor Asset</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ticket->pr->pr_detail ?? [] as $key=>$detail)
                <input type="hidden" name="item[{{$key}}][pr_detail_id]" value="{{$detail->id}}">
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $detail->name }}</td>
                    <td>{{ $detail->qty }}</td>
                    <td>{{ ($detail->isAsset) ? "Asset" : "Non Asset" }}</td>
                    <td class="text-justify">
                        <div class="d-flex flex-column asset_field">
                            <div class="form-group text-nowrap">
                                <label>Nomor Asset</label>
                                <textarea type="text" class="form-control assetnumber_input" 
                                placeholder="cth: nomorasset1, nomorasset2, nomorasset3" 
                                name="item[{{$key}}][asset_numbers]">{{$detail->asset_numbers_list_text}}</textarea>
                                <small class="form-text text-muted">Masukkan nomor asset, jika ada beberapa nomor asset pisahkan dengan karakter koma (,)</small>
                            </div>
                            <div class="form-group">
                              <label>Upload File Nomor Asset</label>
                              <input type="file" class="form-control-file assetnumber_input" name="item[{{$key}}][asset_numbers_file]">
                              <small class="form-text text-muted">Pilih File Kelengkapan berisi informasi nomor asset item terkait</small>
                            </div>  
                            @if ($detail->asset_number_filepath)
                                <a href="#" onclick="window.open('/storage/{{$detail->asset_number_filepath}}')">
                                    lampiran nomor asset sebelumnya
                                </a>
                            @endif             
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="form-group">
      <label class="required_field">Alasan Update</label>
      <textarea class="form-control" name="reason" rows="5" required placeholder="Masukan Alasan update nomor asset. (Alasan akan ditampilkan pada monitoring ticket)"></textarea>
    </div>
    <center>
        <button type="submit" class="btn btn-primary">Update Nomor Asset</button><br>
        <small class="text-danger">*item non asset akan menjadi asset jika nomor asset / file asset ditambahkan</small>
    </center>
</form>
@endsection
@section('local-js')
<script>
</script>
@endsection
