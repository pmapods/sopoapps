<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AuctionVendorBidding extends Model
{
    use SoftDeletes;
    protected $table = 'auction_vendor_bidding';
    protected $primaryKey = 'id';

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
