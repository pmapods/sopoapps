<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ArmadaType extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'armada_type';

    public function armada()
    {
        return $this->hasMany(Armada::class);
    }
}
