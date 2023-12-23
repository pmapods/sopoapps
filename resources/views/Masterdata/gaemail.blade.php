@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header py-1">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">GA Team Email</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item">Additional Email</li>
                    <li class="breadcrumb-item active">GA Team Email</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-4">
    <div class="d-flex justify-content-end mb-3">
        <div width="20%">
            <select class="form-control" id="emailtype_selection">
                <option value="/additionalemail">Additional Email</option>
                <option value="/additionalemail/purchasing">Purchasing Team Email</option>
                <option selected value="/additionalemail/ga">GA Team Email</option>
            </select>
        </div>
    </div>
    <form action="/additionalemail/update" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            @php
                $splits = $emails->split(2);
                $count = 0;
            @endphp
            @foreach ($splits as $split)
                <div class="col-6">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Tipe</th>
                                <th width="60%">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($split as $email)
                                <tr>
                                    <td>{{ ucwords(str_replace('_',' ',$email->category)) }}</td>
                                    <td>{{ ucwords(str_replace('_',' ',$email->type)) }}</td>
                                    <td>
                                        @php
                                            $emails_text = "";
                                            if($email->emails != null){
                                                $array_email = json_decode($email->emails);
                                                $emails_text = implode(",\r\n", $array_email);
                                            }
                                        @endphp
                                        <textarea class="form-control" 
	                                       name="items[{{ $email->id }}][emails]" rows="5"
                                            style="resize:none">{{ $emails_text }}</textarea>
                                    </td>
                                </tr>
                                @php
                                    $count++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
            <div class="col-12 d-flex justify-content-center">
                <button type="submit" class="btn btn-info">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        $('#emailtype_selection').change(function() {
            window.location.href = $(this).val();
        });
    });
</script>
@endsection
