<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuctionDetail extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'auction_detail';
    protected $primaryKey = 'id';
    public $incrementing = true; // ID adalah integer dan auto increment
    protected $keyType = 'int';

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    protected $fillable = [
        'auction_header_id',
        'ticket_id',
        'ticket_item_id',
        'product_name',
        'salespoint_name',
        'posted_by',
        'removed_by',
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

    public function emails()
    {
        $emails = json_decode($this->email);
        return $emails;
    }
}
