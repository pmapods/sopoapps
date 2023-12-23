<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;
use App\Mail\NotificationMail;
use Mail;

use App\Models\EmployeeLocationAccess;
use App\Models\SalesPoint;
use App\Models\Authorization;
use App\Models\AssumptionBudget;
use App\Models\BudgetUpload;
use App\Models\BudgetUploadAuthorization;
use App\Models\MaintenanceBudget;

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

class AssumptionBudgetUploadController extends Controller
{
    public function assumptionBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        if (request()->input('status') == -1) {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'assumption')
                ->where('status', 2)
                ->withTrashed()
                ->get();
        } else {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'assumption')
                ->where('status', '!=', 2)
                ->get();
        }
        return view('Budget.Assumption.assumptionbudget', compact('budgets'));
    }

    public function assumptionBudgetDetailView($budget_upload_code)
    {
        $budget = BudgetUpload::where('type', 'assumption')
            ->where('code', $budget_upload_code)->first();

        $salespoint = SalesPoint::find($budget->salespoint_id);
        $newauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 10)->get();
        $reviseauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 11)->get();
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if ($budget == null) {
            return redirect('/assumptionbudget')->with('error', 'Kode budget tidak tersedia.');
        } else {
            if (!$user_location_access->contains($budget->salespoint_id)) {
                return redirect('/assumptionbudget')->with('error', 'Anda tidak memiliki akses untuk budget berikut. Tidak memiliki akses salespoint "' . $budget->salespoint->name . '"');
            }
            return view('Budget.Assumption.assumptionbudgetdetail', compact('budget', 'newauthorization', 'reviseauthorization'));
        }
    }

    public function addAssumptionBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        return view('Budget.Assumption.addassumptionbudget', compact('available_salespoints'));
    }

    public function createBudgetRequest(Request $request)
    {
        try {
            DB::beginTransaction();

            $is_pending_request = BudgetUpload::where('status', 0)
                ->where('type', 'assumption')
                ->where('salespoint_id', $request->salespoint_id)
                ->where('year', '=', $request->year)
                ->first();
            if ($is_pending_request) {
                $salespoint = SalesPoint::find($request->salespoint_id);
                return back()->with('error', 'Harap menyelesaikan request budget assumption pada salespoint ' . $salespoint->name . ' sebelumnya terlebih dahulu. dengan kode request ' . $is_pending_request->code);
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
                $code = "BUDGET-ASM-" . $salespoint->initial . "-" . now()->translatedFormat('ymd') . '-' . str_repeat("0", 3 - strlen($budget_request_count + 1)) . ($budget_request_count + 1);
                $checkbudget = BudgetUpload::where('code', $code)->first();
                if ($checkbudget) {
                    $flag = false;
                    $budget_request_count++;
                } else {
                    $flag = true;
                }
            } while (!$flag);

            // set old budget  status on selected salespoint to non active
            $oldbudgets = BudgetUpload::where('type', 'assumption')
                ->where('salespoint_id', $request->salespoint_id)
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
            $newBudget->type                 = 'assumption';
            $newBudget->code                 = $code;
            $newBudget->status               = 0;
            $newBudget->year                 = $request->year;
            $newBudget->created_by           = Auth::user()->id;
            $newBudget->save();

            $json_data = $this->readTemplate($request)->getData();
            // dd($json_data);
            if ($json_data->error) {
                return back()->with('error', 'Terjadi kesalahan dalam membaca file. (Pesan error : ' . $json_data->message . ')');
            }
            if (count($json_data->data) == 0) {
                return back()->with('error', 'Minimal satu item dalam pengajuan budget');
            }
            if ($json_data->salespoint->code != $salespoint->code) {
                return back()->with('error', 'Salespoint yang dipilih tidak sesuai dengan salespoint yang di upload');
            }
            foreach ($json_data->data as $item) {
                $newAssumptionBudget                         = new AssumptionBudget;
                $newAssumptionBudget->budget_upload_id       = $newBudget->id;
                $newAssumptionBudget->maintenance_budget_id  = $item->maintenance_budget_id;
                $newAssumptionBudget->code                   = $item->code;
                $newAssumptionBudget->group                  = $item->group;
                $newAssumptionBudget->name                   = $item->name;
                $newAssumptionBudget->qty                    = $item->qty;
                $newAssumptionBudget->value                  = $item->value;
                $newAssumptionBudget->save();
            }

            $authorization = Authorization::findOrFail($request->authorization_id);
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
            // Mail budget upload
            $emailflag = true;
            $emailmessage = "";
            $mail_to = $newBudget->authorizations->first()->employee->email;
            $name_to = $newBudget->authorizations->first()->employee->name;
            $data = array(
                'original_emails' => [$mail_to],
                'budget_type' => 'Assumption',
                'salespoint_name' => $salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $newBudget->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'budget_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            // recall the new one
            $authorization = $newBudget->current_authorization();
            DB::commit();
            return redirect('/assumptionbudget/' . $code)->with('success', 'Berhasil membuat request upload budget, otorisasi saat ini oleh ' . $authorization->employee_name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/assumptionbudget')->with('error', 'Gagal membuat request upload budget (' . $ex->getMessage() . ')');
        }
    }

    public function nonActiveBudget($budget_upload_code)
    {
        try {
            DB::beginTransaction();

            $oldbudgets = BudgetUpload::where('type', 'assumption')
                ->where('code', $budget_upload_code)->first();

            $oldbudgets->status         = 2;
            $oldbudgets->reject_notes   = 'Budget Di Non Aktifkan';
            $oldbudgets->rejected_by    = Auth::user()->id;
            $oldbudgets->save();

            DB::commit();
            return redirect('/armadabudget/')->with('success', 'Berhasil Non Aktifkan Budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/armadabudget/')->with('error', 'Gagal Non Aktifkan Budget (' . $ex->getMessage() . ')');
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
            if (count($json_data->data) == 0) {
                return back()->with('error', 'Minimal satu item dalam pengajuan budget');
            }
            if ($json_data->salespoint->code != $budget->salespoint->code) {
                return back()->with('error', 'Salespoint yang dipilih tidak sesuai dengan salespoint yang di upload');
            }

            foreach ($json_data->data as $item) {
                $newAssumptionBudget                         = new AssumptionBudget;
                $newAssumptionBudget->budget_upload_id       = $budget->id;
                $newAssumptionBudget->maintenance_budget_id  = $item->maintenance_budget_id;
                $newAssumptionBudget->code                   = $item->code;
                $newAssumptionBudget->group                  = $item->group;
                $newAssumptionBudget->name                   = $item->name;
                $newAssumptionBudget->qty                    = $item->qty;
                $newAssumptionBudget->value                  = $item->value;
                $newAssumptionBudget->save();
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

            // Mail budget upload
            $emailflag = true;
            $emailmessage = "";
            $mail_to = $current_authorization->employee->email;
            $name_to = $current_authorization->employee->name;
            $data = array(
                'original_emails' => [$mail_to],
                'budget_type' => 'Assumption',
                'salespoint_name' => $budget->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $budget->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'budget_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();
            return redirect('/assumptionbudget/' . $budget->code)->with('success', 'Berhasil membuat revisi upload budget, otorisasi saat ini oleh ' . $current_authorization->employee_name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
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
            return redirect('/assumptionbudget?status=-1')->with('success', 'Berhasil membatalkan pengadaan upload budget');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
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

                // Mail budget upload
                $emailflag = true;
                $emailmessage = "";
                $mail_to = $budget->authorization_emails();
                $name_to = "Bapak/Ibu";
                $data = array(
                    'original_emails' => $mail_to,
                    'budget_type' => 'Assumption',
                    'salespoint_name' => $budget->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $budget->code,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'budget_approved'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }
                DB::commit();
                if ($return_data_type == "api") {
                    return response()->json([
                        "error" => false,
                        "message" => 'Otorisasi request budget ' . $budget->code . ' telah selesai. Status Budget sudah aktif' . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Otorisasi request budget ' . $budget->code . ' telah selesai. Status Budget sudah aktif' . $emailmessage);
                }
            } else {
                // Mail budget upload
                $emailflag = true;
                $emailmessage = "";
                $mail_to = $current_authorization->employee->email;
                $name_to = $current_authorization->employee->name;
                $data = array(
                    'original_emails' => [$mail_to],
                    'budget_type' => 'Assumption',
                    'salespoint_name' => $budget->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $budget->code,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'budget_approval'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }
                DB::commit();
                if ($return_data_type == "api") {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan approval otorisasi request budget ' . $budget->code . '. Otorisasi selanjutnya oleh ' . $current_authorization->employee_name . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan approval otorisasi request budget ' . $budget->code . '. Otorisasi selanjutnya oleh ' . $current_authorization->employee_name . $emailmessage);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan otorisasi ' . $ex->getMessage());
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

            // $current_authorization = $budget->current_authorization();
            // Mail budget upload
            $emailflag = true;
            $emailmessage = "";
            $mail_to = $current_authorization->employee->email;
            $name_to = $current_authorization->employee->name;
            $ccs = $budget->authorization_emails();
            $data = array(
                'original_emails' => [$mail_to],
                'original_ccs' => $ccs,
                'budget_type' => 'Assumption',
                'salespoint_name' => $budget->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $budget->code,
                'reason' => $request->reason
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'budget_reject'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            DB::commit();
            if ($return_data_type == "api") {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil menolak request budget ' . $budget->code . ' dengan alasan ' . $request->reason . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil menolak request budget ' . $budget->code . ' dengan alasan ' . $request->reason . $emailmessage);
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

    public function getBudgetTemplate_old()
    {
        $budgets = MaintenanceBudget::all();
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $start_row = 1;
        $count_row = 0;
        // ALL DATA
        $kode_category          = "A";
        $kategori_category      = "B";
        $nama_category          = "C";
        $uom_category           = "D";
        $qty_category           = "E";
        $value_category         = "F";

        // header
        $total_row = $start_row + $count_row;
        $sheet->setCellValue($kode_category . $total_row, 'KODE BUDGET');
        $sheet->setCellValue($kategori_category . $total_row, 'KATEGORI');
        $sheet->setCellValue($nama_category . $total_row, 'NAMA');
        $sheet->setCellValue($uom_category . $total_row, 'UOM');
        $sheet->setCellValue($qty_category . $total_row, 'QTY');
        $sheet->setCellValue($value_category . $total_row, 'VALUE');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $count_row++;

        $old_budget = BudgetUpload::find(request()->budget_upload_id);

        $additional_items = array(
            array('code' => 'SCRT', 'category_name' => 'SECURITY', 'name' => 'Security Personil', 'uom' => 'person'),
            array('code' => 'CIT', 'category_name' => 'CIT', 'name' => 'Pickup Service Expense', 'uom' => 'days')
        );
        foreach (collect($additional_items) as $additional_item) {
            $new = new \stdClass();
            $new->code = $additional_item['code'];
            $new->category_name = $additional_item['category_name'];
            $new->name = $additional_item['name'];
            $new->uom = $additional_item['uom'];
            $budgets->push($new);
        }

        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue($kode_category . $total_row, $budget->code);
            $sheet->setCellValue($kategori_category . $total_row, $budget->category_name);
            $sheet->setCellValue($nama_category . $total_row, $budget->name);
            $sheet->setCellValue($uom_category . $total_row, $budget->uom);

            // untuk revisi = cek kalo misal ada item terkait langsung tambahin datanya
            try {
                $old_item = $old_budget->budget_detail->where('code', $budget->code)->first() ?? null;
                if ($old_item != null) {
                    $sheet->setCellValue($qty_category . $total_row, $old_item->qty);
                    $sheet->setCellValue($value_category . $total_row, $old_item->value);
                }
            } catch (\Throwable $th) {
            }
            $count_row++;
        }

        for ($i = 'A'; $i != $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle('E2:F' . $total_row)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Assumption Budget Template.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function getBudgetTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_assumption_template.xlsx");
        $budgets = MaintenanceBudget::all();

        $salespointSheet = $spreadsheet->getSheetByName('Pilihan Salespoint');
        $salespoints = SalesPoint::all()->sortBy('name');
        $count_row = 3;
        foreach ($salespoints as $salespoint) {
            $salespointSheet->setCellValue('A' . $count_row, $salespoint->name . " || " . $salespoint->code);
            $count_row++;
        }
        $start_salespoint_row = 3;
        $end_salespoint_row = $count_row;

        $budgetinputSheet = $spreadsheet->getSheetByName('Budget Input');
        $validation = $budgetinputSheet->getCell('B2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('=\'Pilihan Salespoint\'!$A$' . $start_salespoint_row . ':$A$' . $end_salespoint_row);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setErrorTitle('Pilihan Salespoint Tidak Terdapat pada list');
        $validation->setError('harap memilih salespoint berdasarkan pilihan salespoint yang terdaftar');

        $old_budget = BudgetUpload::find(request()->budget_upload_id);
        // set salespoint existing if oldbudget exist
        if ($old_budget) {
            $budgetinputSheet->setCellValue('B2', $old_budget->salespoint->name . " || " . $old_budget->salespoint->code);
        }

        $additional_items = array(
            array('code' => 'SCRT', 'category_name' => 'SECURITY', 'name' => 'Security Personil', 'uom' => 'person'),
            array('code' => 'CIT', 'category_name' => 'CIT', 'name' => 'Pickup Service Expense', 'uom' => 'days')
        );
        foreach (collect($additional_items) as $additional_item) {
            $new = new \stdClass();
            $new->code = $additional_item['code'];
            $new->category_name = $additional_item['category_name'];
            $new->name = $additional_item['name'];
            $new->uom = $additional_item['uom'];
            $budgets->push($new);
        }
        $count_row = 5;
        foreach ($budgets as $budget) {
            $budgetinputSheet->setCellValue('A' . $count_row, $budget->code);
            $budgetinputSheet->setCellValue('B' . $count_row, $budget->category_name);
            $budgetinputSheet->setCellValue('C' . $count_row, $budget->name);
            $budgetinputSheet->setCellValue('D' . $count_row, $budget->uom);

            // untuk revisi = cek kalo misal ada item terkait langsung tambahin datanya
            try {
                $old_item = $old_budget->budget_detail->where('code', $budget->code)->first() ?? null;
                if ($old_item != null) {
                    $budgetinputSheet->setCellValue('E' . $count_row, $old_item->qty);
                    $budgetinputSheet->setCellValue('F' . $count_row, $old_item->value);
                }
            } catch (\Throwable $th) {
            }
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Assumption Budget Template.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function readTemplate(Request $request)
    {
        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($request->file('file')->getPathName());

            $d = $spreadsheet->getSheet(0)->toArray();
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            $selected_salespoint = $sheetData[1][1];

            $salespoint_name = NULL;
            $salespoint_code = NULL;
            try {
                $salespoint_code = explode("||", $selected_salespoint)[1];
                $salespoint = SalesPoint::where('code', trim($salespoint_code))->first();
                if ($salespoint) {
                    $salespoint_name = $salespoint->name;
                    $salespoint_code = $salespoint->code;
                } else {
                    throw new \Throwable;
                }
            } catch (\Throwable $th) {
                $salespoint_name = NULL;
                $salespoint_code = NULL;
                throw new \Exception('Data salespoint ' . $selected_salespoint . ' tidak ditemukan');
            }
            unset($sheetData[0], $sheetData[1], $sheetData[2], $sheetData[3]);

            $list = [];
            $errorlist = [];
            foreach ($sheetData as $t) {
                try {
                    $maintenance_budget = MaintenanceBudget::where('code', trim($t[0]))->first();
                    $qty = intval($t[4]);
                    $value = intval($t[5]);
                    if ($qty < 1 || $value < 1) {
                        throw new \Throwable;
                        continue;
                    }
                    if ($maintenance_budget == null) {
                        if ($t[0] != 'SCRT' && $t[0] != 'CIT') {
                            throw new \Throwable;
                            continue;
                        } else {
                            if ($t[0] == 'SCRT') {
                                $group = 'SECURITY';
                                $name = 'Security personil';
                                $uom = 'person';
                            }
                            if ($t[0] == 'CIT') {
                                $group = 'CIT';
                                $name = 'Pickup Service Expense';
                                $uom = 'months';
                            }
                        }
                    } else {
                        $group =  $maintenance_budget->category_name;
                        $name =  $maintenance_budget->name;
                        $uom = $maintenance_budget->uom;
                    }
                    $data = new \stdClass();
                    $data->code = $t[0];
                    $data->maintenance_budget_id = $maintenance_budget->id ?? null;
                    $data->group = $group;
                    $data->name = $name;
                    $data->uom = $uom;
                    $data->qty = $qty;
                    $data->value = $value;
                    array_push($list, $data);
                } catch (\Throwable $th) {
                    $error = [
                        "name" => $t[0] . " " . $t[1] . " " . $t[2],
                        "qty" => $t[4] ??  "",
                        "value" => $t[5] ??  ""
                    ];
                    // pastiin error bukan karna qty dan valuenya null / memang tidak diisi
                    if ($error['qty'] != null || $error['value'] != null) {
                        array_push($errorlist, $error);
                    }
                    continue;
                }
            }

            return response()->json([
                'error' => false,
                'data' => $list,
                'errordata' => $errorlist,
                'salespoint' => [
                    'name' => $salespoint_name,
                    'code' => $salespoint_code,
                ]
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function monitoringBudget()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'assumption')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'assumption')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)->get();
        return view('Budget.Assumption.assumptionmonitoring', compact('activeBudget', 'pendingBudget', 'noBudgetSalespoint'));
    }

    public function monitoringBudgetExport()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'assumption')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'assumption')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_assumption_upload_monitoring.xlsx");
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Monitoring Assumption Budget ' . now()->translatedFormat("d F Y") . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}
