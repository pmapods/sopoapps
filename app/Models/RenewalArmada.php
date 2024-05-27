<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\SalesPoint;
use App\Models\Employee;
use App\Models\EmployeePosition;

class RenewalArmada extends Model
{
    use SoftDeletes;
    protected $table = 'renewal_armada_detail';
    protected $primaryKey = 'id';

    public function last_salespoint(){
        if($this->last_salespoint_id != null){
            return SalesPoint::find($this->last_salespoint_id);
        }else{
            return null;
        }
    }

    public function authorizations(){
        return $this->hasMany(RenewalArmadaAuthorization::class);
    }

    public function current_authorization(){
        $queue = $this->authorizations->where('status',0)->sortBy('level');
        $current = $queue->first();
        if($this->status != 0){
            // authorization done
            return null;
        }else{
            return $current;
        }
    }

    public function new_salespoint(){
        if($this->new_salespoint_id != null){
            return SalesPoint::find($this->new_salespoint_id);
        }else{
            return null;
        }
    }

    public function created_by_employee(){
        if($this->created_by != null){
            return Employee::find($this->created_by);
        }else{
            return null;
        }
    }

    public function approved_by_employee(){
        if($this->approved_by != null){
            return Employee::find($this->approved_by);
        }else{
            return null;
        }
    }

    public function terminated_by_employee(){
        if($this->terminated_by != null){
            return Employee::find($this->terminated_by);
        }else{
            return null;
        }
    }

    public function rejected_by_employee(){
        if($this->rejected_by != null){
            return Employee::find($this->rejected_by);
        }else{
            return null;
        }
    }

    public function armada_type(){
        return $this->belongsTo(ArmadaType::class);
    }

    public function employee_position(){
        if($this->approved_by != null){
            return EmployeePosition::find($this->terminated_by);
        }else{
            return null;
        }
    }

    public function status(){
        switch ($this->status) {
            case 0:
                return 'Waiting Approval';
                break;

            case 1:
                return 'Approved';
                break;

            case 2:
                return 'Rejected';
                break;

            case -1:
                return 'Terminated';
                break;

            default:
                return 'undefined_armada_status';
                break;
        }
    }

}
