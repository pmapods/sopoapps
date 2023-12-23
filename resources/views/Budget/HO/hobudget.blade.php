@extends('Layout.app')
@section('local-css')
@endsection

@section('content')

    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">HO Budget @if (request()->get('status') == -1)
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
                        <li class="breadcrumb-item active">HO Budget @if (request()->get('status') == -1)
                                (History)
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                @if (request()->get('status') == -1)
                    <a href="/ho_budget" class="btn btn-success">Budget Aktif</a>
                @else
                    @if (((Auth::user()->menu_access->budget ?? 0) & 16) != 0)
                        <a href="/ho_budget/monitoring" class="btn btn-warning">Monitoring</a>
                    @endif
                    <a href="/ho_budget?status=-1" class="btn btn-info ml-2">History</a>
                    <a href="/ho_budget/create" class="btn btn-primary ml-2">Tambah Budget Baru</a>
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
                        <th>Divisi</th>
                        <th>Tahun</th>
                        <th>Tanggal Dibuat</th>
                        <th>Nama Pengaju</th>
                        @if (request()->get('status') == -1)
                            <th>Alasan Expired</th>
                        @else
                            <th>Status</th>
                        @endif
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
                            <td>{{ $budget->code }}</td>
                            <td>{{ $budget->salespoint->name }}</td>
                            <td>{{ $budget->division }}</td>
                            <td>{{ $budget->year }}</td>
                            <td>{{ $budget->created_at->translatedFormat('d F Y') }}</td>
                            <td>{{ $budget->created_by_employee->name }}</td>
                            @if (request()->get('status') == -1)
                                <td>
                                    {{ $budget->reject_notes ?? '-' }}<br>
                                    <b>Oleh :</b>{{ $budget->rejected_by_employee->name ?? '-' }}
                                </td>
                            @else
                                <td>{{ $budget->status() }}</td>
                            @endif

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
                                    <a class="btn btn-danger" href="/ho_budget/nonActiveBudget/{{ $budget->code }}">
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
                window.location.href = "/ho_budget/" + code;
            });
        })
    </script>
@endsection
