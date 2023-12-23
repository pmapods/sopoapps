<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiddingAuthorization extends Model
{
    protected $table = 'bidding_authorization';
    protected $primaryKey = 'id';

    public function bidding(){
        return $this->belongsTo(Bidding::class)->withTrashed();
    }

    public function employee(){
        return $this->belongsTo(Employee::class)->withTrashed();
    }
}
