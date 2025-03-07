<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesPoint extends Model
{
    use SoftDeletes;
    protected $table = 'salespoint';
    protected $primaryKey = 'id';

    public function authorization()
    {
        $this->hasMany(Authorization::class);
    }

    public function salespoint_id_list()
    {
        $salespoint_id_list = [$this->id];
        array_push($salespoint_id_list, "all");
        array_push($salespoint_id_list, strtolower($this->region_name()));
        // if(strtolower($this->region_name()) != "indirect"){
        array_push($salespoint_id_list, $this->region_type);
        // }
        return $salespoint_id_list;
    }

    public function region_name()
    {
        switch ($this->region) {
            case 1:
                return 'SUMATERA';
                break;
            case 2:
                return 'BANTEN';
                break;
            case 3:
                return 'DKI';
                break;
            case 4:
                return 'JABAR';
                break;
            case 5:
                return 'JATENG';
                break;
            case 6:
                return 'JATIM';
                break;
            case 7:
                return 'BALINUSRA';
                break;
            case 8:
                return 'KALIMANTAN';
                break;
            case 9:
                return 'SULAWESI';
                break;
            case 10:
                return 'HO';
                break;
            default:
                return 'region_undefined';
                break;
        }
    }

    public function status_name()
    {
        switch ($this->status) {
            case 0:
                return 'INACTIVE';
                break;
            case 1:
                return 'ACTIVE';
                break;
            default:
                return 'status_undefined';
                break;
        }
    }

    public function trade_type_name()
    {
        switch ($this->trade_type) {
            case 0:
                return 'GT';
                break;
            case 1:
                return 'MT';
                break;
            case 2:
                return 'INDIRECT';
                break;
            default:
                return 'trade_type_undefined';
                break;
        }
    }

    // public function jawasumatra()
    // {
    //     if ($this->isJawaSumatra) {
    //         return 'Dalam';
    //     } else {
    //         return 'Luar';
    //     }
    // }
}
