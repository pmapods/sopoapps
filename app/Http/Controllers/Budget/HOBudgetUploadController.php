<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;

use App\Models\EmployeeLocationAccess;
use App\Models\SalesPoint;
use App\Models\Authorization;
use App\Models\HOBudget;
use App\Models\HOBudgetUpload;
use App\Models\BudgetUpload;
use App\Models\BudgetUploadAuthorization;

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

class HOBudgetUploadController extends Controller
{
    public function hoBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        if (request()->input('status') == -1) {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'ho')
                ->where('status', 2)
                ->withTrashed()
                ->get();
        } else {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'ho')
                ->where('status', '!=', 2)
                ->get();
        }
        return view('Budget.HO.hobudget', compact('budgets'));
    }

    public function hoBudgetDetailView($budget_upload_code)
    {
        $budget = BudgetUpload::where('type', 'ho')
            ->where('code', $budget_upload_code)->first();

        $salespoint = SalesPoint::find($budget->salespoint_id);
        $newauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 10)->get();
        $reviseauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 11)->get();

        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if ($budget == null) {
            return redirect('/ho_budget')->with('error', 'Kode budget tidak tersedia.');
        } else {
            if (!$user_location_access->contains($budget->salespoint_id)) {
                return redirect('/ho_budget')->with('error', 'Anda tidak memiliki akses untuk budget berikut. Tidak memiliki akses salespoint "' . $budget->salespoint->name . '"');
            }
            return view('Budget.HO.hobudgetdetail', compact('budget', 'newauthorization', 'reviseauthorization'));
        }
    }

    public function addHOBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->where('status', 5)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        return view('Budget.HO.addhobudget', compact('available_salespoints'));
    }

    public function createBudgetRequest(Request $request)
    {
        try {
            DB::beginTransaction();
            $is_pending_request = BudgetUpload::where('status', 0)
                ->where('type', 'ho')
                ->where('division', $request->divisi)
                ->where('salespoint_id', $request->salespoint_id)
                ->where('year', '=', $request->year)
                ->first();
            if ($is_pending_request) {
                $salespoint = SalesPoint::find($request->salespoint_id);
                return back()->with('error', 'Harap menyelesaikan request budget HO pada salespoint ' . $salespoint->name . ' sebelumnya terlebih dahulu dengan divisi ' . $request->divisi . '. dengan kode request ' . $is_pending_request->code);
            }
            $budget_request_count = BudgetUpload::whereBetween('created_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ])
                ->where('salespoint_id', $request->salespoint_id)
                ->withTrashed()
                ->count();
            $salespoint = SalesPoint::find($request->salespoint_id);
            do {
                $code = "BUDGET-HO-" . $salespoint->initial . "-" . now()->translatedFormat('ymd') . '-' . str_repeat("0", 3 - strlen($budget_request_count + 1)) . ($budget_request_count + 1);
                $checkbudget = BudgetUpload::where('code', $code)->first();
                if ($checkbudget) {
                    $flag = false;
                    $budget_request_count++;
                } else {
                    $flag = true;
                }
            } while (!$flag);

            // set old budget  status on selected salespoint to non active
            $oldbudgets = BudgetUpload::where('type', 'ho')
                ->where('salespoint_id', $request->salespoint_id)
                ->where('division', $request->divisi)
                ->whereIn('status', [-1, 0, 1])
                ->where('year', '=', $request->year)
                ->get();
            foreach ($oldbudgets as $oldbudget) {
                $oldbudget->status         = 2;
                $oldbudget->reject_notes   = 'Replaced by ' . $code;
                $oldbudget->rejected_by   = Auth::user()->id;
                $oldbudget->save();
            }

            $newBudget                       = new BudgetUpload;
            $newBudget->salespoint_id        = $request->salespoint_id;
            $newBudget->division             = $request->divisi;
            $newBudget->year                 = $request->year;
            $newBudget->type                 = 'ho';
            $newBudget->code                 = $code;
            $newBudget->status               = 0;
            $newBudget->created_by           = Auth::user()->id;
            $newBudget->save();

            $json_data = $this->readTemplate($request)->getData();
            if ($json_data->error) {
                return back()->with('error', 'Terjadi kesalahan dalam membaca file. (Pesan error : ' . $json_data->message . ')');
            }
            if (count($json_data->data->monthly) == 0) {
                return back()->with('error', 'Minimal satu item dalam pengajuan budget');
            }
            // validasi divisi, dan kode salepoint apakah sesuai dengan request
            $checksalespoint = SalesPoint::where('code', $json_data->info->salespoint_code)->first();
            if ($request->salespoint_id != $checksalespoint->id) {
                return back()->with('error', 'Salespoint yang di upload tidak sesuai');
            }
            if ($request->divisi != $json_data->info->division) {
                return back()->with('error', 'Divisi yang di upload tidak sesuai');
            }
            if ($request->year != $json_data->info->year) {
                return back()->with('error', 'Tahun yang di upload tidak sesuai');
            }

            foreach ($json_data->data->monthly as $item) {
                $newHOBudget                         = new HOBudgetUpload;
                $newHOBudget->budget_upload_id       = $newBudget->id;
                $newHOBudget->ho_budget_id           = $item->ho_budget_id;
                $newHOBudget->code                   = $item->code;
                $newHOBudget->category               = $item->category;
                $newHOBudget->name                   = $item->name;
                $newHOBudget->values                 = json_encode($item->values);
                $newHOBudget->save();
            }

            $authorization = Authorization::findOrFail($request->authorization_id);
            $notes_authorization = $authorization->notes;
            // check berdasarkan notes otorisasi apakah sesuai divisinya
            if (!str_contains(strtolower($notes_authorization), strtolower($newBudget->division))) {
                return back()->with('error', 'Pilih matriks approval yang sesuai dengan divisi');
            }
            // cek berdasarkan otorisasi pertama apakah sesuai dengan pembuat
            if ($authorization->authorization_detail->first()->employee_id != Auth::user()->id) {
                return back()->with('error', 'Pilihan Otorisasi pertama tidak sesuai dengan pembuat ho budget. (otorisasi pertama = ' . $authorization->authorization_detail->first()->employee->name . ',akun login pembuat saat ini ' . Auth::user()->name . ')');
            }
            foreach ($authorization->authorization_detail as $key => $authorization) {
                $newAuthorization                    = new BudgetUploadAuthorization;
                $newAuthorization->budget_upload_id  = $newBudget->id;
                $newAuthorization->employee_id       = $authorization->employee_id;
                $newAuthorization->employee_name     = $authorization->employee->name;
                $newAuthorization->as                = $authorization->sign_as;
                $newAuthorization->employee_position = $authorization->employee_position->name;
                $newAuthorization->level             = $key + 1;
                $newAuthorization->save();
            }

            // recall the new one
            $authorization = $newBudget->current_authorization();
            DB::commit();
            return redirect('/ho_budget/' . $code)->with('success', 'Berhasil membuat request upload budget, otorisasi saat ini oleh ' . $authorization->employee_name);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/ho_budget')->with('error', 'Gagal membuat request upload budget (' . $ex->getMessage() . ')');
        }
    }

    public function nonActiveBudget($budget_upload_code)
    {
        try {
            DB::beginTransaction();

            $oldbudgets = BudgetUpload::where('type', 'ho')
                ->where('code', $budget_upload_code)->first();

            $oldbudgets->status         = 2;
            $oldbudgets->reject_notes   = 'Budget Di Non Aktifkan';
            $oldbudgets->rejected_by    = Auth::user()->id;
            $oldbudgets->save();

            DB::commit();
            return redirect('/ho_budget/')->with('success', 'Berhasil Non Aktifkan Budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/ho_budget/')->with('error', 'Gagal Non Aktifkan Budget (' . $ex->getMessage() . ')');
        }
    }

    public function reviseBudget(Request $request)
    {
        try {
            DB::beginTransaction();
            $budget                 = BudgetUpload::findOrFail($request->budget_upload_id);
            $budget->status         = 0;
            $budget->save();

            foreach ($budget->budget_detail as $b) {
                $b->delete();
            }

            $json_data = $this->readTemplate($request)->getData();

            if ($json_data->error) {
                return back()->with('error', 'Terjadi kesalahan dalam membaca file. (Pesan error : ' . $json_data->message . ')');
            }
            if (count($json_data->data->monthly) == 0) {
                return back()->with('error', 'Minimal satu item dalam pengajuan budget');
            }
            // validasi divisi, dan kode salepoint apakah sesuai dengan request
            $checksalespoint = SalesPoint::where('code', $json_data->info->salespoint_code)->first();
            if ($budget->salespoint_id != $checksalespoint->id || $budget->division != $json_data->info->division) {
                return back()->with('error', 'Divisi dan Salespoint yang di upload tidak sesuai');
            }

            foreach ($json_data->data->monthly as $item) {
                $newHOBudget                         = new HOBudgetUpload;
                $newHOBudget->budget_upload_id       = $budget->id;
                $newHOBudget->ho_budget_id           = $item->ho_budget_id;
                $newHOBudget->code                   = $item->code;
                $newHOBudget->category               = $item->category;
                $newHOBudget->name                   = $item->name;
                $newHOBudget->values                 = json_encode($item->values);
                $newHOBudget->save();
            }

            // reset otorisasi dengan otorisasi baru
            foreach ($budget->authorizations as $authorization) {
                $authorization->delete();
            }

            $authorization = Authorization::findOrFail($request->authorization_id);
            foreach ($authorization->authorization_detail as $key => $authorization) {
                $newAuthorization                    = new BudgetUploadAuthorization;
                $newAuthorization->budget_upload_id  = $budget->id;
                $newAuthorization->employee_id       = $authorization->employee_id;
                $newAuthorization->employee_name     = $authorization->employee->name;
                $newAuthorization->as                = $authorization->sign_as;
                $newAuthorization->employee_position = $authorization->employee_position->name;
                $newAuthorization->level             = $key + 1;
                $newAuthorization->save();
            }

            $current_authorization = BudgetUpload::find($budget->id)->current_authorization();
            DB::commit();
            return redirect('/ho_budget/' . $budget->code)->with('success', 'Berhasil membuat revisi upload budget, otorisasi saat ini oleh ' . $current_authorization->employee_name);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat revisi upload budget (' . $ex->getMessage() . $ex->getLine() . ')');
        }
    }

    public function terminateBudget(Request $request)
    {
        try {
            DB::beginTransaction();

            $budget                 = BudgetUpload::findOrFail($request->budget_upload_id);
            $budget->status         = 2;
            $budget->reject_notes   = $request->reason;
            $budget->rejected_by    = Auth::user()->id;
            $budget->save();

            DB::commit();
            return redirect('/ho_budget?status=-1')->with('success', 'Berhasil membatalkan pengadaan upload budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membatalkan pengadaan upload budget');
        }
    }

    public function approveBudgetAuthorization(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $budget = BudgetUpload::findOrFail($request->budget_upload_id);
            $current_authorization = $budget->current_authorization();
            if ($current_authorization->employee_id != Auth::user()->id) {
                if ($return_data_type == "api") {
                    return response()->json([
                        "error" => true,
                        "message" => 'Otorisasi saat ini tidak sesuai dengan akun login.'
                    ]);
                } else {
                    return back()->with('error', 'Otorisasi saat ini tidak sesuai dengan akun login.');
                }
            } else {
                $current_authorization->status = 1;
                $current_authorization->save();
            }

            $current_authorization = $budget->current_authorization();
            if ($current_authorization == null) {
                $budget->status = 1;
                $budget->save();
                DB::commit();
                if ($return_data_type == "api") {
                    return response()->json([
                        "error" => false,
                        "message" => 'Otorisasi request budget ' . $budget->code . ' telah selesai. Status Budget sudah aktif'
                    ]);
                } else {
                    return back()->with('success', 'Otorisasi request budget ' . $budget->code . ' telah selesai. Status Budget sudah aktif');
                }
            } else {
                DB::commit();
                if ($return_data_type == "api") {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan approval otorisasi request budget ' . $budget->code . '. Otorisasi selanjutnya oleh ' . $current_authorization->employee_name
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan approval otorisasi request budget ' . $budget->code . '. Otorisasi selanjutnya oleh ' . $current_authorization->employee_name);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == "api") {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi ' . $ex->getMessage());
            }
        }
    }

    public function rejectBudgetAuthorization(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $budget = BudgetUpload::findOrFail($request->budget_upload_id);
            $current_authorization = $budget->current_authorization();
            if ($current_authorization->employee_id != Auth::user()->id) {
                if ($return_data_type == "api") {
                    return response()->json([
                        "error" => true,
                        "message" => 'Otorisasi saat ini tidak sesuai dengan akun login.'
                    ]);
                } else {
                    return back()->with('error', 'Otorisasi saat ini tidak sesuai dengan akun login.');
                }
            }

            $budget->status = -1;
            $budget->rejected_by = Auth::user()->id;
            $budget->reject_notes = $request->reason;
            $budget->save();

            // reset all authorization
            foreach ($budget->authorizations as $authorization) {
                $authorization->status = 0;
                $authorization->save();
            }

            DB::commit();
            if ($return_data_type == "api") {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil menolak request budget ' . $budget->code . ' dengan alasan ' . $request->reason
                ]);
            } else {
                return back()->with('success', 'Berhasil menolak request budget ' . $budget->code . ' dengan alasan ' . $request->reason);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == "api") {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal menolak request budget ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal menolak request budget ' . $ex->getMessage());
            }
        }
    }

    public function getBudgetTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_ho_template.xlsx");

        // SET SALLESPOINT SELECTION
        $salespointSheet = $spreadsheet->getSheetByName('Pilihan Salespoint');
        $salespoints_ho = SalesPoint::where('status', 5)->get()->sortBy('name');
        $count_row = 3;
        foreach ($salespoints_ho as $salespoint) {
            $salespointSheet->setCellValue('A' . $count_row, $salespoint->name . " || " . $salespoint->code);
            $count_row++;
        }
        $start_salespoint_row = 3;
        $end_salespoint_row = $count_row;

        $monthlySheet = $spreadsheet->getSheetByName('Budget Input');
        $validation = $monthlySheet->getCell('B2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('=\'Pilihan Salespoint\'!$A$' . $start_salespoint_row . ':$A$' . $end_salespoint_row);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setErrorTitle('Pilihan Salespoint Tidak Terdapat pada list');
        $validation->setError('harap memilih salespoint berdasarkan pilihan salespoint yang terdaftar');

        // SET DIVSI SELECTION
        $salespointSheet = $spreadsheet->getSheetByName('Pilihan Divisi');
        $divisions = config('customvariable.division');
        $count_row = 3;
        foreach ($divisions as $division) {
            $salespointSheet->setCellValue('A' . $count_row, $division);
            $count_row++;
        }
        $start_divisi_row = 3;
        $end_divisi_row = $count_row;

        $validation = $monthlySheet->getCell('B3')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('=\'Pilihan Divisi\'!$A$' . $start_divisi_row . ':$A$' . $end_divisi_row);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setErrorTitle('Pilihan Divisi Tidak Terdapat pada list');
        $validation->setError('harap memilih divisi berdasarkan pilihan divisi yang terdaftar');

        $old_budget = BudgetUpload::find(request()->budget_upload_id);
        $this->modifyMonthlySheet($monthlySheet, $old_budget);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="HO Budget Template.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function modifyMonthlySheet($sheet, $old_budget)
    {
        $budgets = HOBudget::join('ho_budget_category', 'ho_budget_category.id', '=', 'ho_budget.ho_budget_category_id')
            ->select('ho_budget.*')
            ->orderBy('ho_budget_category.name', 'asc')
            ->orderBy('ho_budget.code', 'asc')
            ->get();

        if ($old_budget) {
            $sheet->setCellValue('B2', $old_budget->salespoint->name . " || " . $old_budget->salespoint->code);
            $sheet->setCellValue("B3", $old_budget->division);
            $sheet->setCellValue("B4", $old_budget->year);
        }
        $start_row = 8;
        $count_row = 0;
        // ALL DATA
        $kode_column      = "A";
        $kategori_column  = "B";
        $nama_column      = "C";
        $total_row = 0;
        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue($kode_column . $total_row, $budget->code);
            $sheet->setCellValue($kategori_column . $total_row, $budget->category_name);
            $sheet->setCellValue($nama_column . $total_row, $budget->name);

            try {
                $old_item = $old_budget->budget_detail->where('code', $budget->code)->first() ?? null;
                if ($old_item != null) {
                    for ($i = 1; $i <= 12; $i++) {
                        $result = collect(json_decode($old_item->values))->where('months', $i)->first();
                        if ($result) {
                            $sheet->setCellValueByColumnAndRow(3 + ($i * 2) - 1, $total_row, $result->qty);
                            $sheet->setCellValueByColumnAndRow(3 + ($i * 2), $total_row, $result->value);
                        }
                    }
                }
            } catch (\Exception $ex) {
            }
            $count_row++;
        }
        $sheet->getStyle("A8:AA" . $total_row)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1");
    }

    public function modifyQuarterlySheet($sheet)
    {
        $budgets = HOBudget::where('frequency', 'quarterly')->get();;
        $start_row = 5;
        $count_row = 0;
        // ALL DATA
        $kode_column      = "A";
        $kategori_column  = "B";
        $nama_column      = "C";

        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue($kode_column . $total_row, $budget->code);
            $sheet->setCellValue($kategori_column . $total_row, $budget->category_name);
            $sheet->setCellValue($nama_column . $total_row, $budget->name);
            $count_row++;
        }

        $total_row = $start_row + $count_row - 1;
        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle('D5:K' . $total_row)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    }

    public function modifyYearlySheet($sheet)
    {
        $budgets = HOBudget::where('frequency', 'yearly')->get();
        $start_row = 5;
        $count_row = 0;
        // ALL DATA
        $kode_column      = "A";
        $kategori_column  = "B";
        $nama_column      = "C";

        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue($kode_column . $total_row, $budget->code);
            $sheet->setCellValue($kategori_column . $total_row, $budget->category_name);
            $sheet->setCellValue($nama_column . $total_row, $budget->name);
            $count_row++;
        }

        $total_row = $start_row + $count_row - 1;
        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle('D5:E' . $total_row)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    }

    public function modifyIfAnySheet($sheet)
    {
        $budgets = HOBudget::where('frequency', 'if any')->get();
        $start_row = 5;
        $count_row = 0;
        // ALL DATA
        $kode_column      = "A";
        $kategori_column  = "B";
        $nama_column      = "C";

        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue($kode_column . $total_row, $budget->code);
            $sheet->setCellValue($kategori_column . $total_row, $budget->category_name);
            $sheet->setCellValue($nama_column . $total_row, $budget->name);
            $count_row++;
        }

        $total_row = $start_row + $count_row - 1;
        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle('D5:AA' . $total_row)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    }

    public function readTemplate(Request $request)
    {
        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($request->file('file')->getPathName());
            $monthlySheet = $spreadsheet->getSheetByName('Budget Input');
            $info = null;
            try {
                $salespoint_name = NULL;
                $selected_salespoint = $monthlySheet->getCell("B2")->getValue() ?? null;
                $salespoint_code = explode("||", $selected_salespoint)[1] ?? null;
                $salespoint = SalesPoint::where('code', trim($salespoint_code))->where('status', 5)->first();
                if (!$salespoint) {
                    throw new \Exception('Data salespoint "' . $selected_salespoint . '" tidak ditemukan');
                }
                $selected_division = $monthlySheet->getCell("B3")->getValue() ?? null;
                $division_list = config('customvariable.division');
                if (!in_array($selected_division, $division_list)) {
                    throw new \Exception('Divisi "' . $selected_division . '" tidak ditemukan');
                }

                $selected_year = $monthlySheet->getCell("B4")->getValue() ?? null;
                if (!$selected_year) {
                    throw new \Exception('Tahun belum diisi');
                }
                $info = [
                    "salespoint_code" => $salespoint->code,
                    "salespoint_name" => $salespoint->name,
                    "division" => $selected_division,
                    "year" => $selected_year
                ];
            } catch (\Exception $ex) {
                $info = null;
                throw new \Exception($ex->getMessage());
            }
            $monthlyData = $monthlySheet->toArray();
            unset($monthlyData[0], $monthlyData[1], $monthlyData[2], $monthlyData[3], $monthlyData[4], $monthlyData[5]);

            $monthlylist = [];
            $monthlyerror = [];
            foreach ($monthlyData as $t) {
                if (!$t[0]) {
                    continue;
                }
                $flag = true;
                // validasi kalo memang dia ga isi budgetnya sama sekali
                for ($i = 1; $i <= 12; $i++) {
                    $value_column = 2 + ($i * 2);
                    $qty_column = $value_column - 1;
                    if ($t[$value_column] != null || $t[$qty_column] != null) {
                        $flag = false;
                    }
                }
                if ($flag) {
                    // budget ga sama sekali diisi
                    continue;
                }
                $ho_budget = HOBudget::where('code', trim($t[0]))->first();
                // get all months qty and value
                $values = [];
                try {
                    for ($i = 1; $i <= 12; $i++) {
                        $value_column = 2 + ($i * 2);
                        $qty_column = $value_column - 1;
                        if ($t[$qty_column] == "") {
                            $t[$qty_column] = 0;
                        }
                        if ($t[$value_column] == "") {
                            $t[$value_column] = 0;
                        }
                        if (!is_numeric($t[$qty_column]) || !is_numeric($t[$value_column])) {
                            throw new \Exception("");
                        }
                        array_push($values, [
                            'months' => $i,
                            'qty' => intval($t[$qty_column]),
                            'value' => intval($t[$value_column]),
                        ]);
                    }
                } catch (\Exception $ex) {
                    // format salah
                    $error = [
                        "name" => $t[0] . " " . $t[1] . " " . $t[2],
                        "error" => "Format Qty dan Value harus berupa angka dan tidak boleh kosong"
                    ];
                    array_push($monthlyerror, $error);
                    continue;
                }
                if (!$ho_budget) {
                    continue;
                }
                $data = new \stdClass();
                $data->code = $ho_budget->code;
                $data->ho_budget_id = $ho_budget->id;
                $data->category = $ho_budget->category_name;
                $data->name = $ho_budget->name;
                $data->values = $values;
                array_push($monthlylist, $data);
            }

            return response()->json([
                'error' => false,
                'info' => $info,
                'data' => [
                    "monthly" => $monthlylist,
                ],
                'errordata' => [
                    "monthly" => $monthlyerror,
                ],
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => "Terjadi kesalahan dalam pembacaan file excel.(" . $ex->getMessage() . $ex->getLine() . ")",
            ]);
        }
    }

    public function monitoringBudget()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'ho')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'ho')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)
            ->where('status', 5)
            ->get();
        return view('Budget.HO.homonitoring', compact('activeBudget', 'pendingBudget', 'noBudgetSalespoint'));
    }

    public function monitoringBudgetExport()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'ho')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'ho')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)
            ->where('status', 5)
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_ho_upload_monitoring.xlsx");
        $budgetMonitoringSheet = $spreadsheet->getSheetByName('Budget Monitoring');

        $count_row = 3;
        foreach ($activeBudget as $budget) {
            $budgetMonitoringSheet->setCellValue('A' . $count_row, $budget->salespoint->name);
            $budgetMonitoringSheet->setCellValue('B' . $count_row, $budget->salespoint->region_name());
            $budgetMonitoringSheet->setCellValue('C' . $count_row, $budget->code);
            $budgetMonitoringSheet->setCellValue('D' . $count_row, $budget->created_at->format("d-m-Y"));
            $budgetMonitoringSheet->setCellValue('E' . $count_row, $budget->created_by_employee->name);
            $budgetMonitoringSheet->setCellValue('F' . $count_row, "Aktif");
            $count_row++;
        }

        foreach ($pendingBudget as $budget) {
            $budgetMonitoringSheet->setCellValue('A' . $count_row, $budget->salespoint->name);
            $budgetMonitoringSheet->setCellValue('B' . $count_row, $budget->salespoint->region_name());
            $budgetMonitoringSheet->setCellValue('C' . $count_row, $budget->code);
            $budgetMonitoringSheet->setCellValue('D' . $count_row, $budget->created_at->format("d-m-Y"));
            $budgetMonitoringSheet->setCellValue('E' . $count_row, $budget->created_by_employee->name);
            $budgetMonitoringSheet->setCellValue('F' . $count_row, "Pending || " . $budget->status());
            $count_row++;
        }

        foreach ($noBudgetSalespoint as $salespoint) {
            $budgetMonitoringSheet->setCellValue('A' . $count_row, $salespoint->name);
            $budgetMonitoringSheet->setCellValue('B' . $count_row, $salespoint->region_name());
            $budgetMonitoringSheet->setCellValue('F' . $count_row, "No Budget");
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Monitoring HO Budget ' . now()->translatedFormat("d F Y") . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}
