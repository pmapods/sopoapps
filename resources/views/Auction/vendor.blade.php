@extends('Layout.auction')
@section('local-css')
    <style>
        #pills-tab .nav-link {
            background-color: #a01e2b48;
            color: black !important;
        }

        #pills-tab .nav-link.active {
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
                    <h1 class="m-0 text-dark">Auction Ticket</h1>
                </div>
                <!-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item active">Auction</li>
                    </ol>
                </div> -->
            </div>
            <!-- <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAuctionModal"
                    id="addbutton">
                    Tambah Auction
                </button>
            </div> -->
        </div>
    </div>
    <div class="content-body px-4">
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-vendor-tab" data-toggle="pill" href="#pills-vendor"
                    role="tab" aria-controls="pills-vendor" aria-selected="true">Vendor Register</a>
            </li>
            <!-- <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-armada-tab" data-toggle="pill" href="#pills-armada" role="tab"
                    aria-controls="pills-armada" aria-selected="false">Armada</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-security-tab" data-toggle="pill" href="#pills-security" role="tab"
                    aria-controls="pills-security" aria-selected="false">Security</a>
            </li> -->
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-vendor" role="tabpanel"
                aria-labelledby="pills-vendor-tab">
                <div class="table-responsive">
                    <table id="vendorDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Type</th>
                                <th>Vendor Company Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1 @endphp
                            @foreach ($vendors->where('status', 0) as $key => $vendor)
                                <tr data-vendor="{{ $vendor }}">
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $vendor->code }}</td>
                                    <td>{{ $vendor->name }}</td>
                                    <td>{{ $vendor->type }}</td>
                                    <td>{{ $vendor->vendor_code_ref }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#vendorDT').DataTable(datatable_settings);
            $('#vendorDT tbody').on('click', 'tr', function() {
                let data = $(this).data('vendor_login');
                modal.find('input[name="id"]').val(data['id']);
                modal.find('input[name="code"]').val(data['code']);
                modal.find('select[name="type"]').val(data['type']);
                modal.find('input[name="name"]').val(data['name']);
                modal.find('input[name="vendor_code_ref"]').val(data['vendor_code_ref']);
                modal.modal('show');
            });
        });
    </script>
@endsection
