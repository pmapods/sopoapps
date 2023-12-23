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
use App\Models\InventoryBudget;
use App\Models\BudgetUpload;
use App\Models\BudgetUploadAuthorization;
use App\Models\BudgetPricing;
use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;

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

class BudgetUploadController extends Controller
{
    public function inventoryBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        if (request()->input('status') == -1) {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'inventory')
                ->where('status', 2)
                ->withTrashed()
                ->get();
        } else {
            $budgets = BudgetUpload::whereIn('salespoint_id', $user_location_access)
                ->where('type', 'inventory')
                ->where('status', '!=', 2)
                ->get();
        }
        return view('Budget.Inventory.inventorybudget', compact('budgets'));
    }

    public function inventoryBudgetDetailView($budget_upload_code)
    {
        $budget = BudgetUpload::where('type', 'inventory')
            ->where('code', $budget_upload_code)->first();

        $salespoint = SalesPoint::find($budget->salespoint_id);
        $newauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 10)->get();
        $reviseauthorization = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 11)->get();
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if ($budget == null) {
            return redirect('/inventorybudget')->with('error', 'Kode budget tidak tersedia.');
        } else {
            if (!$user_location_access->contains($budget->salespoint_id)) {
                return redirect('/inventorybudget')->with('error', 'Anda tidak memiliki akses untuk budget berikut. Tidak memiliki akses salespoint "' . $budget->salespoint->name . '"');
            }
            $nextbudget = BudgetUpload::where('type', 'inventory')->where('id', '>', $budget->id)->get();
            return view('Budget.Inventory.inventorybudgetdetail', compact('budget', 'newauthorization', 'reviseauthorization'));
        }
    }

    public function addInventoryBudgetView()
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        return view('Budget.Inventory.addinventorybudget', compact('available_salespoints'));
    }

    public function nonActiveBudget($budget_upload_code)
    {
        try {
            DB::beginTransaction();

            $oldbudgets = BudgetUpload::where('type', 'inventory')
                ->where('code', $budget_upload_code)->first();

            $oldbudgets->status         = 2;
            $oldbudgets->reject_notes   = 'Budget Di Non Aktifkan';
            $oldbudgets->rejected_by    = Auth::user()->id;
            $oldbudgets->save();

            DB::commit();
            return redirect('/inventorybudget/')->with('success', 'Berhasil Non Aktifkan Budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/inventorybudget/')->with('error', 'Gagal Non Aktifkan Budget (' . $ex->getMessage() . ')');
        }
    }

    public function createBudgetRequest(Request $request)
    {
        try {
            DB::beginTransaction();

            $is_pending_request = BudgetUpload::where('status', 0)
                ->where('type', 'inventory')
                ->where('salespoint_id', $request->salespoint_id)
                ->where('year', '=', $request->year)
                ->first();
            if ($is_pending_request) {
                $salespoint = SalesPoint::find($request->salespoint_id);
                return back()->with('error', 'Harap menyelesaikan request budget inventory pada salespoint ' . $salespoint->name . ' sebelumnya terlebih dahulu. dengan kode request ' . $is_pending_request->code);
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
                $code = "BUDGET-INV-" . $salespoint->initial . "-" . now()->translatedFormat('ymd') . '-' . str_repeat("0", 3 - strlen($budget_request_count + 1)) . ($budget_request_count + 1);
                $checkbudget = BudgetUpload::where('code', $code)->first();
                if ($checkbudget) {
                    $flag = false;
                    $budget_request_count++;
                } else {
                    $flag = true;
                }
            } while (!$flag);

            // set old budget  status on selected salespoint to non active
            $oldbudgets = BudgetUpload::where('type', 'inventory')
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
            $newBudget->type                 = 'inventory';
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
                $newInventoryBudget                    = new InventoryBudget;
                $newInventoryBudget->budget_upload_id  = $newBudget->id;
                $newInventoryBudget->code              = $item->kode;
                $newInventoryBudget->keterangan        = $item->keterangan;
                $newInventoryBudget->qty               = $item->qty;
                $newInventoryBudget->value             = $item->value;
                $newInventoryBudget->save();
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
                'budget_type' => 'Inventory',
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
            return redirect('/inventorybudget/' . $code)->with('success', 'Berhasil membuat request upload budget, otorisasi saat ini oleh ' . $authorization->employee_name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/inventorybudget')->with('error', 'Gagal membuat request upload budget (' . $ex->getMessage() . ')');
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
                $newInventoryBudget                    = new InventoryBudget;
                $newInventoryBudget->budget_upload_id  = $budget->id;
                $newInventoryBudget->code              = $item->kode;
                $newInventoryBudget->keterangan        = $item->keterangan;
                $newInventoryBudget->qty               = $item->qty;
                $newInventoryBudget->value             = $item->value;
                $newInventoryBudget->save();
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
                'budget_type' => 'Inventory',
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
            return redirect('/inventorybudget/' . $budget->code)->with('success', 'Berhasil membuat revisi upload budget, otorisasi saat ini oleh ' . $current_authorization->employee_name ?? "" . $emailmessage);
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
            return redirect('/inventorybudget?status=-1')->with('success', 'Berhasil membatalkan pengadaan upload budget');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/inventorybudget?status=-1')->with('error', 'Gagal membatalkan pengadaan upload budget');
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
                    'budget_type' => 'Inventory',
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
                    'budget_type' => 'Inventory',
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
                'budget_type' => 'Inventory',
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

    public function getBudgetAuthorizationbySalespoint($salespoint_id)
    {
        $salespoint = SalesPoint::find($salespoint_id);
        $budget_authorizations = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 10)->get();

        foreach ($budget_authorizations as $authorizations) {
            $authorizations->list = $authorizations->authorization_detail;
            foreach ($authorizations->list as $item) {
                $item->employee_name = $item->employee->name;
            }
        }
        return response()->json([
            'data' => $budget_authorizations,
        ]);
    }

    public function getSalespointBudget(Request $request)
    {
        $budget = BudgetUpload::where('type', $request->type)
            ->where('salespoint_id', $request->salespoint_id)
            ->whereIn('status', $request->status)
            ->where('year', $request->year)
            ->first();
        if ($request->type == "ho") {
            $budget = BudgetUpload::where('type', "ho")
                ->where('salespoint_id', $request->salespoint_id)
                ->where('division', $request->division)
                ->where('year', $request->year)
                ->whereIn('status', $request->status)
                ->first();
        }
        if ($budget != null) {
            $budget->status = $budget->status();
            $budget->period = $budget->created_at->translatedFormat('F Y');
        }
        $data = [
            "budget" => $budget,
            "lists" => $budget->budget_detail ?? null,
        ];
        return response()->json([
            "data" => $data
        ]);
    }

    public function getBudgetTemplate_old()
    {
        $budgets = BudgetPricing::all();
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        // full data
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(15);

        $start_row = 1;
        $count_row = 0;
        // ALL DATA
        $kode_category          = "A";
        $kategori_category      = "B";
        $keterangan_category    = "C";
        $uom_category           = "D";
        $qty_category           = "E";
        $value_category         = "F";

        // header
        $total_row = $start_row + $count_row;
        $sheet->setCellValue($kode_category . $total_row, 'KODE BUDGET');
        $sheet->setCellValue($kategori_category . $total_row, 'KATEROGI');
        $sheet->setCellValue($keterangan_category . $total_row, 'KETERANGAN');
        $sheet->setCellValue($uom_category . $total_row, 'UOM');
        $sheet->setCellValue($qty_category . $total_row, 'QTY');
        $sheet->setCellValue($value_category . $total_row, 'VALUE');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $count_row++;

        $old_budget = BudgetUpload::find(request()->budget_upload_id);

        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue($kode_category . $total_row, $budget->code);
            $sheet->setCellValue($kategori_category . $total_row, $budget->budget_pricing_category->name);
            $sheet->setCellValue($keterangan_category . $total_row, $budget->name);
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Inventory Budget Template.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
    public function getBudgetTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_inventory_template.xlsx");
        $budgets = BudgetPricing::all();

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
        $count_row = 5;
        foreach ($budgets as $budget) {
            $budgetinputSheet->setCellValue('A' . $count_row, $budget->code);
            $budgetinputSheet->setCellValue('B' . $count_row, $budget->budget_pricing_category->name);
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Inventory Budget Template.xlsx"');
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
            $i = 1;
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
                    if (BudgetPricing::where('code', trim($t[0]))->first() == null) {
                        throw new \Throwable;
                        continue;
                    }
                    if (intval($t[4]) < 1 || intval($t[5]) < 1) {
                        throw new \Throwable;
                        continue;
                    }
                    $data = new \stdClass();
                    $data->kode        = $t[0];
                    $data->kategori    = $t[1];
                    $data->keterangan  = $t[2];
                    $data->uom         = $t[3];
                    $data->qty         = intval($t[4]);
                    $data->value       = intval($t[5]);
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
                ],
                ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage(),
                'line' => $ex->getLine(),
            ]);
        }
    }

    public function itemTracking()
    {
        try {
            $budget = BudgetUpload::findOrFail(request()->budget_upload_id);
            if ($budget->type == 'inventory') {
                $keterangan = request()->data['keterangan'];
                $item_code = request()->data['code'];
                $tickets_code = [];
                $prs = [];
                $pos = [];
                $data = [];
                $budget_detail = $budget->budget_detail->where('code', $item_code)->first();

                if ($budget_detail) {
                    $tickets_code = $budget_detail->getTicketsCode();
                    $selected_tickets = Ticket::whereIn('code', $tickets_code)->get();
                    foreach ($selected_tickets as $selected_ticket) {
                        $pos = $selected_ticket->po()->pluck('no_po_sap')->unique()->toArray();
                        $prs = $selected_ticket->po()->pluck('no_pr_sap')->unique()->toArray();

                        // check item quantity on ticket
                        $qty = 0;
                        foreach ($selected_ticket->ticket_item->where('isCancelled', false)->whereNotNull('budget_pricing_id') as $ticket_item) {
                            $code = $ticket_item->budget_pricing->code;
                            if ($code == $item_code) {
                                $qty  += $ticket_item->count;
                            }
                        }
                        // check quantity on ticket po if exist replace po
                        if (count($pos) > 0) {
                            try {
                                $sum = 0;
                                foreach ($pos as $no_po_sap) {
                                    $selected_po = Po::where('no_po_sap', $no_po_sap)->first();
                                    foreach ($selected_po->po_detail as $key => $detail) {
                                        $po_item_name = str_replace(' ', '', strtolower($detail->item_name));
                                        $budget_item_name = str_replace(' ', '', strtolower($budget_detail->keterangan));
                                        if ($po_item_name == $budget_item_name) {
                                            $sum += $detail->qty;
                                        }
                                    }
                                }
                            } catch (\Throwable $th) {
                                dd($th);
                            }
                        }
                        $pack = [
                            'ticket_code' => $selected_ticket->code,
                            'prs' => $prs,
                            'pos' => $pos,
                            'qty' => $qty,
                        ];
                        array_push($data, $pack);
                    }
                }
            }
            if ($budget->type == 'armada') {
                $armada_type = request()->data['armada_type'];
                $vendor_code = request()->data['vendor_code'];
                $vendor_name = request()->data['vendor_name'];
                // dd($armada_type,$vendor_code,$vendor_name);
                $tickets_code = [];
                $prs = [];
                $pos = [];
                $data = [];
                $budget_detail = $budget->budget_detail
                    ->where('armada_type_name', $armada_type)
                    ->where('vendor_name', $vendor_name)
                    ->first();

                if ($budget_detail) {
                    $tickets_code = $budget_detail->getTicketsCode();
                    $selected_tickets = ArmadaTicket::whereIn('code', $tickets_code)->get();
                    foreach ($selected_tickets as $selected_ticket) {
                        $pos = $selected_ticket->po()->pluck('no_po_sap')->unique()->toArray();
                        $prs = $selected_ticket->po()->pluck('no_pr_sap')->unique()->toArray();

                        $pack = [
                            'ticket_code' => $selected_ticket->code,
                            'prs' => $prs,
                            'pos' => $pos,
                            'qty' => 1,
                        ];
                        array_push($data, $pack);
                    }
                }
            }
            if ($budget->type == 'assumption') {
                $budget_code = request()->data['code'];
                $budget_category = request()->data['category'];
                $budget_name = request()->data['name'];
                $tickets_code = [];
                $prs = [];
                $pos = [];
                $data = [];
                $budget_detail = $budget->budget_detail->where('code', $budget_code)->first();

                if ($budget_detail) {
                    $tickets_code = $budget_detail->getTicketsCode();
                    $selected_tickets = Ticket::whereIn('code', $tickets_code)->get();
                    foreach ($selected_tickets as $selected_ticket) {
                        $pos = $selected_ticket->po()->pluck('no_po_sap')->unique()->toArray();
                        $prs = $selected_ticket->po()->pluck('no_pr_sap')->unique()->toArray();

                        // check item quantity on ticket
                        $qty = 0;
                        foreach ($selected_ticket->ticket_item->where('isCancelled', false)->whereNotNull('maintenance_budget_id') as $ticket_item) {
                            $code = $ticket_item->maintenance_budget->code;
                            if ($code == $budget_code) {
                                $qty  += $ticket_item->count;
                            }
                        }
                        // check quantity on ticket po if exist replace po
                        if (count($pos) > 0) {
                            try {
                                $sum = 0;
                                foreach ($pos as $no_po_sap) {
                                    $selected_po = Po::where('no_po_sap', $no_po_sap)->first();
                                    foreach ($selected_po->po_detail as $key => $detail) {
                                        $po_item_name = str_replace(' ', '', strtolower($detail->item_name));
                                        $budget_item_name = str_replace(' ', '', strtolower($budget_detail->name));
                                        if ($po_item_name == $budget_item_name) {
                                            $sum += $detail->qty;
                                        }
                                    }
                                }
                            } catch (\Throwable $th) {
                                dd($th);
                            }
                        }
                        $pack = [
                            'ticket_code' => $selected_ticket->code,
                            'prs' => $prs,
                            'pos' => $pos,
                            'qty' => $qty,
                        ];
                        array_push($data, $pack);
                    }
                }

                // TODO belom validasi untuk pengadaan security dan cit
            }
            return response()->json([
                'error' => false,
                'data' => $data
            ]);
        } catch (\Exception $ex) {
            dd($ex);
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function monitoringBudget()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'inventory')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'inventory')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)
            ->get();
        return view('Budget.Inventory.inventorymonitoring', compact('activeBudget', 'pendingBudget', 'noBudgetSalespoint'));
    }

    public function monitoringBudgetExport()
    {
        $employee_location_access = Auth::user()->location_access_list();
        $activeBudget = BudgetUpload::where('status', 1)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'inventory')
            ->get();
        $pendingBudget = BudgetUpload::where('status', 0)
            ->whereIn('salespoint_id', $employee_location_access)
            ->where('type', 'inventory')
            ->get();

        $activeBudgetSalespointIds = $activeBudget->pluck('salespoint_id')->unique();
        $pendingBudgetSalespointIds = $pendingBudget->pluck('salespoint_id')->unique();
        $budgetSalespointIds = array_merge($activeBudgetSalespointIds->toArray(), $pendingBudgetSalespointIds->toArray());
        $noBudgetSalespoint = SalesPoint::whereNotIn('id', $budgetSalespointIds)
            ->whereIn('id', $employee_location_access)
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_inventory_upload_monitoring.xlsx");
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
        $response->headers->set('Content-Disposition', 'attachment;filename="Monitoring Inventory Budget ' . now()->translatedFormat("d F Y") . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}
