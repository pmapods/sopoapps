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
use App\Models\ArmadaBudget;
use App\Models\BudgetUpload;
use App\Models\BudgetUploadAuthorization;
use App\Models\ArmadaType;
use App\Models\Vendor;

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

class ArmadaBudgetUploadController extends Controller
{
    public function armadaBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        if (request()->input('status') == -1) {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'armada')
                ->where('status', 2)
                ->withTrashed()
                ->get();
        } else {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'armada')
                ->where('status', '!=', 2)
                ->get();
        }
        return view('Budget.Armada.armadabudget', compact('budgets'));
    }

    public function armadaBudgetDetailView($budget_upload_code)
    {
        $budget = BudgetUpload::where('type', 'armada')->where('code', $budget_upload_code)->first();

        $salespoint = SalesPoint::find($budget->salespoint_id);
        $newauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 10)->get();
        $reviseauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 11)->get();
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if ($budget == null) {
            return redirect('/armadabudget')->with('error', 'Kode budget tidak tersedia.');
        } else {
            if (!$user_location_access->contains($budget->salespoint_id)) {
                return redirect('/armadabudget')->with('error', 'Anda tidak memiliki akses untuk budget berikut. Tidak memiliki akses salespoint "' . $budget->salespoint->name . '"');
            }
            return view('Budget.Armada.armadabudgetdetail', compact('budget', 'newauthorization', 'reviseauthorization'));
        }
    }

    public function addArmadaBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        return view('Budget.Armada.addarmadabudget', compact('available_salespoints'));
    }

    public function createBudgetRequest(Request $request)
    {
        try {
            DB::beginTransaction();

            // check apakah ada request budget armada yang masih pending request
            $is_pending_request = BudgetUpload::where('status', 0)
                ->where('type', 'armada')
                ->where('salespoint_id', $request->salespoint_id)
                ->where('year', '=', $request->year)
                ->first();
            if ($is_pending_request) {
                $salespoint = SalesPoint::find($request->salespoint_id);
                return back()->with('error', 'Harap menyelesaikan request budget armada pada salespoint ' . $salespoint->name . ' sebelumnya terlebih dahulu. dengan kode request ' . $is_pending_request->code);
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
                $code = "BUDGET-ARM-" . $salespoint->initial . "-" . now()->translatedFormat('ymd') . '-' . str_repeat("0", 3 - strlen($budget_request_count + 1)) . ($budget_request_count + 1);
                $checkbudget = BudgetUpload::where('code', $code)->first();
                if ($checkbudget) {
                    $flag = false;
                    $budget_request_count++;
                } else {
                    $flag = true;
                }
            } while (!$flag);

            // set old budget status to non active
            $oldbudgets = BudgetUpload::where('type', 'armada')
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
            $newBudget->type                 = 'armada';
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
                $newArmadaBudget                   = new ArmadaBudget;
                $newArmadaBudget->budget_upload_id = $newBudget->id;
                $newArmadaBudget->armada_type_id   = $item->armada_type_id;
                $newArmadaBudget->armada_type_name = trim($item->armada_type_name);
                $newArmadaBudget->vendor_code      = trim($item->vendor_code);
                $newArmadaBudget->vendor_name      = trim($item->vendor_name);
                $newArmadaBudget->qty              = $item->qty;
                $newArmadaBudget->value            = $item->value;
                $newArmadaBudget->save();
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
                'budget_type' => 'Armada',
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
            return redirect('/armadabudget/' . $code)->with('success', 'Berhasil membuat request upload budget, otorisasi saat ini oleh ' . $authorization->employee_name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/armadabudget')->with('error', 'Gagal membuat request upload budget (' . $ex->getMessage() . ')');
        }
    }

    public function nonActiveBudget($budget_upload_code)
    {
        try {
            DB::beginTransaction();

            $oldbudgets = BudgetUpload::where('type', 'armada')
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
                $newArmadaBudget                   = new ArmadaBudget;
                $newArmadaBudget->budget_upload_id = $budget->id;
                $newArmadaBudget->armada_type_id   = $item->armada_type_id;
                $newArmadaBudget->armada_type_name = trim($item->armada_type_name);
                $newArmadaBudget->vendor_code      = trim($item->vendor_code);
                $newArmadaBudget->vendor_name      = trim($item->vendor_name);
                $newArmadaBudget->qty              = $item->qty;
                $newArmadaBudget->value            = $item->value;
                $newArmadaBudget->save();
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
                'budget_type' => 'Armada',
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
            return redirect('/armadabudget/' . $budget->code)->with('success', 'Berhasil membuat revisi upload budget, otorisasi saat ini oleh ' . $current_authorization->employee_name ?? "" . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/armadabudget/' . $budget->code)->with('error', 'Gagal membuat revisi upload budget. Pesan Error : ' . $ex->getMessage());
        }
    }

    public function terminateBudget(Request $request)
    {
        try {
            DB::beginTransaction();

            $budget                 = BudgetUpload::findOrFail($request->budget_upload_id);
            $budget->status         = -1;
            $budget->reject_notes   = $request->reason;
            $budget->rejected_by    = Auth::user()->id;
            $budget->save();
            $budget->delete();

            foreach ($budget->budget_detail as $b) {
                $b->delete();
            }

            DB::commit();
            return redirect('/armadabudget')->with('success', 'Berhasil membatalkan pengadaan upload budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/armadabudget')->with('error', 'Gagal membatalkan pengadaan upload budget. Pesan Error : ' . $ex->getMessage());
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
                $current_authorization->status += 1;
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
                    'budget_type' => 'Armada',
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
                    'budget_type' => 'Armada',
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

            // Mail budget upload
            $emailflag = true;
            $emailmessage = "";
            $mail_to = $current_authorization->employee->email;
            $name_to = $current_authorization->employee->name;
            $ccs = $budget->authorization_emails();
            $data = array(
                'original_emails' => [$mail_to],
                'original_ccs' => $ccs,
                'budget_type' => 'Armada',
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
        $armada_types = ArmadaType::all();
        $armada_vendors = Vendor::where('type', 'armada')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle("Daftar Budget");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $armada_type_category   = "A";
        $armada_vendor_category = "B";
        $qty_category           = "C";
        $value_category         = "D";

        $sheet->setCellValue($armada_type_category . '1', 'TIPE ARMADA');
        $sheet->setCellValue($armada_vendor_category . '1', 'VENDOR');
        $sheet->setCellValue($qty_category . '1', 'QTY');
        $sheet->setCellValue($value_category . '1', 'VALUE');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $type_array = $armada_types->pluck('name')->toArray();
        $type_array_text = implode(', ', $type_array);
        // TIPE ARMADA COLUMN SETTING
        for ($i = 2; $i < 100; $i++) {
            $validation = $sheet->getCell('A' . $i)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setFormula1('"' . $type_array_text . '"');
            // $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setErrorTitle('Pilihan Tipe Armada Tidak Terdapat pada list');
            $validation->setError('Untuk daftar pilihan armada dapat dilihat pada tab Master Tipe Armada');
        }

        $vendor_array = $armada_vendors->pluck('alias')->toArray();
        $vendor_array_text = implode(', ', $vendor_array);
        // TIPE ARMADA COLUMN SETTING
        for ($i = 2; $i < 100; $i++) {
            $validation = $sheet->getCell('B' . $i)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setFormula1('"' . $vendor_array_text . '"');
            // $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setErrorTitle('Pilihan Tipe Vendor Tidak Terdapat pada list');
            $validation->setError('Untuk daftar pilihan Vendor dapat dilihat pada tab Master Tipe Vendor');
        }

        // cek kalau ada old budget maka isi data
        $old_budget = BudgetUpload::find(request()->budget_upload_id);
        if ($old_budget != null) {
            $row = 2;
            foreach ($old_budget->budget_detail as $detail) {
                $sheet->setCellValue($armada_type_category . $row, trim($detail->armada_type_name));
                $sheet->setCellValue($armada_vendor_category . $row, trim($detail->vendor->alias));
                $sheet->setCellValue($qty_category . $row, $detail->qty);
                $sheet->setCellValue($value_category . $row, $detail->value);
                $row++;
            }
        }

        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle('A2:D100')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        // Master Armada
        $armadaSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Master Tipe Armada');
        $spreadsheet->addSheet($armadaSheet, 1);

        $armadaSheet->getColumnDimension('A')->setWidth(20);
        $armadaSheet->getColumnDimension('B')->setWidth(20);
        $armadaSheet->getColumnDimension('C')->setWidth(20);
        $armadaSheet->getColumnDimension('D')->setWidth(20);

        $armada_name_category        = "A";
        $armada_brand_name_category  = "B";
        $armada_alias_category       = "C";
        $armada_isNiaga_category     = "D";

        $start_row = 1;
        $count_row = 0;
        $total_row = $start_row + $count_row;
        $armadaSheet->setCellValue($armada_name_category . $total_row, 'NAMA');
        $armadaSheet->setCellValue($armada_brand_name_category . $total_row, 'BRAND');
        $armadaSheet->setCellValue($armada_alias_category . $total_row, 'ALIAS');
        $armadaSheet->setCellValue($armada_isNiaga_category . $total_row, 'TIPE NIAGA');
        $armadaSheet->getStyle('A1:D1')->getFont()->setBold(true);

        $count_row++;
        foreach ($armada_types as $armada_type) {
            $total_row = $start_row + $count_row;
            $armadaSheet->setCellValue($armada_name_category . $total_row, $armada_type->name);
            $armadaSheet->setCellValue($armada_brand_name_category . $total_row, $armada_type->brand_name);
            $armadaSheet->setCellValue($armada_alias_category . $total_row, $armada_type->alias);
            $armadaSheet->setCellValue($armada_isNiaga_category . $total_row, ($armada_type->isNiaga) ? "Niaga" : "Non Niaga");
            $count_row++;
        }
        $armadaSheet->getProtection()->setSheet(true);

        // Master Vendor Armada
        $vendorSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Master Vendor Armada');
        $spreadsheet->addSheet($vendorSheet, 2);

        $vendorSheet->getColumnDimension('A')->setWidth(40);
        $vendorSheet->getColumnDimension('B')->setWidth(20);;

        $vendor_name_category   = "A";
        $vendor_alias_category  = "B";

        $start_row = 1;
        $count_row = 0;
        $total_row = $start_row + $count_row;
        $vendorSheet->setCellValue($vendor_name_category . $total_row, 'NAMA');
        $vendorSheet->setCellValue($vendor_alias_category . $total_row, 'ALIAS');
        $vendorSheet->getStyle('A1:B1')->getFont()->setBold(true);

        $count_row++;
        foreach ($armada_vendors as $armada_vendor) {
            $total_row = $start_row + $count_row;
            $vendorSheet->setCellValue($vendor_name_category . $total_row, $armada_vendor->name);
            $vendorSheet->setCellValue($vendor_alias_category . $total_row, $armada_vendor->alias);
            $count_row++;
        }
        $vendorSheet->getProtection()->setSheet(true);

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Armada Budget Template.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function getBudgetTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_armada_template.xlsx");

        // Master Salespoint
        $salespointSheet = $spreadsheet->getSheetByName('Pilihan Salespoint');
        $salespoints = SalesPoint::all()->sortBy('name');
        $count_row = 3;
        foreach ($salespoints as $salespoint) {
            $salespointSheet->setCellValue('A' . $count_row, $salespoint->name . " || " . $salespoint->code);
            $count_row++;
        }
        $start_salespoint_row = 3;
        $end_salespoint_row = $count_row;

        // Master Armada
        $armada_types = ArmadaType::all();
        $armadaSheet = $spreadsheet->getSheetByName('Pilihan Tipe Armada');
        $count_row = 3;
        foreach ($armada_types as $armada_type) {
            $armadaSheet->setCellValue("A" . $count_row, $armada_type->name);
            $armadaSheet->setCellValue("B" . $count_row, $armada_type->brand_name);
            $armadaSheet->setCellValue("C" . $count_row, $armada_type->alias);
            $armadaSheet->setCellValue("D" . $count_row, ($armada_type->isNiaga) ? "Niaga" : "Non Niaga");
            $count_row++;
        }
        $start_armadatype_row = 3;
        $end_armadatype_row = $count_row;

        // Master Vendor Armada
        $armada_vendors = Vendor::where('type', 'armada')->get();
        $vendorSheet = $spreadsheet->getSheetByName('Pilihan Vendor Armada');
        $count_row = 3;
        foreach ($armada_vendors as $armada_vendor) {
            $vendorSheet->setCellValue("A" . $count_row, $armada_vendor->name);
            $vendorSheet->setCellValue("B" . $count_row, $armada_vendor->alias);
            $count_row++;
        }
        $start_armadavendor_row = 3;
        $end_armadavendor_row = $count_row;

        $budgetinputSheet = $spreadsheet->getSheetByName('Budget Input');
        $validation = $budgetinputSheet->getCell('B2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('=\'Pilihan Salespoint\'!$A$' . $start_salespoint_row . ':$A$' . $end_salespoint_row);
        // $validation->setAllowBlank(false);
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

        for ($i = 5; $i < 105; $i++) {
            $validation = $budgetinputSheet->getCell('A' . $i)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setFormula1('=\'Pilihan Tipe Armada\'!$A$' . $start_armadatype_row . ':$A$' . $end_armadatype_row);
            // $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setErrorTitle('Pilihan Tipe Armada Tidak Terdapat pada list');
            $validation->setError('harap memilih Tipe Armada berdasarkan pilihan salespoint yang terdaftar');
        }

        for ($i = 5; $i < 105; $i++) {
            $validation = $budgetinputSheet->getCell('B' . $i)->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setFormula1('=\'Pilihan Vendor Armada\'!$B$' . $start_armadavendor_row . ':$B$' . $end_armadavendor_row);
            // $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setErrorTitle('Pilihan Vendor Armada Tidak Terdapat pada list');
            $validation->setError('harap memilih Vendor Armada berdasarkan pilihan salespoint yang terdaftar');
        }

        // cek kalau ada old budget maka isi data
        $old_budget = BudgetUpload::find(request()->budget_upload_id);
        if ($old_budget != null) {
            $row = 5;
            foreach ($old_budget->budget_detail as $detail) {
                $budgetinputSheet->setCellValue("A" . $row, trim($detail->armada_type_name));
                $budgetinputSheet->setCellValue("B" . $row, trim($detail->vendor->alias));
                $budgetinputSheet->setCellValue("C" . $row, $detail->qty);
                $budgetinputSheet->setCellValue("D" . $row, $detail->value);
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Armada Budget Template.xlsx"');
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
                    $armada_type = ArmadaType::where('name', $t[0])->first();
                    $vendor = Vendor::where('alias', $t[1])->first();
                    $qty = intval($t[2]);
                    $value = intval($t[3]);

                    if ($armada_type == null || $vendor == null || $qty < 1 || $value < 1) {
                        throw new \Throwable;
                        continue;
                    }
                    $data = new \stdClass();
                    $data->armada_type_id   = $armada_type->id;
                    $data->armada_type_name = trim($armada_type->name);
                    $data->vendor_code      = trim($vendor->code);
                    $data->vendor_name      = trim($vendor->alias);
                    $data->qty              = $qty;
                    $data->value            = $value;
                    array_push($list, $data);
                } catch (\Throwable $th) {
                    $error = [
                        "armadatype" => $t[0] ?? "",
                        "vendor" => $t[1] ?? "",
                        "qty" => $t[2] ??  "",
                        "value" => $t[3] ??  ""
                    ];
                    // pastiin error bukan karna qty dan valuenya null / memang tidak diisi
                    if ($error['armadatype'] != null || $error['vendor'] != null || $error['qty'] != null || $error['value'] != null) {
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
                'line' => $ex->getLine(),
                'exception' => $ex
            ]);
        }
    }

    public function monitoringBudget()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'armada')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'armada')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)
            ->get();
        return view('Budget.Armada.armadamonitoring', compact('activeBudget', 'pendingBudget', 'noBudgetSalespoint'));
    }

    public function monitoringBudgetExport()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'armada')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'armada')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_armada_upload_monitoring.xlsx");
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Monitoring Armada Budget ' . now()->translatedFormat("d F Y") . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}
