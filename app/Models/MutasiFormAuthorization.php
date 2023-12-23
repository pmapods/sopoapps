<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiFormAuthorization extends Model
{
    protected $table = 'mutasi_form_authorization';
    protected $primaryKey = 'id';

    public function mutasi_form(){
        return $this->belongsTo(MutasiForm::class);
    }
    
    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
