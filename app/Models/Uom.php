<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    use SoftDeletes;
    protected $table = 'uom';
    protected $primaryKey = 'id';

    public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function material()
    {
        return $this->hasMany(Material::class);
    }

}
