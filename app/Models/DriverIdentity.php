<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverIdentity extends Model
{
    protected $table = 'driver_identity';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }
}
