@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Vendor Evaluation @if (request()->get('status') == 3)
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
                        <li class="breadcrumb-item active">Vendor Evaluation @if (request()->get('status') == 3)
                                (History)
                            @endif
                        </li>

                    </ol>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-1">
            <a class="btn btn-primary" href="/vendor-evaluation/create" role="button">Create Vendor Evaluation</a>
            @if (request()->get('status') == 3)
                <a href="/vendor-evaluation" class="btn btn-primary ml-2">Vendor Evaluation Aktif</a>
            @else
                <a href="/vendor-evaluation?status=3" class="btn btn-info ml-2">History</a>
            @endif
        </div>
    </div>

    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="vendorDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>Kode</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Pembuat Form</th>
                        <th>Area</th>
                        <th>Vendor</th>
                        <th>Start Periode Penilaian</th>
                        <th>End Periode Penilaian</th>
                        <th width="25%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vendors as $key => $vendor)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">{{ $vendor->code }}</td>
                            <td>{{ $vendor->created_at->translatedFormat('d F Y') }}</td>
                            <td>{{ $vendor->created_by_employee->name ?? '-' }}</td>
                            <td>{{ $vendor->salespoint->name }}</td>
                            @if ($vendor->vendor == 0)
                                <td>Pest Control</td>
                            @elseif ($vendor->vendor == 1)
                                <td>CIT</td>
                            @elseif ($vendor->vendor == 2)
                                <td>Si Cepat</td>
                            @elseif ($vendor->vendor == 3)
                                <td>Ekspedisi</td>
                            @endif
                            <td>{{ \Carbon\Carbon::parse($vendor->start_periode_penilaian)->translatedFormat('d F Y') }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($vendor->end_periode_penilaian)->translatedFormat('d F Y') }}</td>
                            <td class="small">
                                @php
                                    $authorization = $vendor->current_authorization();
                                    $rejected_by = '';
                                @endphp

                                @foreach ($vendors->where('status', 0) as $key => $item)
                                    @php
                                        $rejected_by = $item->rejected_by_employee->name;
                                        $reason = $item->reason;
                                    @endphp
                                @endforeach

                                @foreach ($vendors->where('status', 4) as $key => $item)
                                    @php
                                        $rejected_by = $item->rejected_by_employee->name;
                                        $reason = $item->reason;
                                    @endphp
                                @endforeach

                                @if ($authorization && $vendor->status == 2)
                                    Menunggu approval dari {{ $authorization->employee_name }}
                                @elseif ($vendor->status == 1)
                                    Menunggu pembuatan form evaluasi vendor
                                @elseif ($vendor->status == 3)
                                    Vendor evaluasi selesai
                                @elseif ($vendor->status == 0)
                                    <div>
                                        Vendor evaluasi di reject oleh :{{ $rejected_by }}
                                    </div>
                                    <div>
                                        Alasan : {{ $reason }}
                                    </div>
                                @elseif ($vendor->status == 4)
                                    <div>
                                        Vendor evaluasi di Batalkan oleh :{{ $rejected_by }}
                                    </div>
                                    <div>
                                        Alasan : {{ $reason }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('local-js')
    <script>
        $(document).ready(function() {
            var table = $('#vendorDT').DataTable(datatable_settings);
            $('#vendorDT tbody').on('click', 'tr', function() {
                let code = $(this).find('td').eq(1).text().trim();
                window.location.href = "/vendor-evaluation/" + code;
            });
        })
    </script>
@endsection
