<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Po;

class PoItem extends Model
{
    use SoftDeletes;
    protected $table = 'po_item';
    protected $primaryKey = 'id';

    
}
