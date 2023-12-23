<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BudgetPricing;
use App\Models\BudgetPricingCategory;
use App\Models\BudgetBrand;
use App\Models\BudgetType;
use DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetPricingController extends Controller
{
    public function budgetpricingView(){
        $budget_categories = BudgetPricingCategory::all();
        $budgets = BudgetPricing::all()->sortBy('code');
        return view('Masterdata.budgetpricing',compact('budget_categories', 'budgets'));
    }

    public function addBudget(Request $request){
        try {
            DB::beginTransaction();
            $category = BudgetPricingCategory::findOrFail($request->budget_pricing_category_id);
            $count_items = $category->budget_pricing_with_trashed->count() + 1;
            $code = $category->code."-".str_repeat("0", 2-strlen($count_items)).$count_items;
            $flag = false;
            do{
                $checkbudget = BudgetPricing::where('code',$code)->first();
                if(!$checkbudget){
                    $flag = true;
                }else{
                    $flag = false;
                    $count_items++;
                    $code = $category->code."-".str_repeat("0", 2-strlen($count_items)).$count_items;
                }
            }while(!$flag);

            $newBudget = new BudgetPricing;
            $newBudget->budget_pricing_category_id = $request->budget_pricing_category_id;
            $newBudget->code                       = $code;
            $newBudget->name                       = $request->name;
            $newBudget->uom                        = $request->uom;
            $newBudget->isAsset                    = $request->isAsset;
            $newBudget->isIT                       = $request->isIT ?? false;
            if($newBudget->isIT == true){
                $newBudget->IT_alias                    = $request->IT_alias;
            }else{
                $budget->IT_alias = null;
            }
            $newBudget->injs_min_price             = ($request->injs_min_price > 0) ? $request->injs_min_price : null;
            $newBudget->injs_max_price             = ($request->injs_max_price > 0) ? $request->injs_max_price : null;
            $newBudget->outjs_min_price            = ($request->outjs_min_price > 0) ? $request->outjs_min_price : null;
            $newBudget->outjs_max_price            = ($request->outjs_max_price > 0) ? $request->outjs_max_price : null;
            $newBudget->save();
            
            if($request->brand){
                foreach($request->brand as $brand){
                    $newBudgetBrand = new BudgetBrand;
                    $newBudgetBrand->budget_pricing_id = $newBudget->id;
                    $newBudgetBrand->name = $brand;
                    $newBudgetBrand->save();
                }
            }

            if($request->type){
                foreach($request->type as $type){
                    $newBudgetType = new BudgetType;
                    $newBudgetType->budget_pricing_id = $newBudget->id;
                    $newBudgetType->name = $type;
                    $newBudgetType->save();
                }
            }
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
            $budget = BudgetPricing::findOrFail($request->id);
            $budget->isAsset = $request->isAsset;
            $budget->isIT                       = $request->isIT ?? false;
            if($budget->isIT == true){
                $budget->IT_alias                    = $request->IT_alias;
            }else{
                $budget->IT_alias = null;
            }
            $budget->uom            = $request->uom;
            $budget->injs_min_price = ($request->injs_min_price > 0) ? $request->injs_min_price : null;
            $budget->injs_max_price = ($request->injs_max_price > 0) ? $request->injs_max_price : null;
            $budget->outjs_min_price = ($request->outjs_min_price > 0) ? $request->outjs_min_price : null;
            $budget->outjs_max_price = ($request->outjs_max_price > 0) ? $request->outjs_max_price : null;
            $budget->save();

            if($budget->budget_brand){
                foreach($budget->budget_brand as $brand){
                    $brand->delete();
                }
            }

            if($request->brand){
                foreach($request->brand as $brand){
                    $newBudgetBrand = new BudgetBrand;
                    $newBudgetBrand->budget_pricing_id = $budget->id;
                    $newBudgetBrand->name = $brand;
                    $newBudgetBrand->save();
                }
            }

            if($budget->budget_type){
                foreach($budget->budget_type as $type){
                    $type->delete();
                }
            }

            if($request->type){
                foreach($request->type as $type){
                    $newBudgetType = new BudgetType;
                    $newBudgetType->budget_pricing_id = $budget->id;
                    $newBudgetType->name = $type;
                    $newBudgetType->save();
                }
            }
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
            $budget = BudgetPricing::findOrFail($request->id);
            if($budget->budget_brand){
                foreach($budget->budget_brand as $brand){
                    $brand->delete();
                }
            }
            if($budget->budget_type){
                foreach($budget->budget_type as $type){
                    $type->delete();
                 }
            }
            $budget->delete();
            DB::commit();
            return back()->with('success','Berhasil menghapus budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menghapus budget "'.$ex->getMessage().'"');
        }
    }

    public function updateAllTemplate(){
        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_pricing_updateall_template.xlsx");

        $budgets = BudgetPricing::all()->sortBy('code');
        $budgetpricingSheet = $spreadsheet->getSheetByName('Budget Pricing');
        $count_row = 4;
        foreach($budgets as $budget){
            $budgetpricingSheet->setCellValue('A'.$count_row,$budget->code);
            $budgetpricingSheet->setCellValue('B'.$count_row,$budget->name);
            $budgetpricingSheet->setCellValue('C'.$count_row,$budget->uom);
            $budgetpricingSheet->setCellValue('D'.$count_row,($budget->isAsset) ? 'asset' : 'non asset');

            $validation = $budgetpricingSheet->getCell('D'.$count_row)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setFormula1('"asset,non asset"');
            // $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setErrorTitle('Pilihan Tidak tersedia');
            $validation->setError('pilihan hanya asset dan non asset');

            $budgetpricingSheet->setCellValue('E'.$count_row,$budget->brand_list_text());
            $budgetpricingSheet->setCellValue('F'.$count_row,$budget->type_list_text());
            $budgetpricingSheet->setCellValue('G'.$count_row,($budget->injs_min_price) ? $budget->injs_min_price : "");
            $budgetpricingSheet->setCellValue('H'.$count_row,($budget->injs_max_price) ? $budget->injs_max_price : "");
            $budgetpricingSheet->setCellValue('I'.$count_row,($budget->outjs_min_price) ? $budget->outjs_min_price : "");
            $budgetpricingSheet->setCellValue('J'.$count_row,($budget->outjs_max_price) ? $budget->outjs_max_price : "");

            $count_row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Update All Budget Pricing Template.xlsx"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }

    public function updateAllUpdate(Request $request){
        try {
            DB::beginTransaction();
            $json_data = $this->updateAllReadTemplate($request)->getData();
            // dd($json_data);
            if($json_data->error){
                return back()->with('error','Terjadi kesalahan dalam membaca file. (Pesan error : '.$json_data->message.')');
            }

            foreach($json_data->data as $item){
                try{
                    $budgetpricing = BudgetPricing::where('code',$item->code)->first();
                    $budgetpricing->isAsset           = ($item->isAsset == "asset") ? 1 : 0;
                    $budgetpricing->uom               = $item->uom;
                    $budgetpricing->injs_min_price    = intval($item->injs_min_price) ?? null;
                    $budgetpricing->injs_max_price    = intval($item->injs_max_price) ?? null;
                    $budgetpricing->outjs_min_price   = intval($item->outjs_min_price) ?? null;
                    $budgetpricing->outjs_max_price   = intval($item->outjs_max_price) ?? null;

                    if($budgetpricing->budget_brand){
                        foreach($budgetpricing->budget_brand as $brand){
                            $brand->delete();
                        }
                    }

                    if($item->brands){
                        foreach(explode("/",$item->brands) as $brand){
                            if($brand != ""){
                                $newBudgetBrand = new BudgetBrand;
                                $newBudgetBrand->budget_pricing_id = $budgetpricing->id;
                                $newBudgetBrand->name = $brand;
                                $newBudgetBrand->save();
                            }
                        }
                    }
                    
                    if($budgetpricing->budget_type){
                        foreach($budgetpricing->budget_type as $type){
                            $type->delete();
                        }
                    }
                    
                    if($item->types){
                        foreach(explode("/",$item->types) as $type){
                            if($type != ""){
                                $newBudgetType = new BudgetType;
                                $newBudgetType->budget_pricing_id = $budgetpricing->id;
                                $newBudgetType->name = $type;
                                $newBudgetType->save();
                            }
                        }
                    }
                    $budgetpricing->save();
                }catch(\Exception $ex){
                    continue;
                }
            }
            
            DB::commit();
            return back()->with('success','Berhasil update budget pricing');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
        }
    }

    public function updateAllReadTemplate(Request $request){
        try{
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($request->file('file')->getPathName());
    
            $d=$spreadsheet->getSheet(0)->toArray();
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            unset($sheetData[0],$sheetData[1],$sheetData[2]);

            $list = [];
            $errorlist = [];
            foreach ($sheetData as $t) {
                try{
                    if(BudgetPricing::where('code',trim($t[0]))->first() == null) {
                        throw new \Exception("code undefined");
                        continue;
                    }
                    if(!in_array($t[3],["asset","non asset"])){
                        throw new \Exception("asset false");
                        continue;
                    }
                    $data = new \stdClass();
                    $data->code            = $t[0];
                    $data->name            = $t[1];
                    $data->uom            = $t[2];
                    $data->isAsset         = $t[3];

                    $brands = [];
                    foreach(explode("/",$t[4]) as $brand){
                        if(trim($brand) != ""){
                            array_push($brands,trim($brand));
                        }
                    }
                    $data->brands = implode("/",$brands);
                    
                    $types = [];
                    foreach(explode("/",$t[5]) as $type){
                        if(trim($type) != ""){
                            array_push($types,trim($type));
                        }
                    }
                    $data->types = implode("/",$types);

                    $data->injs_min_price  = intval($t[6]);
                    $data->injs_max_price  = intval($t[7]);
                    $data->outjs_min_price = intval($t[8]);
                    $data->outjs_max_price = intval($t[9]);

                    array_push($list,$data);
                }catch(\Throwable $th){
                    array_push($errorlist,$th->getMessage());
                    continue;
                }
            }
    
            return response()->json([
                'error' => false,
                'data' => $list,
                'errordata' => $errorlist
            ]);
        }catch(\Exception $ex){
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage(),
            ]);
        }
    }

}
