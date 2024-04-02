<?php

namespace App\Http\Controllers\Masterdata;
use DB;
use Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use App\Models\PoManual;
use App\Models\PoManualNew;
use App\Models\InventoryBudget;
use App\Models\ArmadaBudget;
use App\Models\ArmadaType;
use App\Models\AssumptionBudget;
use App\Models\HOBudgetUpload;
use App\Models\BudgetUpload;
use App\Models\SalesPoint;
use App\Models\Authorization;
use Carbon\Carbon;

class PoManualController extends Controller
{
    public function pomanualView(){
        $employee_access = Auth::user()->location_access->pluck('salespoint_id');
        $salespoints = SalesPoint::whereIn('id', $employee_access)->get();
        $po_manuals = PoManual::where('status_upload', 'success')->orderBy('created_at', 'desc')->get();
        $po_manual_failed = PoManual::where('status_upload', 'failed')->orderBy('created_at', 'desc')->get();
        return view('Masterdata.pomanual',compact('po_manuals','salespoints', 'po_manual_failed'));
    }

    public function addPoManual(Request $request){
        $file_mimes = array('application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/wps-office.xlsx', 'application/wps-office.xls');

        try {
            DB::beginTransaction();
            if(isset($_FILES['po_manual_file']['name']) && in_array($_FILES['po_manual_file']['type'], $file_mimes)) {

                $arr_file = explode('.', $_FILES['po_manual_file']['name']);
                $extension = end($arr_file);

                if ('xls' == $extension){
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                } else if ('csv' == $extension) {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                }

                $spreadsheet = $reader->load($_FILES['po_manual_file']['tmp_name']);
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                $poManual = null;
                $tgl_pengajuan = null;

                for($i = 7;$i < count($sheetData);$i++)
                {
                    if(!empty($sheetData[$i][0])) {
                        $poManual                           = new PoManual;
                        $poManual->po_number                = $sheetData[$i][0];
                        $poManual->po_reference_number      = $sheetData[$i][1];

                        $salespoint_code = $sheetData[$i][2];

                        //cek salespoint_id base on salespoint_code
                        $get_salespoints = SalesPoint::where('code', $salespoint_code)->first();
                        $poManual->salespoint_id            = empty($get_salespoints->id) ? null : $get_salespoints->id;
                        $poManual->salespoint_name          = empty($get_salespoints->name) ? null : $get_salespoints->name;

                        $poManual->type_budget              = empty($sheetData[$i][4]) ? null : $sheetData[$i][4];
                        if (str_contains($sheetData[$i][5], "-")) {
                            $tgl_pengajuan = Carbon::createFromFormat('Y-m-d', $sheetData[$i][5])->format('Y-m-d');
                        } else {
                            $tgl_pengajuan= null;
                        }
                        $poManual->item_code                = empty($sheetData[$i][6]) ? null : $sheetData[$i][6];
                        $poManual->item_name                = empty($sheetData[$i][7]) ? null : $sheetData[$i][7];
                        $poManual->vendor_code              = empty($sheetData[$i][8]) ? null : $sheetData[$i][8];
                        $poManual->vendor_name              = empty($sheetData[$i][9]) ? null : $sheetData[$i][9];
                        $poManual->gs_plate                 = empty($sheetData[$i][10]) ? null : $sheetData[$i][10];
                        $poManual->gt_plate                 = empty($sheetData[$i][11]) ? null : $sheetData[$i][11];
                        $poManual->isNiaga                  = ($sheetData[$i][12] == "Niaga") ? 1 : 0;
                        $poManual->armada_name              = empty($sheetData[$i][13]) ? null : $sheetData[$i][13];

                        //cek armada_type_id base on armada type name
                        $get_armada_type = ArmadaType::where('name', $poManual->armada_name)->first();
                        $poManual->armada_type_id           = empty($get_armada_type->id) ? null : $get_armada_type->id;

                        $poManual->qty                      = empty($sheetData[$i][14]) ? 0 : intval($sheetData[$i][14]);
                        if (str_contains($sheetData[$i][15], "-")) {
                            $poManual->start_date = Carbon::createFromFormat('Y-m-d', $sheetData[$i][15])->format('Y-m-d');
                        } else {
                            $poManual->start_date = null;
                        }
                        if (str_contains($sheetData[$i][16], "-")) {
                            $poManual->end_date = Carbon::createFromFormat('Y-m-d', $sheetData[$i][16])->format('Y-m-d');
                        } else {
                            $poManual->end_date = null;
                        }
                        $poManual->jenis_item               = empty($sheetData[$i][17]) ? null : $sheetData[$i][17];
                        $poManual->jenis_pengadaan          = ($sheetData[$i][18] == "Baru") ? 1 : 0;
                        $poManual->jenis_it                 = ($sheetData[$i][19] == "IT") ? 1 : 0;

                        // jadikan string ke integer
                        $string_harga = preg_replace("/[^0-9]/", "", $sheetData[$i][20]);
                        $poManual->harga                    = empty($string_harga) ? 0 : intval($string_harga);

                        $poManual->category_name            = empty($sheetData[$i][21]) ? null : $sheetData[$i][21];
                        $poManual->budget_or_non_budget     = ($sheetData[$i][22] == "Budget") ? 1 : 0;
                        $poManual->keterangan               = empty($sheetData[$i][23]) ? null : $sheetData[$i][23];
                        $poManual->status                   = 3;
                        $poManual->created_at               = now()->format('Y-m-d H:i:s');

                        $poManual->barang_jasa_form_bidding_filepath                         = null;
                        $poManual->barang_jasa_pr_manual_filepath                            = null;
                        $poManual->barang_jasa_po_filepath                                   = null;
                        $poManual->barang_jasa_lpb_filepath                                  = null;
                        $poManual->barang_jasa_invoice_filepath                              = null;
                        $poManual->armada_pr_manual_filepath                                 = null;
                        $poManual->armada_po_filepath                                        = null;
                        $poManual->armada_bastk_filepath                                     = null;
                        $poManual->security_cit_pestcontrol_merchandiser_pr_manual_filepath  = null;
                        $poManual->security_cit_pestcontrol_merchandiser_po_filepath         = null;
                        $poManual->security_lpb_filepath                                     = null;
                        $poManual->cit_lpb_filepath                                          = null;
                        $poManual->pestcontrol_lpb_filepath                                  = null;
                        $poManual->merchandiser_lpb_filepath                                 = null;

                        // cek budget by type
                        $this->createBudget($poManual, $tgl_pengajuan);

                        $cekPoManual = PoManual::where('po_number', $poManual->po_number)->first();
                        if ($cekPoManual == null){
                            $poManual->save();

                        } else if ($cekPoManual->status_upload == 'failed'){
                            $cekPoManual->status_upload = $poManual->status_upload;
                            $cekPoManual->reason_upload = $poManual->reason_upload;
                            $cekPoManual->update();
                        }
                    } else {
                        break;
                    }
                }
            }

            DB::commit();
            return back()->with('success','Berhasil upload PO Manual');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal Upload PO Manual ('.$ex->getMessage()."(".$ex->getLine().")"."(".$ex->getFile().")");
        }

    }

    private function createBudget($poManual, $tgl_pengajuan){
        switch (strtolower($poManual->type_budget)) {
            case 'armada':

                $start_year = time();
                $year = date('Y', $start_year);
                $budget_upload = BudgetUpload::where('status', 1)
                ->where('type', 'armada')
                ->where('salespoint_id', $poManual->salespoint_id)
                // ->where('year', '=', '2023')
                ->where('year', '=', $year)
                ->first();

                if ($budget_upload){
                    $budget_armada = ArmadaBudget::where('budget_upload_id', $budget_upload->id)
                    ->where('vendor_code', $poManual->vendor_code)
                    ->where('armada_type_id',$poManual->armada_type_id)
                    ->whereNull('deleted_at')
                    ->first();

                    if ($budget_armada){
                        $budget_armada->qty     -= (int)$poManual->qty;
                        $budget_armada->value   -= (int)$poManual->harga;
                        if ($budget_armada->qty >= 0 && $budget_armada->value >= 0){
                            $budget_armada->save();

                            $poManual->status_upload = 'success';
                            $poManual->reason_upload = '';
                        }else{
                            $poManual->status_upload = 'failed';
                            $poManual->reason_upload = 'qty / value di budget armada tidak mencukupi';
                        }
                    }else{
                        $poManual->status_upload = 'failed';
                        $poManual->reason_upload = 'vendor code '.$poManual->vendor_code.' dan armada type name '.$poManual->armada_name.' di budget armada tidak ditemukan';
                    }
                }else{
                    $poManual->status_upload = 'failed';
                    $poManual->reason_upload = 'budget upload dengan type armada untuk tahun '.$year.' tidak ditemukan';
                }

                break;
            case 'assumption':
                $start_year = time();
                $year = date('Y', $start_year);

                $budget_upload = BudgetUpload::where('status', 1)
                ->where('type', 'assumption')
                ->where('salespoint_id', $poManual->salespoint_id)
                // ->where('year', '=', '2023')
                ->where('year', '=', $year)
                ->first();

                if ($budget_upload){
                    $budget_assumption = AssumptionBudget::where('budget_upload_id', $budget_upload->id)
                    ->where('code', $poManual->item_code)
                    ->whereNull('deleted_at')
                    ->first();

                    if ($budget_assumption){
                        $budget_assumption->qty     -= (int)$poManual->qty;
                        $budget_assumption->value   -= (int)$poManual->harga;
                        if ($budget_assumption->qty >= 0 && $budget_assumption->value >= 0){
                            $budget_assumption->save();

                            $poManual->status_upload = 'success';
                            $poManual->reason_upload = '';
                        }else{
                            $poManual->status_upload = 'failed';
                            $poManual->reason_upload = 'qty / value di budget assumption tidak mencukupi';
                        }
                    }else{
                        $poManual->status_upload = 'failed';
                        $poManual->reason_upload = 'code '.$poManual->item_code.' di budget assumption tidak ditemukan';
                    }
                }else{
                    $poManual->status_upload = 'failed';
                    $poManual->reason_upload = 'budget upload dengan type assumption untuk tahun '.$year.' tidak ditemukan';
                }

                break;
            case 'ho budget':
                $start_year = time();
                $year = date('Y', $start_year);
                // $year = '2023';

                $budget_upload = BudgetUpload::leftJoin('ho_budget_upload', function ($join) use ($poManual) {
                    $join->on('ho_budget_upload.budget_upload_id', '=', 'budget_upload.id')
                        ->where('ho_budget_upload.code', '=', $poManual->item_code)
                        ->whereNull('ho_budget_upload.deleted_at');
                })
                ->leftJoin('ho_budget', function ($join) use ($poManual) {
                    $join->on('ho_budget.id', '=', 'ho_budget_upload.ho_budget_id')
                        ->where('ho_budget.code', '=', $poManual->item_code)
                        ->where('ho_budget.isIT', '=', $poManual->jenis_it)
                        ->whereNull('ho_budget.deleted_at');
                })
                ->where('budget_upload.type', '=', 'ho')
                ->where('salespoint_id', $poManual->salespoint_id)
                ->where('year', '=', $year)
                ->where('budget_upload.division', '=', $poManual->keterangan)
                ->where('budget_upload.status', '=', 1)
                ->first();

                if ($budget_upload){
                    $targetData = null;
                    $anotherMonth = [];
                    $targetMonth = date('n', strtotime($tgl_pengajuan));
                    $ho_budget_value = json_decode($budget_upload->values);
                    foreach ($ho_budget_value as $key => $item) {
                        if ($item->months == $targetMonth) {
                            $targetData = $item;
                            break;
                        }
                    }

                    foreach ($ho_budget_value as $item) {
                        if ($item->months != $targetMonth) {
                            $anotherMonth[] = $item;
                        }
                    }

                    $qty_reduction = $targetData->qty - $poManual->qty;
                    $value_reduction = $targetData->value - $poManual->harga;

                    if ($qty_reduction >= 0 && $value_reduction >= 0){
                        $stringArray = ['months', 'qty', 'value'];
                        $ValueArray = [(int)$targetMonth, $qty_reduction, $value_reduction];
                        $outputArray = array_combine($stringArray, $ValueArray);

                        $object = new \stdClass();
                        foreach ($outputArray as $key => $value) {
                            $object->$key = $value;
                        }

                        $myArray[] = $object;
                        $merge = array_merge($myArray, $anotherMonth);
                        $hbupm = HOBudgetUpload::where('ho_budget_upload.budget_upload_id', '=', $budget_upload->budget_upload_id)
                            ->where('ho_budget_upload.code', '=', $budget_upload->code)->first();
                        $hbupm->values = json_encode($merge);
                        $hbupm->save();

                        $poManual->status_upload = 'success';
                        $poManual->reason_upload = '';
                    }else{
                        $poManual->status_upload = 'failed';
                        $poManual->reason_upload = 'qty / value di HO Budget Upload tidak mencukupi';
                    }



                }else{
                    $poManual->status_upload = 'failed';
                    $poManual->reason_upload = 'budget upload dengan type HO untuk tahun '.$year.' tidak ditemukan';
                }

                $budgetModel = new HOBudgetUpload;

                break;
            case 'inventory':
                $start_year = time();
                $year = date('Y', $start_year);

                $budget_upload = BudgetUpload::where('status', 1)
                ->where('type', 'inventory')
                ->where('salespoint_id', $poManual->salespoint_id)
                // ->where('year', '=', '2023')
                ->where('year', '=', $year)
                ->first();

                if ($budget_upload){
                    $budget_inventory = InventoryBudget::where('budget_upload_id', $budget_upload->id)
                    ->where('code', $poManual->item_code)
                    ->whereNull('deleted_at')
                    ->first();

                    if ($budget_inventory){
                        $budget_inventory->qty     -= (int)$poManual->qty;
                        $budget_inventory->value   -= (int)$poManual->harga;
                        if ($budget_inventory->qty >= 0 && $budget_inventory->value >= 0){
                            $budget_inventory->save();

                            $poManual->status_upload = 'success';
                            $poManual->reason_upload = '';
                        }else{
                            $poManual->status_upload = 'failed';
                            $poManual->reason_upload = 'qty / value di budget inventory tidak mencukupi';
                        }
                    }else{
                        $poManual->status_upload = 'failed';
                        $poManual->reason_upload = 'budget inventory dengan code '.$poManual->item_code.' tidak ditemukan';
                    }
                }else{
                    $poManual->status_upload = 'failed';
                    $poManual->reason_upload = 'budget upload dengan type inventory untuk tahun '.$year.' tidak ditemukan';
                }

                break;
            default:
                return 'item_type_undefined';
                break;
        }
    }

}
