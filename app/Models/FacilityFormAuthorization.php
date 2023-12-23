<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityFormAuthorization extends Model
{
    protected $table = 'facility_form_authorization';
    protected $primaryKey = 'id';

    public function facility_form(){
        return $this->belongsTo(FacilityForm::class);
    }
    
    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
