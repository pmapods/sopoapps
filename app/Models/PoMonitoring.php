<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoMonitoring extends Model
{
    protected $table = 'po_monitoring';
    protected $primaryKey = 'id';

    public function pos(){
        return $this->belongsTo(PO::class);
    }
}
