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

    public function isNiaga()
    {
        switch ($this->isNiaga) {
            case '0':
                return 'Non Niaga';
                break;
            case '1':
                return 'Niaga';
                break;
            case '2':
                return 'Non Niaga-COP';
                break;
            default:
                return 'is_niaga_undefined';
                break;
        }
    }

    public function isSBH() {
        switch ($this->isSBH) {
            case 0:
                return 'Non SBH';
                break;
            case 1:
                return 'SBH';
                break;
        }
    }
}
