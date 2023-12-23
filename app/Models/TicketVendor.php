<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vendor;

class TicketVendor extends Model
{
    use SoftDeletes;
    protected $table = 'ticket_vendor';
    protected $primaryKey = 'id';
    
    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }

    public function po(){
        return $this->hasOne(Po::class);
    }

    public function vendor(){
        if($this->vendor_id != null){
            return Vendor::find($this->vendor_id);
        }
        return null;
    }

    public function type(){
        switch ($this->type) {
            case '0':
                return 'Terdaftar';
                break;
            case '1':
                return 'One Time Vendor';
                break;
            default:
                return 'undefined vendor type';
                break;
        }
    }

    public function deletedBy(){
        return $this->belongsTo(Employee::class,'deleted_by','id');
    }
}
