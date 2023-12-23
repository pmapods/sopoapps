<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HOBudgetCategory;
use App\Models\HOBudget;

use DB;
class HOBudgetController extends Controller
{
    
    public function view(){
        $budget_categories = HOBudgetCategory::all();
        $budgets = HOBudget::withTrashed()->get()->sortByDesc('created_at')->sortBy('id');
        return view('Masterdata.hobudget',compact('budget_categories', 'budgets'));
    }

    public function addBudget(Request $request){
        try {
            DB::beginTransaction();
            
            $category = HOBudgetCategory::findOrFail($request->category);
            $count_items = HOBudget::all()->count();
            $code = "HO-".str_repeat("0", 3-strlen($count_items)).$count_items;
            // check budget code if exist add 1 to count_items
            do{
                $ho_budget = HOBudget::where('code',$code)->first();
                if($ho_budget){
                    $found = true;
                    $count_items ++;
                    $code = "HO-".str_repeat("0", 3-strlen( $count_items)).$count_items;
                }else{
                    $found = false;
                }
            }while($found);

            $newBudget = new HOBudget;
            $newBudget->ho_budget_category_id = $request->category;
            $newBudget->code = $code;
            $newBudget->name = $request->name;
            $newBudget->isIT = $request->isIT;
            if($newBudget->isIT == true){
                $newBudget->IT_alias = $request->IT_alias;
            }else{
                $newBudget->IT_alias = null;
            }
            $newBudget->name = $request->name;
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
            $budget = HOBudget::findOrFail($request->id);
            $budget->name            = $request->name;
            $budget->isIT = $request->isIT;
            if($budget->isIT == true){
                $budget->IT_alias = $request->IT_alias;
            }else{
                $budget->IT_alias = null;
            }
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
            $budget = HOBudget::findOrFail($request->id);
            $budget->delete();
            DB::commit();
            return back()->with('success','Berhasil menghapus budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menghapus budget "'.$ex->getMessage().'"');
        }
    }
}
