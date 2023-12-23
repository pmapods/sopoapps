<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Employee;
use App\Models\EmployeeMenuAccess;
use App\Models\Authorization;
use App\Models\AuthorizationDetail;
use App\Models\SalesPoint;

use DB;

class EmployeeAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::beginTransaction();
            EmployeeMenuAccess::truncate();
            // tambah satu user super admin
            //SUPERADMIN ACCESS
            // kasih full akses untuk ke seluruh area
            $superadmin_employee = Employee::find(1);
    
            $masterdata_accesses = config('customvariable.masterdata_accesses');
            $budget_accesses = config('customvariable.budget_accesses');
            $operational_accesses = config('customvariable.operational_accesses');
            $monitoring_accesses = config('customvariable.monitoring_accesses');
            $reporting_accesses = config('customvariable.reporting_accesses');
    
            $access = new EmployeeMenuAccess;
            $access->employee_id = $superadmin_employee->id;
            $access->masterdata  = $this->sumArrayGeometry($masterdata_accesses);
            $access->budget      = $this->sumArrayGeometry($budget_accesses);
            $access->operational = $this->sumArrayGeometry($operational_accesses);
            $access->monitoring  = $this->sumArrayGeometry($monitoring_accesses);
            $access->reporting   = $this->sumArrayGeometry($reporting_accesses);
            $access->save();
    
            // EmployeeAccess
            // employee access berdasarkan matriks approval seeder
            $employees = Employee::where('id','!=',1)->get();
            foreach($employees as $employee){
                $employee_authorization_detail = AuthorizationDetail::where("employee_id",$employee->id)->get();
                $employee_authorizations = Authorization::whereIn("id",$employee_authorization_detail->pluck("authorization_id")->unique())->get();
                $form_types = $employee_authorizations->pluck('form_type')->unique();
                $access = EmployeeMenuAccess::where('employee_id', $employee->id)->first();
                if(!$access){
                    $access = new EmployeeMenuAccess;
                    $access->employee_id = $employee->id;
                    $access->masterdata  = 0;
                    $access->budget      = 0;
                    $access->operational = 0;
                    $access->monitoring  = 0;
                    $access->reporting   = 0;
                    $access->save();
                }
                $masterdata_access   = 0;
                $budget_access       = 0;
                $operational_access  = 0;
                $monitoring_access   = 0;
                $reporting_access    = 0;
                foreach($form_types as $form_type){
                    if(in_array($form_type,[0,4,5,6,7,8,9])){
                        // jika belum ada akses ticketing di menu operational tambahkan (1)
                        if(($operational_access & 1) == 0){
                            $operational_access += 1;
                        }
                    }

                    if($form_type == 0){
                        // jika belum ada akses ticketing dan pr di menu operational tambahkan (1 & 4)
                        if(($operational_access & 1) == 0){
                            $operational_access += 1;
                        }
                        if(($operational_access & 4) == 0){
                            $operational_access += 4;
                        }
                    } 
                    if($form_type == 1){
                        // jika belum ada akses bidding di menu operational tambahkan (2)
                        if(($operational_access & 2) == 0){
                            $operational_access += 2;
                        }
                    }
                    if($form_type == 2){
                        // jika belum ada akses pr di menu operational tambahkan (4)
                        if(($operational_access & 4) == 0){
                            $operational_access += 4;
                        }
                    }
                    if($form_type == 3){
                        // jika belum ada akses po di menu operational tambahkan (8)
                        if(($operational_access & 8) == 0){
                            $operational_access += 8;
                        }
                    }
                    if(in_array($form_type,[10,11])){
                        $budget_access = 15;
                    }
                }
                $access->masterdata  = $masterdata_access;
                $access->budget      = $budget_access;
                $access->operational = $operational_access;
                $access->monitoring  = $monitoring_access;
                $access->reporting   = $reporting_access;
                $access->save();
            }
            print("Set employee access done !");
            
            DB::commit();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }catch(\Exception $ex){
            print($ex->getMessage()."(".$ex->getLine().")");
            DB::rollback();
        }
        
    }

    private function sumArrayGeometry($array){
        $value = 0;
        for($i = 0; $i < count($array); $i++){
            $value += pow(2,$i);
        }
        return $value;
    }
}
