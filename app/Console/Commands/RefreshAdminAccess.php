<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesPoint;
use App\Models\EmployeeLocationAccess;
use App\Models\EmployeeMenuAccess;

use DB;

class RefreshAdminAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:refreshaccess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update akses admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $admin_location_access = EmployeeLocationAccess::where('employee_id',1)->get();
            foreach($admin_location_access as $access) {
                $access->delete();
            }
    
            foreach(SalesPoint::all() as $salespoint){
                $newAccess = new EmployeeLocationAccess;
                $newAccess->employee_id = 1;
                $newAccess->salespoint_id = $salespoint->id;
                $newAccess->save();
            }
    
            $admin_menu_access = EmployeeMenuAccess::where('employee_id',1)->first();
            $admin_menu_access->delete();
    
            $masterdata_accesses = config('customvariable.masterdata_accesses');
            $budget_accesses = config('customvariable.budget_accesses');
            $operational_accesses = config('customvariable.operational_accesses');
            $monitoring_accesses = config('customvariable.monitoring_accesses');
            $reporting_accesses = config('customvariable.reporting_accesses');
            $feature_accesses = config('customvariable.feature_accesses');

            $access = new EmployeeMenuAccess;
            $access->employee_id = 1;
            $access->masterdata  = $this->sumArrayGeometry($masterdata_accesses);
            $access->budget      = $this->sumArrayGeometry($budget_accesses);
            $access->operational = $this->sumArrayGeometry($operational_accesses);
            $access->monitoring  = $this->sumArrayGeometry($monitoring_accesses);
            $access->reporting   = $this->sumArrayGeometry($reporting_accesses);
            $access->feature   = $this->sumArrayGeometry($feature_accesses);
            $access->save();
            DB::commit();
            echo 'success';
        } catch (\Throwable $th) {
            DB::rollback();
            echo 'error';
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
