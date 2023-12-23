<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceBudgetCategory;
use App\Models\MaintenanceBudget;
use DB;

class MaintenanceBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MaintenanceBudgetCategory::truncate();
        MaintenanceBudget::truncate();
        $seeder = array(
            0 => array('code' => 'TR-01', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Double Long Chasis', 'uom' => '-'),
            1 => array('code' => 'TR-02', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Double', 'uom' => '-'),
            2 => array('code' => 'TR-03', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Engkel Long Chasis', 'uom' => '-'),
            3 => array('code' => 'TR-04', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Engkel', 'uom' => '-'),
            4 => array('code' => 'TR-05', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'L300', 'uom' => '-'),
            5 => array('code' => 'TR-06', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Grand Max', 'uom' => '-'),
            6 => array('code' => 'TR-07', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Blind Van', 'uom' => '-'),
            7 => array('code' => 'TR-08', 'group_code' => 'TR', 'group_name' => 'TYRE REPLACEMENT', 'name' => 'Demo 3 (Pickup Motor)', 'uom' => '-'),
            8 => array('code' => 'BR-01', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Double Long Chasis', 'uom' => '-'),
            9 => array('code' => 'BR-02', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Double', 'uom' => '-'),
            10 => array('code' => 'BR-03', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Engkel Long Chasis', 'uom' => '-'),
            11 => array('code' => 'BR-04', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Engkel', 'uom' => '-'),
            12 => array('code' => 'BR-05', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'L300', 'uom' => '-'),
            13 => array('code' => 'BR-06', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Grand Max', 'uom' => '-'),
            14 => array('code' => 'BR-07', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Blind Van', 'uom' => '-'),
            15 => array('code' => 'BR-08', 'group_code' => 'BR', 'group_name' => 'BATTERY REPLACEMENT', 'name' => 'Demo 3 (Pickup Motor)', 'uom' => '-'),
            16 => array('code' => 'ORS-01', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Double Long Chasis', 'uom' => '-'),
            17 => array('code' => 'ORS-02', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Double', 'uom' => '-'),
            18 => array('code' => 'ORS-03', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Engkel Long Chasis', 'uom' => '-'),
            19 => array('code' => 'ORS-04', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Engkel', 'uom' => '-'),
            20 => array('code' => 'ORS-05', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'L300', 'uom' => '-'),
            21 => array('code' => 'ORS-06', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Grand Max', 'uom' => '-'),
            22 => array('code' => 'ORS-07', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Blind Van', 'uom' => '-'),
            23 => array('code' => 'ORS-08', 'group_code' => 'ORS', 'group_name' => 'OIL REPLACEMENT & ROUTINE SERVICE', 'name' => 'Demo 3 (Pickup Motor)', 'uom' => '-'),
            24 => array('code' => 'BDM-01', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Warehouse Reconditioning (per M2)', 'uom' => '-'),
            25 => array('code' => 'BDM-02', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Unpack + Installation AC (per Unit)', 'uom' => '-'),
            26 => array('code' => 'BDM-03', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Unpack + Installation Racking (per M2)', 'uom' => '-'),
            27 => array('code' => 'BDM-04', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Unpack + Installation CCTV (per Unit)', 'uom' => '-'),
            28 => array('code' => 'BDM-05', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Unpack + Installation ICON (per Unit)', 'uom' => '-'),
            29 => array('code' => 'BDM-06', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Safety Box Moving', 'uom' => '-'),
            30 => array('code' => 'BDM-07', 'group_code' => 'BDM', 'group_name' => 'BUILDING MAINTENANCE', 'name' => 'Monthly Maintenance', 'uom' => '-'),
            31 => array('code' => 'OEM-01', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Cleaning AC (per Unit)', 'uom' => '-'),
            32 => array('code' => 'OEM-02', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Freon AC Refill (per Unit)', 'uom' => '-'),
            33 => array('code' => 'OEM-03', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Head Printer LQ Maintenance (per Unit)', 'uom' => '-'),
            34 => array('code' => 'OEM-04', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Printer Epson / HP Maintenance (per Unit)', 'uom' => '-'),
            35 => array('code' => 'OEM-05', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Maintenance CCTV - Camera', 'uom' => '-'),
            36 => array('code' => 'OEM-06', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Maintenance CCTV - HD', 'uom' => '-'),
            37 => array('code' => 'OEM-07', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'Maintenance CCTV - Adaptor', 'uom' => '-'),
            38 => array('code' => 'OEM-08', 'group_code' => 'OEM', 'group_name' => 'OFFICE EQUIPMENT MAINTENANCE', 'name' => 'VCON Maintenance', 'uom' => '-'),
            39 => array('code' => 'OTM-01', 'group_code' => 'OTM', 'group_name' => 'OTHER MAINTENANCE', 'name' => 'Type of APAR 2KG', 'uom' => '-'),
            40 => array('code' => 'OTM-02', 'group_code' => 'OTM', 'group_name' => 'OTHER MAINTENANCE', 'name' => 'Type of APAR 6KG', 'uom' => '-'),
            41 => array('code' => 'OTM-03', 'group_code' => 'OTM', 'group_name' => 'OTHER MAINTENANCE', 'name' => 'Type of APAR 20KG', 'uom' => '-'),
        );
        $maintenance_budgets = collect($seeder);
        $categories_name = $maintenance_budgets->pluck('group_name')->unique();
        $categories_code = $maintenance_budgets->pluck('group_code')->unique();
        foreach($categories_name as $key=>$name){
            $new = new MaintenanceBudgetCategory;
            $new->name = $name;
            $new->code = $categories_code[$key];
            $new->save();
        }

        foreach ($maintenance_budgets as $key => $budget) {
            $new = new MaintenanceBudget;
            $selected_category = MaintenanceBudgetCategory::where('code',$budget['group_code'])->first();
            $new->maintenance_budget_category_id = $selected_category->id;
            $new->name = $budget['name'];
            $new->code = $budget['code'];
            $new->uom  = $budget['uom'];
            $new->save();
        }
        
    }
}
