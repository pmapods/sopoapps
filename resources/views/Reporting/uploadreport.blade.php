@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Upload Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Reporting</li>
                    <li class="breadcrumb-item active">Upload Report</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addReportModal">
                Tambah Report Baru
            </button>
            
            <div class="modal fade" id="addReportModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form action="/uploadreport/create" method="post">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tambah Report</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="required_field">Nama Report</label>
                                            <input type="text" class="form-control" name="name" maxlength="20" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="required_field">Deskripsi</label>
                                            <textarea class="form-control" name="description" rows="5" style="resize: none;" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary">Tambah</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>                                                  
        </div>
    </div>
</div>

<div class="content-body px-4">
    <table class="table table-bordered table-striped dataTable" role="grid" id="datatable">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Report</th>
                <th width="50%">Deskripsi</th>
                <th width="10%">Jumlah Report</th>
                <th width="15%">Waktu Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1 @endphp
            @foreach ($upload_reports as $upload_report)
            <tr data-id="{{$upload_report->id}}"
                data-list="{{$upload_report->list->toJson()}}">
                <td>{{ $count++ }}</td>
                <td>{{ $upload_report->name }}</td>
                <td>{{ $upload_report->description }}</td>
                <td>{{ $upload_report->list->count() ?? '-' }}</td>
                <td>{{ $upload_report->created_at->format('d F Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="detailmodal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body p-3">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th>File</th>
                            <th>Tanggal Upload</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <hr>
                <form action="/uploadreport/createfile" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="upload_report_id" value="-1">
                    <div class="row">
                        <div class="col-12">
                            <h5>Tambah File Baru</h5>
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control" name="description" placeholder="Masukan Deskripsi" maxlength="50" required>
                        </div>
                        <div class="col-4">
                            <input type="file" class="form-control-file" name="file" required>
                        </div>
                        <div class="col-2">
                            <button type="submit" class="btn btn-primary form-control">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var datatable  = $('#datatable').DataTable(datatable_settings);
        $('#datatable tbody').on('click', 'tr', function () {
            let upload_report_id = $(this).data('id');
            let name = $(this).find('td:eq(1)').text();
            let list = $(this).data('list');
            
            $('#detailmodal tbody').empty();
            if(list.length < 1) {
                $('#detailmodal tbody').append("<td colspan='2' class='text-center'>Belum ada data</td>");
            }else{
                list.forEach(item => {
                    let append_text = "<tr>";
                    append_text += "<td>"+item.description+"</td>";
                    append_text += "<td><a href='/storage/"+item.path+"'>link</a></td>";
                    append_text += "<td>"+item.created_at_format+"</td>";
                    append_text += "</tr>";
                    $('#detailmodal tbody').append(append_text);
                });
            }
            $('#detailmodal input[name="upload_report_id"]').val(upload_report_id);
            $('#detailmodal .modal-title').text(name);
            $('#detailmodal').modal('show');
        });
    });
</script>
@endsection
