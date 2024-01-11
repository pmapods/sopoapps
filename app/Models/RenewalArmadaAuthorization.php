<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RenewalArmadaAuthorization extends Model
{
    use SoftDeletes;
    protected $table = 'renewal_armada_authorization';
    protected $primaryKey = 'id';

    public function renewal_armada(){
        return $this->belongsTo(RenewalArmada::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

}
