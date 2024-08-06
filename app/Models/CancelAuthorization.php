<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancelAuthorization extends Model
{
    protected $table = 'cancel_end_authorization';
    protected $primaryKey = 'id';

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function status()
    {
        switch ($this->status) {
            case '0':
                return 'Pending';
                break;
            case '1':
                return 'Approved';
                break;
            case '2':
                return 'Reject';
                break;

            default:
                return 'undefined_authorization_status';
                break;
        }
    }
}
