<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmadaAccident extends Model
{
    protected $table = 'armada_accident';
    protected $primaryKey = 'id';

    public function salespoint(){
        return $this->belongsTo(SalesPoint::class);
    }

    public function vehicle_identity(){
        return $this->hasOne(VehicleIdentity::class);
    }

    public function driver_identity(){
        return $this->hasOne(DriverIdentity::class);
    }

    public function pic_area(){
        return $this->hasOne(AccidentPicArea::class);
    }

    public function accident_cost(){
        return $this->hasOne(AccidentCost::class);
    }

    public function legal_aspect(){
        return $this->hasOne(LegalAspect::class);
    }

    public function insurance_aspect(){
        return $this->hasOne(InsuranceAspect::class);
    }

    public function recovery_cost(){
        return $this->hasOne(RecoveryAccidentCost::class);
    }

    public function createdBy(){
        return $this->belongsTo(Employee::class,'created_by','id');
    }

    public function updatedBy(){
        try{
            return $this->belongsTo(Employee::class,'updated_by','id');
        }catch(\Exception $e){
            return null;
        }
    }

    public function status(){
        if($this->status == 0){
            // 0
            return 'open';
        }else{
            // 1
            return 'closed';
        }
    }
}
