<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ArmadaBudget extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'armada_budget';
    protected $appends = ['pending_quota','used_quota','vendor'];

    public function budget_upload(){
        return $this->belongsTo(BudgetUpload::class);
    }

    public function getVendorAttribute(){
        return Vendor::where('code',$this->vendor_code)->first();
    }

    public function getPendingQuotaAttribute(){
        $count = 0;
        $armadatickets = ArmadaTicket::where('budget_upload_id',$this->budget_upload_id)->whereNotIn('status',[-1,0])->get();
        foreach($armadatickets as $armadaticket){
            if($armadaticket->armada_type_id == $this->armada_type_id && $armadaticket->vendor_recommendation_name == $this->vendor->alias){
                $count++;
            }
        }
        return $count-$this->getUsedQuotaAttribute();
    }

    public function getTicketsCode(){
        $tickets_code = [];
        $armadatickets = ArmadaTicket::where('budget_upload_id',$this->budget_upload_id)->where('status',"!=",-1)->get();
        foreach($armadatickets as $armadaticket){
            if($armadaticket->armada_type_id == $this->armada_type_id && $armadaticket->vendor_recommendation_name == $this->vendor->alias){
                array_push($tickets_code,$armadaticket->code);
            }
        }
        $tickets_code = array_unique($tickets_code);
        return $tickets_code;
    }

    public function getUsedQuotaAttribute(){
        $count = 0;
        return $count;
    }
}
