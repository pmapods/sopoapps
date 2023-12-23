@extends('Layout.app')
@section('local-css')

@endsection

@section('content')
 <div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">My Access</h1>
            </div>
        </div>
    </div>
</div>
 
 <div class="content-body px-4">

     <div class="row row-cols-4">
         @if (count($current_account_location_access)<1)
            <div class="col">
                <h4>Area</h4>
                 Anda belum memiliki akses area
            </div>
        @else
            @foreach ($current_account_location_access as $list)
                <div class="col">
                    <h4>{{ $list->first()->region_name() }}</h5>
                    <ul>
                        @foreach ($list as $salespoint)
                            <li>{{ $salespoint->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif
     </div>
     <hr>
    @php
        $access_list = [
            'Masterdata' => [
                'submenus' => config('customvariable.masterdata_accesses'),
                'access' => Auth::user()->menu_access->masterdata ?? 0,
            ],
            'Budget' => [
                'submenus' => config('customvariable.budget_accesses'),
                'access' => Auth::user()->menu_access->budget ?? 0,
            ],
            'Operational' => [
                'submenus' => config('customvariable.operational_accesses'),
                'access' => Auth::user()->menu_access->operational ?? 0,
            ],
            'Monitoring' => [
                'submenus' => config('customvariable.monitoring_accesses'),
                'access' => Auth::user()->menu_access->monitoring ?? 0,
            ],
            'Reporting' => [
                'submenus' => config('customvariable.reporting_accesses'),
                'access' => Auth::user()->menu_access->reporting ?? 0,
            ],
            'Feature' => [
                'submenus' => config('customvariable.feature_accesses'),
                'access' => Auth::user()->menu_access->feature ?? 0,
            ],
        ];
    @endphp
     <div class="row row-cols-4">
        @foreach ($access_list as $menu_name=>$list)
        <div class="col">
            <h4>{{ $menu_name }}</h4>
            @if (($list['access'] ?? 0) == 0 )
                Tidak memiliki akses
            @endif
            <ul>
                @foreach ($list['submenus'] as $key => $submenu)
                    @if((($list['access'] ?? 0) & pow(2,$key)) != 0) 
                        <li>{{ $submenu }}</li>
                    @endif
                @endforeach
            </ul>
       </div>
       @endforeach
    </div>
 </div>
@endsection
@section('local-js')
<script>
</script>
@endsection
