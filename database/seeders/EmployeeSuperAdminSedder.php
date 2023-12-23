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
        $employee->name                   = 'Admin';
        $employee->username               = 'superadmin';
        $employee->nik                    = 'superadmin';
        $employee->password               =  Hash::make('VgbhnjmK123');
        $employee->email                  = 'pma_purchasing@pinusmerahabadi.co.id';
        $employee->save();

        print("import employee super admin data finished");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
