<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizationDetail extends Model
{
    protected $table = 'authorization_detail';
    protected $primaryKey = 'id';
    protected $appends = ['employee_name'];

    public function authorization()
    {
        return $this->belongsTo(Authorization::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function employee_position()
    {
        return $this->belongsTo(EmployeePosition::class)->withTrashed();
    }

    public function getEmployeeNameAttribute()
    {
        return $this->employee->name;
    }
}
