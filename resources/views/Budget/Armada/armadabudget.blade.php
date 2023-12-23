@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Armada Budget @if (request()->get('status') == -1)
                            (History)
                        @endif
                    </h1>
                    <div class="text-info">* Data yang ditampilkan berdasarkan hak akses area.
                        <a class="font-weight-bold" href="/myaccess">Cek Disini</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Budget</li>
                        <li class="breadcrumb-item active">Armada Budget @if (request()->get('status') == -1)
                                (History)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                @if (request()->get('status') == -1)
                    <a href="/armadabudget" class="btn btn-success">Budget Aktif</a>
                @else
                    @if (((Auth::user()->menu_access->budget ?? 0) & 16) != 0)
                        <a href="/armadabudget/monitoring" class="btn btn-warning">Monitoring</a>
                    @endif
                    <a href="/armadabudget?status=-1" class="btn btn-info ml-2">History (Budget Non Aktif)</a>
                    <a href="/armadabudget/create" class="btn btn-primary ml-2">Tambah Budget Baru</a>
                @endif
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <div class="table-responsive">
            <table id="budgetDT" class="table table-bordered table-striped dataTable" role="grid">
                <thead>
                    <tr role="row">
                        <th>#</th>
                        <th>Kode Upload</th>
                        <th>Nama Salespoint</th>
                        <th>Tanggal Permintaan</th>
                        <th>Nama Pengaju</th>
                        <th>Tahun</th>
                        @if (request()->get('status') == -1)
                            <th width="25%">Alasan Expired</th>
                        @else
                            <th width="10%">Status</th>
                        @endif
                        <th>Waktu Dibuat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $validate_year = now()->format('Y');
                    @endphp
                    @foreach ($budgets as $key => $budget)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td class="text-nowrap">{{ $budget->code }}</td>
                            <td>{{ $budget->salespoint->name }}</td>
                            <td>{{ $budget->created_at->format('Y-m-d') }}</td>
                            <td>{{ $budget->created_by_employee->name }}</td>
                            <td>{{ $budget->year ?? '-' }}</td>
                            @if (request()->get('status') == -1)
                                <td>
                                    {{ $budget->reject_notes ?? '-' }}<br>
                                    <b>Oleh :</b>{{ $budget->rejected_by_employee->name ?? '-' }}
                                </td>
                            @else
                                <td>{{ $budget->status() }}</td>
                            @endif
                            <td class="text-nowrap">{{ $budget->created_at->translatedFormat('d F Y (H:i)') }}</td>

                            @if ($budget->status == 2 || $budget->status == -1)
                                <td>
                                    <button type="button" class="btn btn-danger" disabled> Non Aktifkan Budget
                                    </button>
                                </td>
                            @elseif (
                                ($budget->year == $validate_year && $budget->status == 0) ||
                                    ($budget->year == $validate_year && $budget->status == 1))
                                <td>
                                    <button type="button" class="btn btn-danger" disabled> Non Aktifkan Budget
                                    </button>
                                </td>
                            @elseif (
                                ($budget->year != $validate_year && $budget->status == 1) ||
                                    ($budget->year != $validate_year && $budget->status == 0))
                                <td>
                                    <a class="btn btn-danger" href="/armadabudget/nonActiveBudget/{{ $budget->code }}">
                                        Non Aktifkan Budget</a>
                                </td>
                            @endif
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
            var table = $('#budgetDT').DataTable(datatable_settings);
            $('#budgetDT tbody').on('click', 'tr', function() {
                let code = $(this).find('td').eq(1).text().trim();
                window.location.href = "/armadabudget/" + code;
            });
        })
    </script>
@endsection
