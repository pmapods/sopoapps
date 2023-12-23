<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class TicketingBlock extends Model
{
    protected $table = 'ticketing_block';
    protected $primaryKey = 'id';

    public function created_by_employee()
    {
        return $this->hasOne(Employee::class, 'id', 'created_by')->withTrashed();
    }

    public function last_update_by_employee()
    {
        return $this->hasOne(Employee::class, 'id', 'last_update_by')->withTrashed();
    }
}
