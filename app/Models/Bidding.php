<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Employee;

class Bidding extends Model
{
    use SoftDeletes;
    protected $table = 'bidding';
    protected $primaryKey = 'id';

    public function bidding_detail()
    {
        return $this->hasMany(BiddingDetail::class);
    }

    public function bidding_authorization()
    {
        return $this->hasMany(BiddingAuthorization::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticket_item()
    {
        return $this->belongsTo(TicketItem::class);
    }

    public function status()
    {
        if ($this->status == 0 || $this->status == 1) {
            if ($this->status == 0) {
                return "Menunggu Otorisasi oleh " . $this->current_authorization()->employee->name;
            }
            if ($this->status == 1) {
                return "Otorisasi selesai -- " . $this->updated_at->translatedFormat('d F Y (H:i)');
            }
        }
        if ($this->status == -1) {
            return "Otorisasi ditolak oleh " . $this->rejected_by_employee()->name . " karena"  . $this->reject_notes;
        }
    }

    public function current_authorization()
    {
        $queue = $this->bidding_authorization->where('status', 0)->sortBy('level');
        $current = $queue->first();
        if ($this->status != 0) {
            // authorization done
            return null;
        } else {
            return $current;
        }
    }

    public function last_authorization()
    {
        $queue = $this->bidding_authorization->where('status', 1)->sortByDesc('level');
        if ($last) {
            $last = $queue->first();
        }
        if ($this->status != 1) {
            return null;
        } else {
            return $last;
        }
    }

    public function selected_vendor()
    {
        if (count($this->bidding_detail) < 1) {
            return null;
        }
        $selected_vendor = null;
        $selected_total = 0;
        foreach ($this->bidding_detail as $detail) {
            $sum = 0;
            $sum += $detail->price_score * 5;
            $sum += $detail->ketersediaan_barang_score * 3;
            $sum += $detail->ketentuan_bayar_score * 2;
            $sum += $detail->others_score * 2;
            if ($sum > $selected_total || $selected_vendor == null) {
                $selected_vendor = $detail;
                $selected_total = $sum;
            }
        }
        return $selected_vendor;
    }

    public function rejected_by_employee()
    {
        if ($this->rejected_by != null) {
            return Employee::find($this->rejected_by);
        } else {
            return null;
        }
    }

    public function group()
    {
        switch ($this->group) {
            case 'asset':
                return "Asset";
                break;
            case 'inventory':
                return "Inventory";
                break;
            case 'others':
                return "Lainnya";
                break;
            default:
                return "";
                break;
        }
    }
}
