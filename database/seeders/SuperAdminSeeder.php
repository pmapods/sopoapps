<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeePosition;
use App\Models\Employee;
use App\Models\EmployeeLocationAccess;
use App\Models\EmployeeMenuAccess;
use App\Models\SalesPoint;
use Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $employee = Employee::find(1);
        // kasih full akses untuk ke seluruh area
        foreach(SalesPoint::all() as $salespoint){
            $newAccess = new EmployeeLocationAccess;
            $newAccess->employee_id = $employee->id;
            $newAccess->salespoint_id = $salespoint->id;
            $newAccess->save();
        }

        $masterdata_accesses = config('customvariable.masterdata_accesses');
        $budget_accesses = config('customvariable.budget_accesses');
        $operational_accesses = config('customvariable.operational_accesses');
        $monitoring_accesses = config('customvariable.monitoring_accesses');
        $reporting_accesses = config('customvariable.reporting_accesses');

        $access = new EmployeeMenuAccess;
        $access->employee_id = $employee->id;
        $access->masterdata  = $this->sumArrayGeometry($masterdata_accesses);
        $access->budget      = $this->sumArrayGeometry($budget_accesses);
        $access->operational = $this->sumArrayGeometry($operational_accesses);
        $access->monitoring  = $this->sumArrayGeometry($monitoring_accesses);
        $access->reporting   = $this->sumArrayGeometry($reporting_accesses);
        $access->save();
    }
    private function sumArrayGeometry($array){
        $value = 0;
        for($i = 0; $i < count($array); $i++){
            $value += pow(2,$i);
        }
        return $value;
    }
}
