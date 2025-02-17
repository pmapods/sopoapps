<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use SoftDeletes;
    protected $table = 'customer';
    protected $primaryKey = 'id';

    public function regency(){
        return $this->belongsTo(Regency::class,'regency_id','id');
    }

    public function customer_type(){
        return $this->belongsTo(CustomerType::class, 'type', 'code');
    }

    public function findByCode($code) {
        return self::where('code', $code)->first();
    }

    public function status_name(){
        switch ($this->status){
            case '0':
                return 'Aktif';
                break;
            case '1':
                return 'Non Aktif';
                break;
            default:
                return 'status_undefined';
                break;
        }
    }

    public function type_name() {
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

    public function emails() {
        $emails = json_decode($this->email);
        return $emails;
    }
}
