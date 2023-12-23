<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CustomBidding extends Model
{
    use SoftDeletes;
    protected $table = 'custom_bidding';
    protected $primaryKey = 'id';

    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }
    public function ticket_item(){
        return $this->belongsTo(TicketItem::class);
    }

    public function createdBy(){
        return $this->belongsTo(Employee::class,'created_by','id');
    }
}
