<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PoAuthorization extends Model
{
    use SoftDeletes;
    protected $table = 'po_authorization';
    protected $primaryKey = 'id';

    public function po(){
        return $this->belongsTo(Po::class);
    }
}
