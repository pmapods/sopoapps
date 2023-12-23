@extends('Layout.app')
@section('local-css')
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Migrasi Karyawan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Masterdata</li>
                        <li class="breadcrumb-item">Karyawan</li>
                        <li class="breadcrumb-item active">Migrasi Karyawan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body px-4">
        <h4>{{ $source_employee->name }} >> {{ $target_employee->name }}</h4>
        <div class="row pt-3">
            <div class="col-12 border p-2">
                <h5>Hak Akses Salespoint</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Salespoint</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($master_location_access as $location_access)
                            <tr>
                                <td>{{ $location_access->salespoint->region_name() }}</td>
                                <td>{{ $location_access->salespoint->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-12 border p-2">
                <h5>Master Matriks Approval Form</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Salespoint</th>
                            <th>Jenis Pengadaan</th>
                            <th>Author</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($master_authorizations as $authorization)
                            <tr>
                                @if ($authorization->salespoint_id == 'west')
                                    <td>West</td>
                                @elseif ($authorization->salespoint_id == 'indirect')
                                    <td>Indirect</td>
                                @elseif($authorization->salespoint_id == 'east')
                                    <td>East</td>
                                @elseif($authorization->salespoint_id == 'all')
                                    <td>All</td>
                                @else
                                    <td>{{ $authorization->salespoint->name }}</td>
                                @endif

                                <td>{{ $authorization->form_type_name() }}</td>
                                <td>
                                    @php
                                        $list = implode(',', array_column(json_decode($authorization->list()), 'name'));
                                    @endphp
                                    {{ $list }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-12 border p-2">
                <h5>Approval {{ $source_employee->name }} saat ini</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Sales Point</th>
                            <th>Kode Pengadaan</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nama Pengaju</th>
                            <th>Jenis Transaksi</th>
                            <th>Status Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($current_authorizations as $author)
                            <tr>
                                <td>{{ $author->salespoint }}</td>
                                <td>{{ $author->code }}</td>
                                <td>{{ $author->created_at }}</td>
                                <td>{{ $author->created_by }}</td>
                                <td>{{ $author->transaction_type }}</td>
                                <td>{{ $author->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-12 p-2 text-center">
                <button type="button" class="btn btn-lg btn-primary" onclick="doMigrateEmployee(this)">Migrasikan
                    Karyawan</button>
            </div>
        </div>
    </div>
    <form action="/employee/migrate" method="post" id="submitform">
        @csrf
        <div>
            <input type="hidden" name="source_employee_id" value="{{ $source_employee->id }}">
            <input type="hidden" name="target_employee_id" value="{{ $target_employee->id }}">
        </div>
    </form>
@endsection
@section('local-js')
    <script>
        function doMigrateEmployee(el) {
            if (confirm('Apakah anda yakin untuk melakukan migrasi karyawan. Lanjutkan?')) {
                $('#submitform').submit();
            }
        }
    </script>
@endsection
