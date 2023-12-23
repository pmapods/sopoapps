<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMonitoring extends Model
{
    protected $table = 'ticket_monitoring';
    protected $primaryKey = 'id';

    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }
}
