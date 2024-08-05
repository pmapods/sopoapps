<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AuctionDetail extends Model
{
    use SoftDeletes;
    protected $table = 'auction_detail';
    protected $primaryKey = 'id';

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'id');
    }
}
