<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use SoftDeletes;
    protected $table = 'product';
    protected $primaryKey = 'id';

    public function regency(){
        return $this->belongsTo(Regency::class,'salespoint','id');
    }

    public function uom(){
        return $this->belongsTo(Uom::class,'uom_id','id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }
}
