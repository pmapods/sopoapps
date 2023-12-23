<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $seeder_array = [
            SalespointSeeder::class,
            EmployeeSeeder::class,
            EmployeePositionSeeder::class,
            AuthorizationSeeder::class,
            IndoRegionSeeder::class,
            EmployeeAccessSeeder::class,
            SuperAdminSeeder::class,
            BudgetSeeder::class,
            MaintenanceBudgetSeeder::class,
            HOBudgetSeeder::class,
            FileCompletementSeeder::class,
            VendorSeeder::class,
            ArmadaSeeder::class,
            POSeeder::class,
            NotificationSeeder::class,
        ];
        $dev_array = [
            // EmployeeSeeder::class,
        ];
        $local_array = [
            // EmployeeSeeder::class,
            // AuthorizationSeeder::class,
        ];
        if(App::environment('local')) {
            $seeder_array = array_merge($seeder_array,$local_array);
        }
        if(App::environment('development')) {
            $seeder_array = array_merge($seeder_array,$dev_array);
        }
        $this->call($seeder_array);
    }
}
