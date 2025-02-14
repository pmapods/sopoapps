<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Hash;

use Illuminate\Database\Seeder;
use App\Models\Employee;

use Schema;
use DB;

class EmployeeSuperAdminSedder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // tambah satu user super admin
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $employee                         = new Employee;
        $employee->id                     = 1;
        $employee->code                   = 'EMP-00001';
        $employee->name                   = 'Super Admin';
        $employee->username               = 'superadmin';
        $employee->position_id            = 1;
        $employee->password               =  Hash::make('FireGundam78');
        $employee->email                  = 'admin@admin.com';
        $employee->save();

        print("import employee super admin data finished");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
