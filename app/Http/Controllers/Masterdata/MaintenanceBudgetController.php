<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceBudgetCategory;
use App\Models\MaintenanceBudget;

use DB;
class MaintenanceBudgetController extends Controller
{
    
    public function view(){
        $budget_categories = MaintenanceBudgetCategory::all();
        $budgets = MaintenanceBudget::withTrashed()->get()->sortBy('code');
        return view('Masterdata.maintenancebudget',compact('budget_categories', 'budgets'));
    }

    public function addBudget(Request $request){
        try {
            DB::beginTransaction();
            
            $category = MaintenanceBudgetCategory::findOrFail($request->category);
            $count_items = $category->maintenance_budget()->withTrashed()->count();
            $code = $category->code."-".str_repeat("0", 2-strlen($count_items)).$count_items;
            // check budget code if exist add 1 to count_items
            do{
                $maintenance_budget = MaintenanceBudget::where('code',$code)->first();
                if($maintenance_budget){
                    $found = true;
                    $count_items ++;
                    $code = $category->code."-".str_repeat("0", 2-strlen($count_items)).$count_items;
                }else{
                    $found = false;
                }
            }while($found);

            $newBudget = new MaintenanceBudget;
            $newBudget->maintenance_budget_category_id = $request->category;
            $newBudget->code = $code;
            $newBudget->name = $request->name;
            $newBudget->uom  = $request->uom;
            $newBudget->save();
            DB::commit();
            return back()->with('success','Berhasil menambah budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menambah budget "'.$ex->getMessage().'"');
        }
    }
    
    public function updateBudget(Request $request){
        try {
            DB::beginTransaction();
            $budget = MaintenanceBudget::findOrFail($request->id);
            $budget->name            = $request->name;
            $budget->uom            = $request->uom;
            $budget->save();
            DB::commit();
            return back()->with('success','Berhasil mengubah budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal mengubah budget "'.$ex->getMessage().'"');
        }
    }

    public function deleteBudget(Request $request){
        try {
            DB::beginTransaction();
            $budget = MaintenanceBudget::findOrFail($request->id);
            $budget->delete();
            DB::commit();
            return back()->with('success','Berhasil menghapus budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menghapus budget "'.$ex->getMessage().'"');
        }
    }
}
