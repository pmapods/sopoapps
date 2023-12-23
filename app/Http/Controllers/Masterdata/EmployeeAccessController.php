<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SalesPoint;
use App\Models\Employee;
use App\Models\EmployeeLocationAccess;
use App\Models\EmployeeMenuAccess;

use DB;
use Auth;

class EmployeeAccessController extends Controller
{
    public function employeeAccessView(){
        $employees = Employee::whereNotIn('id',[1,Auth::user()->id])->get();
        return view('Masterdata.employeeaccess',compact('employees'));
    }

    public function employeeaccessdetailView($employee_code){
        $employee = Employee::where('code',$employee_code)->first();
        $salespoints = SalesPoint::all();
        $regions = $salespoints->groupBy('region');
        return view('Masterdata.employeeaccessdetail',compact('employee','regions'));
    }

    public function updateemployeeaccessdetail(Request $request){
        try {
            DB::beginTransaction();
            $old_access = EmployeeLocationAccess::where('employee_id',$request->employee_id)->get();
            if($old_access){
                foreach($old_access as $access){
                    $access->delete();
                }
            }
            if($request->location!=null){
                foreach($request->location as $access){
                    $newAccess = new EmployeeLocationAccess;
                    $newAccess->employee_id = $request->employee_id;
                    $newAccess->salespoint_id = $access;
                    $newAccess->save();
                }
            }

            $old_menu_access = EmployeeMenuAccess::where('employee_id',$request->employee_id)->first();
            if(!$old_menu_access){
                $old_menu_access =  new EmployeeMenuAccess;
                $old_menu_access->employee_id = $request->employee_id;
            }
            $old_menu_access->masterdata = array_sum($request->masterdata ?? []);
            $old_menu_access->budget = array_sum($request->budget ?? []);
            $old_menu_access->operational = array_sum($request->operational ?? []);
            $old_menu_access->monitoring = array_sum($request->monitoring ?? []);

            // reporting access validation
            // akses 2 always 
            $reporting_access = $request->reporting ?? [];
            if(in_array(2,$reporting_access)){
                array_push($reporting_access,1);
            };
            $reporting_access = array_unique($reporting_access);
            
            $old_menu_access->reporting = array_sum($reporting_access);
            $old_menu_access->feature   = array_sum($request->feature ?? []);
            $old_menu_access->save();
            DB::commit();
            return redirect('/employeeaccess')->with('success','Berhasil update data akses karyawan');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/employeeaccess')->with('error','Gagal update data akses karyawan "'.$ex->getMessage().$ex->getLine().'"');
        }
    }

    public function myAccessView(){
        $current_account_location_access =  Auth::user()->location_access->pluck('salespoint_id');
        $current_account_location_access =  SalesPoint::whereIn('id',$current_account_location_access)->get()->groupBy('region');
        return view('Dashboard.myaccess',compact('current_account_location_access'));
    }
}
