@extends('Layout.app')
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
        #barangjasaDT tbody td:nth-child(2),#armadaDT tbody td:nth-child(2),#securityDT tbody td:nth-child(2){
            white-space: nowrap;
        }
        #barangjasaDT tbody td:nth-child(8),#armadaDT tbody td:nth-child(8),#securityDT tbody td:nth-child(8){
            white-space: pre-line !important;
        }
        #barangjasaDT tbody td:nth-child(9),#armadaDT tbody td:nth-child(9),#securityDT tbody td:nth-child(9){
            white-space: pre-line !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Purchase Order @if (request()->get('status') == -1)
                            (History)
                        @endif
                    </h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Operasional</li>
                        <li class="breadcrumb-item active">Purchase Order @if (request()->get('status') == -1)
                                (History)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="d-flex justify-content-end mb-2">
            @if (request()->get('status') == -1)
                <a href="/po" class="btn btn-primary ml-2">PO dalam Proses</a>
            @else
                <a href="/po?status=-1" class="btn btn-info ml-2">History</a>
            @endif
        </div>
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-barangjasa-tab" data-toggle="pill" href="#pills-barangjasa" role="tab"
                    aria-controls="pills-barangjasa" aria-selected="true">Barang Jasa</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-armada-tab" data-toggle="pill" href="#pills-armada" role="tab"
                    aria-controls="pills-armada" aria-selected="false">Armada</a>
            </li>
            <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
                <a class="nav-link" id="pills-security-tab" data-toggle="pill" href="#pills-security" role="tab"
                    aria-controls="pills-security" aria-selected="false">Security</a>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-barangjasa" role="tabpanel"
                aria-labelledby="pills-barangjasa-tab">
                <div class="table-responsive">
                    <table id="barangjasaDT" class="table table-sm small table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode Tiket</th>
                                <th>Tipe Pengadaan</th>
                                <th>SalesPoint</th>
                                <th>Vendor</th>
                                <th>Daftar PO</th>
                                <th>Tanggal Permintaan</th>
                                <th>List Item</th>
                                <th width="30%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @php $count = 1; @endphp
                            @foreach ($barangjasatickets as $key => $ticket)
                                @php
                                    $isView = false;
                                    if (((Auth::user()->menu_access->operational ?? 0) & 8) != 0 && $ticket->status() == 'Menunggu Setup PO') {
                                        $isView = true;
                                    }
                                    if (((Auth::user()->menu_access->operational ?? 0) & 16) != 0 && $ticket->status() != 'Menunggu Setup PO') {
                                        $isView = true;
                                    }
                                @endphp
                                @if ($isView)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $ticket->code }}</td>
                                        <td>{{ $ticket->item_type() }} {{ $ticket->request_type() }}</td>
                                        <td>{{ $ticket->salespoint->name }}</td>
                                        <td>{{ (count($ticket->po_array_list())>0) ? (implode(', ', $ticket->po_array_list())) : '-' }}</td>
                                        <td>{{ $ticket->created_at->translatedFormat('d F Y (H:i)') }}</td>
                                        <td style="white-space: pre-line;">{{ $ticket->status("complete") }}</td>
                                    </tr>
                                @endif
                            @endforeach --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-armada" role="tabpanel" aria-labelledby="pills-armada-tab">
                <div class="table-responsive">
                    <table id="armadaDT" class="table table-sm small table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode Tiket</th>
                                <th>Tipe Pengadaan</th>
                                <th>SalesPoint</th>
                                <th>Vendor</th>
                                <th>Daftar PO</th>
                                <th>Tanggal Permintaan</th>
                                <th>Unit Armada</th>
                                <th width="30%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @php $count = 1; @endphp
                            @foreach ($armadatickets as $key => $ticket)
                                @php
                                    $isView = false;
                                    if (((Auth::user()->menu_access->operational ?? 0) & 8) != 0 && $ticket->status() == 'Menunggu Setup PO') {
                                        $isView = true;
                                    }
                                    if (((Auth::user()->menu_access->operational ?? 0) & 16) != 0 && $ticket->status() != 'Menunggu Setup PO') {
                                        $isView = true;
                                    }
                                @endphp
                                @if ($isView)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $ticket->code }}</td>
                                        <td>{{ $ticket->type() }}</td>
                                        <td>{{ $ticket->salespoint->name }}</td>
                                        <td>{{ (count($ticket->po_array_list())>0) ? (implode(', ', $ticket->po_array_list())) : '-' }}</td>
                                        <td>{{ $ticket->created_at->translatedFormat('d F Y (H:i)') }}</td>
                                        <td style="white-space: pre-line;">{{ $ticket->status("complete") }}</td>
                                    </tr>
                                @endif
                            @endforeach --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                <div class="table-responsive">
                    <table id="securityDT" class="table table-sm small table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode Tiket</th>
                                <th>Tipe Pengadaan</th>
                                <th>SalesPoint</th>
                                <th>Vendor</th>
                                <th>Daftar PO</th>
                                <th>Tanggal Permintaan</th>
                                <th width="30%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @php $count = 1; @endphp
                            @foreach ($securitytickets as $key => $ticket)
                                @php
                                    $isView = false;
                                    if (((Auth::user()->menu_access->operational ?? 0) & 8) != 0 && $ticket->status() == 'Menunggu Setup PO') {
                                        $isView = true;
                                    }
                                    if (((Auth::user()->menu_access->operational ?? 0) & 16) != 0 && $ticket->status() != 'Menunggu Setup PO') {
                                        $isView = true;
                                    }
                                @endphp
                                @if ($isView)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $ticket->code }}</td>
                                        <td>{{ $ticket->type() }}</td>
                                        <td>{{ $ticket->salespoint->name }}</td>
                                        <td>{{ (count($ticket->po_array_list())>0) ? (implode(', ', $ticket->po_array_list())) : '-' }}</td>
                                        <td>{{ $ticket->created_at->translatedFormat('d F Y (H:i)') }}</td>
                                        <td style="white-space: pre-line;">{{ $ticket->status("complete") }}</td>
                                    </tr>
                                @endif
                            @endforeach --}}
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
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status')
            // $('#barangjasaDT').DataTable(datatable_settings);
            let table = $('#barangjasaDT').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "/po/data?type=ticket&status="+status,
                "createdRow": function(row, data, dataIndex) {
                }
            });
            // $('#armadaDT').DataTable(datatable_settings);
            // $('#securityDT').DataTable(datatable_settings);
            
            let armadatable;
            let securitytable;
            let selected_array = [];
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (event) {
                if($(event.target).attr('href') == "#pills-armada" && !selected_array.includes("#pills-armada")){
                    selected_array.push("#pills-armada");
                    armadatable = $('#armadaDT').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": "/po/data?type=armada&status="+status,
                        "createdRow": function(row, data, dataIndex) {
                        }
                    });
                }
                if($(event.target).attr('href') == "#pills-security" && !selected_array.includes("#pills-security")){
                    // console.log("security triggered");
                    selected_array.push("#pills-security");
                    securitytable = $('#securityDT').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": "/po/data?type=security&status="+status,
                        "createdRow": function(row, data, dataIndex) {
                        }
                    });
                }
            });
            $('#barangjasaDT tbody').on('click', 'tr', function() {
                window.location.href = '/po/' + $(this).find('td:eq(1)').text().trim();
            });
            $('#armadaDT tbody').on('click', 'tr', function() {
                window.location.href = '/po/' + $(this).find('td:eq(1)').text().trim();
            });
            $('#securityDT tbody').on('click', 'tr', function() {
                window.location.href = '/po/' + $(this).find('td:eq(1)').text().trim();
            });
            
            // $('#barangjasaDT thead th,#armadaDT thead th,#securityDT thead th').each(function() {
            //     $(this).css('width', '');
            // });
        })
    </script>
@endsection
