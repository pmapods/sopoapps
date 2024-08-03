<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Auction extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'auction_header';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    protected $fillable = [
        'id',
        'ticket_id',
        'ticket_code',
        'salespoint_id',
        'type',
        'notes',
        'status',
        'is_booked',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function salespoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    public function status_name()
    {
        switch ($this->status) {
            case '0':
                return 'Aktif';
                break;
            case '1':
                return 'Booked';
                break;
            default:
                return 'status_undefined';
                break;
        }
    }

    public function type_name()
    {
        switch ($this->type) {
            case 'barangjasa':
                return 'Barang Jasa';
                break;
            case 'armada':
                return 'Armada';
                break;
            case 'security':
                return 'Security';
                break;
            default:
                return "";
                break;
        }
    }

    public function auctionDetails()
    {
        return $this->hasMany(AuctionDetail::class, 'auction_header_id', 'id');
    }
    public function AuctionVendorBiddong()
    {
        return $this->hasMany(AuctionVendorBidding::class, 'auction_header_id', 'id');
    }

    public function emails()
    {
        $emails = json_decode($this->email);
        return $emails;
    }

    public function auction_status() {
        switch ($this->status) {
            case 0:
                return 'Ticket Tidak di Lelang';
                break;
            case 1:
                return 'Ticket Sedang di Lelang';
                break;
            default : 
                return 'Ticket Tidak di Lelang';
                break;
        }
    }
}
