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
                <h1 class="m-0 text-dark">Monitoring Inventory Budget</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Budget</li>
                    <li class="breadcrumb-item">Inventory Budget</li>
                    <li class="breadcrumb-item active">Monitoring</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-end px-4">
    <a href="/inventorybudget/monitoring/export" class="btn btn-primary">Export Data</a>
</div>
<div class="content-body px-4 mt-2">
    <ul class="nav nav-pills flex-column flex-sm-row mb-3" id="pills-tab" role="tablist">
        <li class="flex-sm-fill text-sm-center nav-item mr-1" role="presentation">
            <a class="nav-link active" id="pills-active-tab" data-toggle="pill" href="#pills-active" role="tab"
                aria-controls="pills-active" aria-selected="true">Aktif</a>
        </li>
        <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
            <a class="nav-link" id="pills-pending-tab" data-toggle="pill" href="#pills-pending" role="tab"
                aria-controls="pills-pending" aria-selected="false">Pending</a>
        </li>
        <li class="flex-sm-fill text-sm-center nav-item ml-1" role="presentation">
            <a class="nav-link" id="pills-noupload-tab" data-toggle="pill" href="#pills-noupload" role="tab"
                aria-controls="pills-noupload" aria-selected="false">No Budget</a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-active" role="tabpanel"
            aria-labelledby="pills-active-tab">
            <div class="table-responsive">
                <table class="table table-bordered dataTable table-sm" role="grid" id="activeDT">
                    <thead>
                        <tr role="row">
                            <th>#</th>
                            <th>Nama Salespoint</th>
                            <th>Region</th>
                            <th>Kode Budget</th>
                            <th>Budget Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1 @endphp
                        @foreach($activeBudget as $budget)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $budget->salespoint->name }}</td>
                                <td>{{ $budget->salespoint->region_name() }}({{ $budget->salespoint->status_name() }})</td>
                                <td>{{ $budget->code }}</td>
                                <td>{{ $budget->created_at->translatedFormat('d F Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade show" id="pills-pending" role="tabpanel"
            aria-labelledby="pills-pending-tab">
            <div class="table-responsive">
                <table class="table table-bordered dataTable table-sm" role="grid" id="pendingDT">
                    <thead>
                        <tr role="row">
                            <th>#</th>
                            <th>Nama Salespoint</th>
                            <th>Region</th>
                            <th>Kode Budget</th>
                            <th>Budget Dibuat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1 @endphp
                        @foreach($pendingBudget as $budget)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $budget->salespoint->name }}</td>
                                <td>{{ $budget->salespoint->region_name() }}({{ $budget->salespoint->status_name() }})</td>
                                <td>{{ $budget->code }}</td>
                                <td>{{ $budget->created_at->translatedFormat('d F Y') }}</td>
                                <td>{{ $budget->status() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade show" id="pills-noupload" role="tabpanel"
            aria-labelledby="pills-noupload-tab">
            <div class="table-responsive">
                <table class="table table-bordered dataTable table-sm" role="grid" id="nouploadDT">
                    <thead>
                        <tr role="row">
                            <th>#</th>
                            <th>Nama Salespoint</th>
                            <th>Region</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1 @endphp
                        @foreach($noBudgetSalespoint as $salespoint)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $salespoint->name }}</td>
                                <td>{{ $salespoint->region_name() }}</td>
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
    $(document).ready(function () {
        var activetable = $('#activeDT').DataTable(datatable_settings);
        var pendingtable = $('#pendingDT').DataTable(datatable_settings);
        var nouploadtable = $('#nouploadDT').DataTable(datatable_settings);
    })

</script>
@endsection
