<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmadaTicketAuthorization extends Model
{
    protected $table = 'armada_ticket_authorization';
    protected $primaryKey = 'id';

    public function armada_ticket(){
        return $this->belongsTo(ArmadaTicket::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
