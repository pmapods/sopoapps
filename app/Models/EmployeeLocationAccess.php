<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLocationAccess extends Model
{
    protected $table = 'employee_location_access';
    protected $primaryKey = 'id';

    public function employee(){
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function salespoint(){
        return $this->belongsTo(SalesPoint::class)->withTrashed();
    }
}
