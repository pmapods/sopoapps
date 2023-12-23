<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccidentPicArea extends Model
{
    protected $table = 'accident_pic_area';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }
}
