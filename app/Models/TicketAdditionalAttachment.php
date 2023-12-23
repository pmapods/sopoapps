<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAdditionalAttachment extends Model
{
    protected $table = 'ticket_additional_attachment';
    protected $primaryKey = 'id';

    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }
}
