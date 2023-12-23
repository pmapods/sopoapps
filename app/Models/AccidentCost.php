<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccidentCost extends Model
{
    protected $table = 'accident_cost';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }
}
