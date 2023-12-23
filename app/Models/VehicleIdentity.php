<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleIdentity extends Model
{
    protected $table = 'vehicle_identity';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }

    public function armada(){
        return $this->belongsTo(Armada::class);
    }
}
