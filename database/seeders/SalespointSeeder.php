<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

use App\Models\SalesPoint;

class SalespointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SalesPoint::truncate();
        $salespoints = array(
            0 => array('id' => '1', 'code' => 'BDG-000', 'name' => 'BANDUNG', 'initial' => 'BDG-000', 'region_type' => 'west', 'region' => '7', 'status' => '1', 'trade_type' => '0', 'isJawaSumatra' => '1', 'address' => 'Jl. ABC Km 19.8 No D8  Kel Poris Gaga Kec Regol Kota Bandung'),
            1 => array('id' => '2', 'code' => 'JKT-000', 'name' => 'JAKARTA SELATAN', 'initial' => 'JKT-000', 'region_type' => 'west', 'region' => '6', 'status' => '1', 'trade_type' => '0', 'isJawaSumatra' => '1', 'address' => 'Jl. Raya Bekasi Km23.5  No 08 Cakung Jakarta Selatan'),
        );
        // 0 MT CENTRAL 1
        // 1 SUMATERA 1
        // 2 SUMATERA 2
        // 3 SUMATERA 3
        // 4 SUMATERA 4
        // 5 BANTEN
        // 6 DKI
        // 7 JABAR 1
        // 8 JABAR 2
        // 9 JABAR 3
        // 10 JATENG 1
        // 11 JATENG 2
        // 12 JATIM 1
        // 13 JATIM 2
        // 14 BALINUSRA
        // 15 KALIMANTAN
        // 16 SULAWESI
        // 17 HO
        // 18 JATENG 3
        $west_region = [0,1,2,3,4,5,6,7,8,9,17];
        $east_region = [10,11,12,13,14,15,16,18];
        foreach($salespoints as $salespoint){
            $newSalesPoint = SalesPoint::where("code",$salespoint["code"])->first();
            if(!$newSalesPoint){
                $newSalesPoint                = new SalesPoint;
            }
            $newSalesPoint->code          = $salespoint["code"];
            $newSalesPoint->name          = $salespoint["name"];
            // $region_type = (in_array($salespoint['region'],$west_region) == true) ? 'west' : 'east';
            $region_type                  = $salespoint["region_type"];
            $newSalesPoint->region_type   = $region_type;
            $newSalesPoint->initial       = $salespoint["initial"];
            $newSalesPoint->region        = $salespoint["region"];
            $newSalesPoint->status        = $salespoint["status"];
            $newSalesPoint->trade_type    = $salespoint["trade_type"];
            $newSalesPoint->isJawaSumatra = $salespoint["isJawaSumatra"];
            $newSalesPoint->address       = $salespoint["address"];
            $newSalesPoint->save();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
