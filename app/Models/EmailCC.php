<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\EmployeePosition;

class EmailCC extends Model
{
    protected $table = 'email_cc';
    protected $primaryKey = 'id';

    public function created_by_employee()
    {
        return $this->hasOne(Employee::class, 'id', 'created_by')->withTrashed();
    }

    public function employee_positions()
    {
        return $this->hasOne(EmployeePosition::class, 'id', 'employee_position')->withTrashed();
    }
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function armada_ticket()
    {
        return $this->belongsTo(ArmadaTicket::class);
    }

    public function security_ticket()
    {
        return $this->belongsTo(SecurityTicket::class);
    }
}
