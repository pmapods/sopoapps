<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BiddingDetail extends Model
{
    use SoftDeletes;
    protected $table = 'bidding_detail';
    protected $primaryKey = 'id';

    public function bidding(){
        return $this->belongsTo(Bidding::class);
    }

    public function ticket_vendor(){
        return $this->belongsTo(TicketVendor::class);
    }
}
