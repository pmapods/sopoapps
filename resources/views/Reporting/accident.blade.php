@extends('Layout.app')
@section('local-css')

<style>
    #pills-tab .nav-link{
        background-color: #a01e2b48;
        color: black !important;
    }
    #pills-tab .nav-link.active{
        background-color: #A01E2A;
        color: white !important;
    }
</style>
@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Accident</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Reporting</li>
                    <li class="breadcrumb-item active">Accident</li>
                </ol>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addArmadaModal">
                Tambah Report Baru
            </button>
        </div>
    </div>
</div>

<div class="content-body px-4">
    <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
        <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
          <a class="nav-link active" id="pills-armada-tab" data-toggle="pill" href="#pills-armada" role="tab" aria-controls="pills-armada" aria-selected="true">ARMADA</a>
        </li>
        <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
          <a class="nav-link" id="pills-security-tab" data-toggle="pill" href="#pills-security" role="tab" aria-controls="pills-security" aria-selected="false">SECURITY</a>
        </li>
        <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
          <a class="nav-link" id="pills-cit-tab" data-toggle="pill" href="#pills-cit" role="tab" aria-controls="pills-cit" aria-selected="false">CIT</a>
        </li>
        <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
          <a class="nav-link" id="pills-pest-tab" data-toggle="pill" href="#pills-pest" role="tab" aria-controls="pills-pest" aria-selected="false">PEST CONTROL</a>
        </li>
        <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
          <a class="nav-link" id="pills-merchandiser-tab" data-toggle="pill" href="#pills-merchandiser" role="tab" aria-controls="pills-merchandiser" aria-selected="false">MERCHANDISER</a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-armada" role="tabpanel" aria-labelledby="pills-armada-tab">
            <table id="armadaDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint</th>
                        <th>Tanggal Accident</th>
                        <th>Deskripsi</th>
                        <th>File Pendukung</th>
                        <th>Nopol</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp
                    @foreach ($accidents->where('type','armada') as $accident)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $accident->salespoint->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($accident->accident_date)->translatedFormat('d F Y')}}</td>
                            <td>{{ $accident->description }}</td>
                            <td>
                                @if ($accident->filepath)
                                    <a style="cursor:pointer" class="text-primary" onclick="window.open('/storage/{{ $accident->filepath }}')">Tampilkan File</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $accident->plate }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
            <table id="securityDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint</th>
                        <th>Tanggal Accident</th>
                        <th>Deskripsi</th>
                        <th>File Pendukung</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp
                    @foreach ($accidents->where('type','security') as $accident)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $accident->salespoint->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($accident->accident_date)->translatedFormat('d F Y')}}</td>
                            <td>{{ $accident->description }}</td>
                            <td>
                                @if ($accident->filepath)
                                    <a style="cursor:pointer" class="text-primary" onclick="window.open('/storage/{{ $accident->filepath }}')">Tampilkan File</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $accident->vendor->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="pills-cit" role="tabpanel" aria-labelledby="pills-cit-tab">
            <table id="citDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint</th>
                        <th>Tanggal Accident</th>
                        <th>Deskripsi</th>
                        <th>File Pendukung</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp
                    @foreach ($accidents->where('type','cit') as $accident)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $accident->salespoint->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($accident->accident_date)->translatedFormat('d F Y')}}</td>
                            <td>{{ $accident->description }}</td>
                            <td>
                                @if ($accident->filepath)
                                    <a style="cursor:pointer" class="text-primary" onclick="window.open('/storage/{{ $accident->filepath }}')">Tampilkan File</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $accident->vendor_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="pills-pest" role="tabpanel" aria-labelledby="pills-pest-tab">
            <table id="pestDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint</th>
                        <th>Tanggal Accident</th>
                        <th>Deskripsi</th>
                        <th>File Pendukung</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp
                    @foreach ($accidents->where('type','pest_control') as $accident)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $accident->salespoint->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($accident->accident_date)->translatedFormat('d F Y')}}</td>
                            <td>{{ $accident->description }}</td>
                            <td>
                                @if ($accident->filepath)
                                    <a style="cursor:pointer" class="text-primary" onclick="window.open('/storage/{{ $accident->filepath }}')">Tampilkan File</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $accident->vendor_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="pills-merchandiser" role="tabpanel" aria-labelledby="pills-merchandiser-tab">
            <table id="merchandiserDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>SalesPoint</th>
                        <th>Tanggal Accident</th>
                        <th>Deskripsi</th>
                        <th>File Pendukung</th>
                        <th>Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp
                    @foreach ($accidents->where('type','merchandiser') as $accident)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $accident->salespoint->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($accident->accident_date)->translatedFormat('d F Y')}}</td>
                            <td>{{ $accident->description }}</td>
                            <td>
                                @if ($accident->filepath)
                                    <a style="cursor:pointer" class="text-primary" onclick="window.open('/storage/{{ $accident->filepath }}')">Tampilkan File</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $accident->vendor_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="addArmadaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="/accident/create" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Report Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                <label class="required_field">Tanggal</label>
                                <input type="date" class="form-control" name="date" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                <label class="required_field">Pilih Report</label>
                                <select class="form-control" name="type" required id="report_type">
                                    <option value="armada">ARMADA</option>
                                    <option value="security">SECURITY</option>
                                    <option value="cit">CIT</option>
                                    <option value="pest_control">PEST CONTROL</option>
                                    <option value="merchandiser">MERCHANDISER</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                <label class="required_field">Deskripsi</label>
                                <textarea class="form-control" 
                                    name="description" rows="3"
                                    required
                                    style="resize: none"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                <label class="optional_field">Upload File pendukung</label>
                                <input type="file" class="form-control-file validatefilesize" 
                                accept="image/*,application/pdf"
                                name="support_file">
                                <small class="text-danger">*max 5MB (pdf, jpg, png, jpeg)</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                <label class="required_field">Salespoint</label>
                                    <select class="form-control select2" name="salespoint_id" required>
                                        <option value="">-- Pilih Salespoint --</option>
                                        @foreach ($salespoints as $salespoint)
                                            <option  
                                                value="{{ $salespoint->id }}">{{ $salespoint->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 option_field armada_field">
                                <div class="form-group">
                                <label class="required_field">Nopol</label>
                                    <select class="form-control select2" name="armada_id">
                                        <option value="">-- Pilih Armada --</option>
                                        @foreach ($armadas as $armada)
                                            <option value="{{ $armada->id }}">{{ $armada->plate }} -- {{ $armada->armada_type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 option_field security_field">
                                <div class="form-group">
                                <label class="required_field">Vendor Security</label>
                                    <select class="form-control select2" name="vendor_id">
                                        <option value="">-- Pilih Vendor --</option>
                                        @foreach ($security_vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 option_field cit_field pest_field merchandiser_field">
                                <div class="form-group">
                                <label class="required_field">Vendor</label>
                                    <input type="text" class="form-control" name="vendor" placeholder="Masukan nama vendor">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        var armadaDT        = $('#armadaDT').DataTable(datatable_settings);
        var securityDT      = $('#securityDT').DataTable(datatable_settings);
        var citDT           = $('#citDT').DataTable(datatable_settings);
        var pestDT          = $('#pestDT').DataTable(datatable_settings);
        var merchandiserDT  = $('#merchandiserDT').DataTable(datatable_settings);
        $('.validatefilesize').change(function(event){
            if(!validatefilesize(event)){
                $(this).val('');
            }
        });
        $('#report_type').change(function(event){
            $('.option_field').hide();
            $('.option_field input, .option_field select').prop('disabled', true).prop('required',false);
            switch ($(this).val()) {
                case 'armada':
                    $('.armada_field').show();
                    $('.armada_field input, .armada_field select').prop('disabled', false).prop('required',true);
                    break;
                case 'security':
                    $('.security_field').show();
                    $('.security_field input, .security_field select').prop('disabled', false).prop('required',true);
                    break;
                case 'cit':
                    $('.cit_field').show();
                    $('.cit_field input, .cit_field select').prop('disabled', false).prop('required',true);
                    break;
                case 'pest_control':
                    $('.pest_field').show();
                    $('.pest_field input, .pest_field select').prop('disabled', false).prop('required',true);
                    break;
                case 'merchandiser':
                    $('.merchandiser_field').show();
                    $('.merchandiser_field input, .merchandiser_field select').prop('disabled', false).prop('required',true);
                    break;
                default:
                    break;
            }
        });
        $('#report_type').trigger('change');

        var type = getUrlVars()["type"];
        switch (type) {
            case "armada":
                $('#pills-armada-tab').click();
                break;
            case "security":
                $('#pills-security-tab').click();
                break;
            case "cit":
                $('#pills-cit-tab').click();
                break;
            case "pest_control":
                $('#pills-pest-tab').click();
                break;
            case "merchandiser":
                $('#pills-merchandiser-tab').click();
                break;
            default:
                break;
        }
    });
</script>
@endsection
