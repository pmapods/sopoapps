<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePosition extends Model
{
    use SoftDeletes;
    protected $table = 'employee_position';
    protected $primaryKey = 'id';
}
