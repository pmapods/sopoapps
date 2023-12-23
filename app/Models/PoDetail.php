<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PoDetail extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'po_detail';

    public function ticket_item(){
        return $this->belongsTo(TicketItem::class);
    }
}
