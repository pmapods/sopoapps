<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeMenuAccess extends Model
{
    protected $table = 'employee_menu_access';
    protected $primaryKey = 'id';

    public function employee(){
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function access_list_text(){
        $menu_array_access = [];
        $masterdata_accesses = config('customvariable.masterdata_accesses');
        $budget_accesses = config('customvariable.budget_accesses');
        $operational_accesses = config('customvariable.operational_accesses');
        $monitoring_accesses = config('customvariable.monitoring_accesses');
        $reporting_accesses = config('customvariable.reporting_accesses');
        $feature_accesses = config('customvariable.feature_accesses');
        foreach($masterdata_accesses as $key=>$access){
            if((($this->masterdata ?? 0) & pow(2,$key)) != 0){
                array_push($menu_array_access, $access);
            }
        }
        foreach($budget_accesses as $key=>$access){
            if((($this->budget ?? 0) & pow(2,$key)) != 0){
                array_push($menu_array_access, "Budget ".$access);
            }
        }
        foreach($operational_accesses as $key=>$access){
            if((($this->operational ?? 0) & pow(2,$key)) != 0){
                array_push($menu_array_access, $access);
            }
        }
        foreach($monitoring_accesses as $key=>$access){
            if((($this->monitoring ?? 0) & pow(2,$key)) != 0){
                array_push($menu_array_access, $access);
            }
        }
        foreach($reporting_accesses as $key=>$access){
            if((($this->reporting ?? 0) & pow(2,$key)) != 0){
                array_push($menu_array_access, $access);
            }
        }
        foreach($feature_accesses as $key=>$access){
            if((($this->feature ?? 0) & pow(2,$key)) != 0){
                array_push($menu_array_access, $access);
            }
        }
        return implode(", ",$menu_array_access);
    }
}
