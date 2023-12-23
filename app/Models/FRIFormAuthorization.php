<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FRIFormAuthorization extends Model
{
    protected $table = 'fri_form_authorization';
    protected $primaryKey = 'id';

    public function fri_form(){
        return $this->belongsTo(FRIForm::class);
    }
    
    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
