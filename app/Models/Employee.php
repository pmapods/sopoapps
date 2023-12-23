<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\SalesPoint;

class Employee extends Authenticatable
{
    use SoftDeletes;
    protected $table = 'employee';
    protected $primaryKey = 'id';

    public function employee_position(){
        return $this->belongsTo(EmployeePosition::class);
    }

    public function location_access(){
    	return $this->hasMany(EmployeeLocationAccess::class);
    }

    public function menu_access(){
        return $this->hasOne(EmployeeMenuAccess::class);
    }

    public function statusName(){
        switch ($this->status){
            case 0:
                return 'Aktif';
                break;
            case 1:
                return 'Non Aktif';
                break;
            default:
                return 'status_name_undefined';
                break;
        }
    }

    public function authorization_detail(){
        $this->hasMany(AuthorizationDetail::class);
    }

    // untuk otorisasi dengan akses by region
    public function location_access_list(){
        $employee_access = $this->location_access->pluck('salespoint_id');
        $salespoints = SalesPoint::whereIn('id',$employee_access)->get();
        $region_access = $salespoints->pluck("region_type")->unique();
        $employee_access = array_merge($employee_access->toArray(),$region_access->toArray());
        array_push($employee_access,"all");
        if($salespoints->where("region",19)->count()>0){ 
            array_push($employee_access,"indirect");
        }
        return $employee_access;
    }

    public function location_access_text(){
        $salespoint_list = [];
        $employee_access = $this->location_access;
        foreach($employee_access as $access){
            array_push($salespoint_list,$access->salespoint->name);
        }
        return implode(', ',$salespoint_list);
    }
}
