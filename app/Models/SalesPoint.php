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
            case 0:
                return 'MT CENTRAL 1';
                break;
            case 1:
                return 'SUMATERA 1';
                break;
            case 2:
                return 'SUMATERA 2';
                break;
            case 3:
                return 'SUMATERA 3';
                break;
            case 4:
                return 'SUMATERA 4';
                break;
            case 5:
                return 'BANTEN';
                break;
            case 6:
                return 'DKI';
                break;
            case 7:
                return 'JABAR 1';
                break;
            case 8:
                return 'JABAR 2';
                break;
            case 9:
                return 'JABAR 3';
                break;
            case 10:
                return 'JATENG 1';
                break;
            case 11:
                return 'JATENG 2';
                break;
            case 12:
                return 'JATIM 1';
                break;
            case 13:
                return 'JATIM 2';
                break;
            case 14:
                return 'BALINUSRA';
                break;
            case 15:
                return 'KALIMANTAN';
                break;
            case 16:
                return 'SULAWESI';
                break;
            case 17:
                return 'HO';
                break;
            case 18:
                return 'JATENG 3';
                break;
            case 19:
                return 'INDIRECT';
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
                return 'DEPO';
                break;
            case 1:
                return 'CABANG';
                break;
            case 2:
                return 'CELLPOINT';
                break;
            case 3:
                return 'SUBDIST';
                break;
            case 4:
                return 'NATIONAL';
                break;
            case 5:
                return 'HEAD OFFICE';
                break;
            case 6:
                return 'CELLPOINT+';
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

    public function jawasumatra()
    {
        if ($this->isJawaSumatra) {
            return 'Dalam';
        } else {
            return 'Luar';
        }
    }
}
