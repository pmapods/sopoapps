@extends('Layout.app')
@section('local-css')

@endsection

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Detail Akses {{$employee->name}} ({{$employee->code}})</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Masterdata</li>
                    <li class="breadcrumb-item">Akses Karyawan</li>
                    <li class="breadcrumb-item active">Detail Akses</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="content-body px-4">
    @php
        $checkedlist = $employee->location_access->pluck('salespoint_id');
    @endphp
    <form action="/updateemployeeaccessdetail" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="employee_id" value="{{$employee->id}}">
        <div class="row">
            <div class="col-md-6">
                <h3>Akses Area</h3>
            </div>
            <div class="col-md-6 text-right">
                <button type="button" class="btn btn-primary text-right" id="unselect_all_area_button">UnSelect All Area</button>
                <button type="button" class="btn btn-primary text-right" id="select_all_area_button">Select All Area</button>
            </div>
        </div>
        <div class="row mt-5" id="area_access_field">
            @foreach($regions as $key => $region)
            <div class="col-6 col-md-3 mb-3 group_check p-2">
                <div class="p-2 card">
                    <div class="form-check form-check-inline mb-2">
                        <label class="form-check-label">
                            <span class="h4 mr-2">{{$region->first()->region_name()}}</span> <input class="form-check-input head_check" type="checkbox" value="{{$region->first()->region}}">
                        </label>
                    </div>
                    @foreach($region as $salespoint)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="location[]" value="{{$salespoint->id}}"
                            @if($checkedlist->contains($salespoint->id)) checked @endif>
                            {{$salespoint->name}} ({{$salespoint->status_name()}} - {{$salespoint->trade_type_name()}})
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        <hr>
        <h3>Akses Menu</h3>
        <div class="row">
            @php
                $masterdata_accesses = config('customvariable.masterdata_accesses');
            @endphp
            @if (Auth::user()->id == 1)
            <div class="col-6 col-md-4 mb-3 group_check">
                <div class="form-check form-check-inline mb-2">
                    <label class="form-check-label">
                        <span class="h4 mr-2">Master Data</span> <input class="form-check-input head_check" type="checkbox">
                    </label>
                </div>
                @foreach ($masterdata_accesses as $key=>$access)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="masterdata[]"
                            value="{{pow(2,$key)}}"
                            @if((($employee->menu_access->masterdata ?? 0) & pow(2,$key)) != 0) checked @endif>{{$access}}
                        </label>
                    </div>
                @endforeach
            </div>
            @endif

            @php
                $budget_accesses = config('customvariable.budget_accesses');
            @endphp
            <div class="col-6 col-md-4 mb-3 group_check">
                <div class="form-check form-check-inline mb-2">
                    <label class="form-check-label">
                        <span class="h4 mr-2">Budget</span> <input class="form-check-input head_check" type="checkbox">
                    </label>
                </div>
                @foreach ($budget_accesses as $key=>$access)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="budget[]"
                            value="{{pow(2,$key)}}"
                            @if((($employee->menu_access->budget ?? 0) & pow(2,$key)) != 0) checked @endif>{{$access}}
                        </label>
                    </div>
                @endforeach
            </div>

            @php
                $operational_accesses = config('customvariable.operational_accesses');
            @endphp
            <div class="col-6 col-md-4 mb-3 group_check">
                <div class="form-check form-check-inline mb-2">
                    <label class="form-check-label">
                        <span class="h4 mr-2">Operational</span> <input class="form-check-input head_check" type="checkbox">
                    </label>
                </div>
                @foreach ($operational_accesses as $key=>$access)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="operational[]"
                            value="{{pow(2,$key)}}"
                            @if((($employee->menu_access->operational ?? 0) & pow(2,$key)) != 0) checked @endif>{{$access}}
                        </label>
                    </div>
                @endforeach
            </div>

            @php
                $monitoring_accesses = config('customvariable.monitoring_accesses');
            @endphp
            <div class="col-6 col-md-4 mb-3 group_check">
                <div class="form-check form-check-inline mb-2">
                    <label class="form-check-label">
                        <span class="h4 mr-2">Monitoring</span> <input class="form-check-input head_check" type="checkbox">
                    </label>
                </div>
                @foreach ($monitoring_accesses as $key=>$access)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="monitoring[]"
                            value="{{pow(2,$key)}}"
                            @if((($employee->menu_access->monitoring ?? 0) & pow(2,$key)) != 0) checked @endif>{{$access}}
                        </label>
                    </div>
                @endforeach
            </div>

            @php
                $reporting_accesses = config('customvariable.reporting_accesses');
            @endphp
            <div class="col-6 col-md-4 mb-3 group_check">
                <div class="form-check form-check-inline mb-2">
                    <label class="form-check-label">
                        <span class="h4 mr-2">Reporting</span> <input class="form-check-input head_check" type="checkbox">
                    </label>
                </div>
                @foreach ($reporting_accesses as $key=>$access)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="reporting[]"
                            value="{{pow(2,$key)}}"
                            @if((($employee->menu_access->reporting ?? 0) & pow(2,$key)) != 0) checked @endif>{{$access}}
                        </label>
                    </div>
                @endforeach
            </div>

            @php
                $feature_accesses = config('customvariable.feature_accesses');
            @endphp
            <div class="col-6 col-md-4 mb-3 group_check">
                <div class="form-check form-check-inline mb-2">
                    <label class="form-check-label">
                        <span class="h4 mr-2">Feature</span> <input class="form-check-input head_check" type="checkbox">
                    </label>
                </div>
                @foreach ($feature_accesses as $key=>$access)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input child_check" name="feature[]"
                            value="{{pow(2,$key)}}"
                            @if((($employee->menu_access->feature ?? 0) & pow(2,$key)) != 0) checked @endif>{{$access}}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary btn-lg">Perbarui Hak Akses</button>
        </div>
    </form>
