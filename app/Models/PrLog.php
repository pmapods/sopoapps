<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrLog extends Model
{
    protected $table = 'pr_log';
    protected $primaryKey = 'id';

    public function pr(){
        return $this->belongsTo(Pr::class);
    }
}
