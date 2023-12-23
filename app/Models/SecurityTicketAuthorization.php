<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityTicketAuthorization extends Model
{
    protected $table = 'security_ticket_authorization';
    protected $primaryKey = 'id';

    public function security_ticket(){
        return $this->belongsTo(SecurityTicket::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
