<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use SoftDeletes;
    protected $table = 'vendor';
    protected $primaryKey = 'id';

    public function regency(){
        return $this->belongsTo(Regency::class,'city_id','id');
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

    public function emails() {
        $emails = json_decode($this->email);
        return $emails;
    }
}
