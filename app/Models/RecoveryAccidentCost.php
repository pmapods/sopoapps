<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryAccidentCost extends Model
{
    protected $table = 'recovery_accident_cost';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }
}
