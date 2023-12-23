@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Armada Accident</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Reporting</li>
                    <li class="breadcrumb-item active">Armada Accident</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAccidentModal">
                Tambah Accident Baru
            </button>
        </div>
    </div>
</div>

<div class="content-body px-4">
    <table class="table table-bordered table-striped dataTable" role="grid" id="datatable">
        <thead>
            <tr>
                <th>No</th>
                <th>Salespoint</th>
                <th>Dibuat Oleh</th>
                <th>Waktu Dibuat</th>
                <th>Terakhir di update oleh</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1 @endphp
            @foreach ($armada_accidents as $armada_accident)
            <tr data-id = "{{ $armada_accident->id }}">
                <td>{{ $count++ }}</td>
                <td>{{ $armada_accident->salespoint->name }}</td>
                <td>{{ $armada_accident->createdBy->name }}</td>
                <td>{{ $armada_accident->created_at->format('d-m-Y') }}</td>
                <td>{{ $armada_accident->updatedBy->name ?? '-' }}</td>
                <td>{{ ucfirst($armada_accident->status()) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addAccidentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/armadaaccident/create" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Accident Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="required_field">Salespoint</label>
                                <select class="form-control select2" name="salespoint_id" required>
                                    <option value="">-- Pilih Salespoint --</option>
                                    @foreach ($salespoints as $salespoint)
                                        <option value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-danger">* salespoint yang muncul berdasarkan hak akses area</small>
                            </div>
                        </div>
                        <div class="col-6">
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                              <label class="required_field">Description</label>
                              <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Buat Accident</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var datatable  = $('#datatable').DataTable(datatable_settings);
        $('#datatable tbody').on('click', 'tr', function () {
            let id = $(this).data('id');
            window.location.href = "/armadaaccident/"+id;
        });
    });
</script>
@endsection
