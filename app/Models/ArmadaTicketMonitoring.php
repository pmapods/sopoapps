<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmadaTicketMonitoring extends Model
{
    protected $table = 'armada_ticket_monitoring';
    protected $primaryKey = 'id';

    public function armada_ticket(){
        return $this->belongsTo(ArmadaTicket::class);
    }
}
