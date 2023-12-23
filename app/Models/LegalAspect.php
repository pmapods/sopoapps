<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalAspect extends Model
{
    protected $table = 'legal_aspect';
    protected $primaryKey = 'id';

    public function armada_accident(){
        return $this->belongsTo(ArmadaAccident::class);
    }
}
