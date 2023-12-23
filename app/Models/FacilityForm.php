<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FacilityForm extends Model
{
    use SoftDeletes;
    protected $table = 'facility_form';
    protected $primaryKey = 'id';

    public function salespoint(){
        return $this->belongsTo(SalesPoint::class);
    }

    public function armada_ticket(){
        return $this->belongsTo(ArmadaTicket::class);
    }

    public function facility_form_authorizations(){
        return $this->hasMany(FacilityFormAuthorization::class);
    }

    public function terminated_by_employee(){
        return $this->belongsTo(Employee::class,'terminated_by','id')->withTrashed();
    }

    public function current_authorization(){
        $queue = $this->facility_form_authorizations->where('status',0)->sortBy('level');
        $current = $queue->first();
        if($this->status != 0){
            // authorization done
            return null;
        }else{
            return $current;
        }
    }

    public function getPath(){
        $code = $this->armada_ticket->code;
        if($code){
            $data = app('app\Http\Controllers\Operational\ArmadaTicketingController')->printFacilityForm($code,'path');
            return $data;
        }else{
            return null;
        }
    }
}
