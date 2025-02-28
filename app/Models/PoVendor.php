<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class PoVendor extends Model
{
    use SoftDeletes;
    protected $table = 'po_vendor';
    protected $primaryKey = 'id';
    
    public function po(){
        return $this->belongsTo(Po::class);
    }

    public function customer(){
        if($this->customer_id != null){
            return Customer::find($this->customer_id);
        }
        return null;
    }

    public function type(){
        switch ($this->type) {
            case 'A1':
                return 'A1';
                break;
            case 'A2':
                return 'A2';
                break;
            case 'A3':
                return 'A3';
                break;
            case 'B1':
                return 'B1';
                break;
            case 'B2':
                return 'B2';
                break;
            case 'B3':
                return 'B3';
                break;
            case 'C1':
                return 'C1';
                break;
            case 'C2':
                return 'C2';
                break;
            case 'C3':
                return 'C3';
                break;
            default:
                return "";
                break;
        }
    }

    public function deletedBy(){
        return $this->belongsTo(Employee::class,'deleted_by','id');
    }
}
