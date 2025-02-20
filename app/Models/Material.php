<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use SoftDeletes;
    protected $table = 'material';
    protected $primaryKey = 'id';

    public function regency(){
        return $this->belongsTo(Regency::class,'salespoint','id');
    }
}
