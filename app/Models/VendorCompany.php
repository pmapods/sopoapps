<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Vendor;

class VendorCompany extends Model
{
    use SoftDeletes;
    protected $table = 'vendor_company';
    protected $primaryKey = 'id';

    public function vendor(){
        if($this->code != null){
            return Vendor::find($this->code);
        }else{
            return null;
        }
    }

    public function status(){
        switch ($this->status) {
            case 0:
                return 'Aktif';
                break;
            
            case 1:
                return 'Non Aktif';
                break;
            
            default:
                return 'undefined_armada_status';
                break;
        }
    }
}
