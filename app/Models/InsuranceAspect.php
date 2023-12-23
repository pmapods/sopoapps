<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceAspect extends Model
{
    protected $table = 'insurance_aspect';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }
}
