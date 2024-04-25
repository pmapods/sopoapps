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
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Purchase Requisition @if (request()->get('status') == -1)
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
                        <li class="breadcrumb-item active">
                            Purchase Requisition @if (request()->get('status') == -1)
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
                <a href="/pr" class="btn btn-primary ml-2">PR dalam Proses</a>
            @else
                <a href="/pr?status=-1" class="btn btn-info ml-2">History</a>
            @endif
        </div>
        <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
            <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
                <a class="nav-link active" id="pills-barangjasa-tab" data-toggle="pill" href="#pills-barangjasa"
                    role="tab" aria-controls="pills-barangjasa" aria-selected="true">Barang Jasa</a>
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
                <div class="table-responsive mt-2">
                    <table id="barangjasaDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode Tiket</th>
                                <th>SalesPoint</th>
                                <th>Tanggal Permintaan</th>
                                <th>List Item</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pengadaantickets as $key => $ticket)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $ticket->code }}</td>
                                    <td>{{ $ticket->salespoint->name }}</td>
                                    <td>{{ $ticket->created_at->translatedFormat('d F Y (H:i)') }}</td>
                                    <td class="small" style="white-space: pre-line !important;">
                                        {{ implode(",\n", $ticket->ticket_item->pluck('name')->toArray()) }}</td>
                                    <td class="small">
                                        {{ $ticket->status() }}
                                        @if (isset($ticket->pr) && ($ticket->pr->status ?? '') == -1)
                                            <br><span class="text-danger">Approval ditolak oleh
                                                <b>{{ $ticket->pr->rejected_by_employee()->name }}</b><br>
                                                Alasan :{{ $ticket->pr->reject_reason }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-armada" role="tabpanel" aria-labelledby="pills-armada-tab">
                <div class="table-responsive mt-2">
                    <table id="armadaDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode Tiket</th>
                                <th>SalesPoint</th>
                                <th>Tanggal Permintaan</th>
                                <th>Jenis Armada</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($armadatickets as $key => $ticket)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $ticket->code }}</td>
                                    <td>{{ $ticket->salespoint->name }}</td>
                                    <td>{{ $ticket->created_at->translatedFormat('d F Y (H:i)') }}</td>
                                    <td>{{ $ticket->armada_type->name }} {{ $ticket->armada_type->brand_name }}
                                        @if ($ticket->armada_type->isSBH == 1)
                                            ({{ $ticket->armada_type->isSBH() }})
                                        @endif
                                    </td>
                                    <td>
                                        {{ $ticket->status() }}
                                        @if (isset($ticket->pr) && ($ticket->pr->status ?? '') == -1)
                                            <br><span class="text-danger">Approval ditolak oleh
                                                <b>{{ $ticket->pr->rejected_by_employee()->name }}</b><br>
                                                Alasan :{{ $ticket->pr->reject_reason }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                <div class="table-responsive mt-2">
                    <table id="securityDT" class="table table-bordered table-striped dataTable" role="grid">
                        <thead>
                            <tr role="row">
                                <th>#</th>
                                <th>Kode Tiket</th>
                                <th>SalesPoint</th>
                                <th>Tanggal Permintaan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($securitytickets as $key => $ticket)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $ticket->code }}</td>
                                    <td>{{ $ticket->salespoint->name }}</td>
                                    <td>{{ $ticket->created_at->translatedFormat('d F Y (H:i)') }}</td>
                                    <td>
                                        {{ $ticket->status() }}
                                        @if (isset($ticket->pr) && ($ticket->pr->status ?? '') == -1)
                                            <br><span class="text-danger">Approval ditolak oleh
                                                <b>{{ $ticket->pr->rejected_by_employee()->name }}</b><br>
                                                Alasan :{{ $ticket->pr->reject_reason }}</span>
                                        @endif
                                    </td>
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
            $('#barangjasaDT').DataTable(datatable_settings);
            $('#armadaDT').DataTable(datatable_settings);
            $('#securityDT').DataTable(datatable_settings);
            $('#barangjasaDT tbody').on('click', 'tr', function() {
                window.location.href = '/pr/' + $(this).find('td:eq(1)').text().trim();
            });
            $('#armadaDT tbody').on('click', 'tr', function() {
                window.location.href = '/pr/' + $(this).find('td:eq(1)').text().trim();
            });
            $('#securityDT tbody').on('click', 'tr', function() {
                window.location.href = '/pr/' + $(this).find('td:eq(1)').text().trim();
            });
        })
    </script>
@endsection
