<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerpanjanganFormAuthorization extends Model
{
    protected $table = 'perpanjangan_form_authorization';
    protected $primaryKey = 'id';

    public function perpanjangan_form(){
        return $this->belongsTo(PerpanjanganForm::class);
    }
    
    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