</div>

@endsection
@section('local-js')
<script>
    $(document).ready(function(){
        $('.head_check').on('change', function(){
            let parent = $(this).closest('.group_check');
            let isChecked = $(this).prop('checked');
            if(isChecked){
                parent.find('.child_check').prop('checked', true);
            }else{
                parent.find('.child_check').prop('checked', false);
            }
        });
        $('.group_check').on('change', function(){
            let parent = $(this).closest('.group_check');
            let isChecked = $(this).prop('checked');
            let checked_count = 0;
            let unchecked_count = 0;
            parent.find('.child_check').each(function(){
                if($(this).prop('checked')){
                    checked_count++;
                }else{
                    unchecked_count++;
                }
            });

            if(checked_count>unchecked_count){
                parent.find('.head_check').prop('indeterminate',false);
                parent.find('.head_check').prop('checked',true);
            }else{
                parent.find('.head_check').prop('indeterminate',false);
                parent.find('.head_check').prop('checked',false);
            }

            if(checked_count>0 && unchecked_count>0){
                parent.find('.head_check').prop('indeterminate',true);
            }
        });

        // check setiap region
        $('.group_check').each((location_index,location_element)=>{
            let head_check = $(location_element).find('.head_check');
            let salespoints = $(location_element).find('.child_check');
            let checked = 0;
            let unchecked = 0;
            salespoints.each(function(salespoint_index,salespoint_element){
                let isChecked = $(salespoint_element).prop('checked');
                if(isChecked){
                    checked++;
                }else{
                    unchecked++;
                }
            })
            if(checked>unchecked){
                head_check.prop('indeterminate',false);
                head_check.prop('checked',true);
            }else{
                head_check.prop('indeterminate',false);
                head_check.prop('checked',false);
            }

            if(checked>0 && unchecked>0){
                head_check.prop('indeterminate',true);
            }

        });
        $('#select_all_area_button').click(function(){
            $('#area_access_field .head_check').prop('checked',true);
            $('#area_access_field .head_check').trigger('change');
        });
        $('#unselect_all_area_button').click(function(){
            $('#area_access_field .head_check').prop('checked',false);
            $('#area_access_field .head_check').trigger('change');
        });
    })
</script>
@endsection
