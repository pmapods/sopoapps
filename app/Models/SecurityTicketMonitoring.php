<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityTicketMonitoring extends Model
{
    protected $table = 'security_ticket_monitoring';
    protected $primaryKey = 'id';

    public function security_ticket(){
        return $this->belongsTo(SecurityTicket::class);
    }
}
