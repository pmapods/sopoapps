<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class EvaluasiFormAuthorization extends Model
{
    use SoftDeletes;
    protected $table="evaluasi_form_authorization";
    protected $primaryKey = 'id';

    public function evaluasi_form(){
        return $this->belongsTo(EvaluasiForm::class);
    }
}
