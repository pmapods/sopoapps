<?php

namespace App\Http\Controllers\Reporting;

use DB;
use Auth;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;

use App\Models\HOBudget;
use App\Models\SalesPoint;
use App\Models\TicketItem;
use App\Models\ArmadaTicket;
use App\Models\BudgetUpload;
use Illuminate\Http\Request;
use App\Models\BudgetPricing;
use App\Models\SecurityTicket;
use App\Models\TicketAuthorization;
use App\Models\SecurityTicketAuthorization;
use App\Models\ArmadaTicketAuthorization;
use App\Models\TicketItemFileRequirement;

use App\Models\MaintenanceBudget;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadReportController extends Controller
{
    public function view()
    {
        $salespoints = SalesPoint::all();
        return view('Reporting.downloadreport', compact('salespoints'));
    }

    public function hobudget()
    {
        $salespoint = SalesPoint::find(request()->salespoint_id);

        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($salespoint->id)) {
            return redirect('/downloadreport')->with('error', 'Anda tidak memiliki akses untuk download report terkait area "' . $salespoint->name);
        }
        $division = request()->division;
        $year = request()->year;

        $budget = BudgetUpload::where('salespoint_id', $salespoint->id)
            ->where('status', 1)->where('type', 'ho')
            ->where('division', $division)
            ->where('year', $year)
            ->first();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/ho_budget_vs_actual.xlsx");

        $sheet = $spreadsheet->getSheetByName('BP vs Actual');
        $this->modifySheetHOBudget($sheet, $budget);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $salespoint->name . ' ' . $division . ' ' . $year . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function hoBudgetNonActive()
    {
        $salespoint = SalesPoint::find(request()->salespoint_id);

        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($salespoint->id)) {
            return redirect('/downloadreport')->with('error', 'Anda tidak memiliki akses untuk download report terkait area "' . $salespoint->name);
        }
        $division = request()->division;
        $year = request()->year;

        $budget = BudgetUpload::where('salespoint_id', $salespoint->id)
            ->where('status', 2)->where('type', 'ho')
            ->where('division', $division)
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/ho_budget_vs_actual.xlsx");

        $sheet = $spreadsheet->getSheetByName('BP vs Actual');
        $this->modifySheetHOBudget($sheet, $budget);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $salespoint->name . ' ' . $division . ' ' . $year . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function modifySheetHOBudget($sheet, $old_budget)
    {
        // $budgets = HOBudget::where('frequency','monthly')->get();
        $budgets = HOBudget::all();
        $salespoint = SalesPoint::findOrFail(request()->salespoint_id);
        $sheet->setCellValue("B1", $salespoint->name);
        $sheet->setCellValue("C1", $salespoint->code);
        $sheet->setCellValue("B2", request()->division);
        $sheet->setCellValue("B3", request()->year);
        $start_row = 10;
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

            $max_row_by_item = 1;
            try {
                $old_item = $old_budget->budget_detail->where('code', $budget->code)->first() ?? null;
                if ($old_item != null) {
                    for ($i = 1; $i <= 12; $i++) {
                        $result = collect(json_decode($old_item->values))->where('months', $i)->first();
                        $qtyByValue = $old_item->getQtyGroupByValue($i);
                        $actual_qty = $old_item->getActualQty($i);
                        if ($result) {
                            // mapping budget
                            $column_saldo_akhir_amount   = 3 + ($i * 9);
                            $column_saldo_akhir_value    = 3 + ($i * 9) - 1;
                            $column_saldo_akhir_qty      = 3 + ($i * 9) - 2;
                            $column_actual_amount        = 3 + ($i * 9) - 3;
                            $column_actual_value         = 3 + ($i * 9) - 4;
                            $column_actual_qty           = 3 + ($i * 9) - 5;
                            $column_budget_amount        = 3 + ($i * 9) - 6;
                            $column_budget_value         = 3 + ($i * 9) - 7;
                            $column_budget_qty           = 3 + ($i * 9) - 8;

                            $sheet->setCellValueByColumnAndRow($column_budget_qty, $total_row, $result->qty);
                            $sheet->setCellValueByColumnAndRow($column_budget_value, $total_row, $result->value);
                            $sheet->setCellValueByColumnAndRow($column_budget_amount, $total_row, $result->qty * $result->value);

                            $count_row_by_item = 0;
                            $total_item_qty = 0;
                            foreach ($qtyByValue as $items) {
                                $sum_qty = 0;
                                foreach ($items as $item) {
                                    $sum_qty += $item['qty'];
                                }
                                $sheet->setCellValueByColumnAndRow($column_actual_qty, $total_row + $count_row_by_item, $sum_qty);
                                $sheet->setCellValueByColumnAndRow($column_actual_value, $total_row + $count_row_by_item, $items[0]['value']);
                                $sheet->setCellValueByColumnAndRow($column_actual_amount, $total_row + $count_row_by_item, $sum_qty * $items[0]['value']);
                                $count_row_by_item++;
                                $total_item_qty += $sum_qty;
                            }

                            $sheet->setCellValueByColumnAndRow($column_saldo_akhir_qty, $total_row, $result->qty - $total_item_qty);
                            $sheet->setCellValueByColumnAndRow($column_saldo_akhir_value, $total_row, $result->value);
                            $sheet->setCellValueByColumnAndRow($column_saldo_akhir_amount, $total_row, ($result->qty - $total_item_qty) * $result->value);

                            if ($max_row_by_item < $count_row_by_item) {
                                $max_row_by_item = $count_row_by_item;
                            }
                            // $sheet->setCellValueByColumnAndRow($column_saldo_akhir_amount, $total_row,($result->qty-$actual_qty)*$result->value);
                        }
                    }
                }
            } catch (\Exception $ex) {
                // continue;
            }
            $count_row += $max_row_by_item;
        }
        for ($col = 'A'; $col !== 'DG'; $col++) {
            $sheet->getColumnDimension($col)
                ->setAutoSize(true);
        }
        $sheet->getProtection()->setSheet(true);
        $protection = $sheet->getProtection();
        $protection->setPassword('pods2022');
    }

    public function areaBudget()
    {
        $salespoint = SalesPoint::find(request()->salespoint_id);
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($salespoint->id)) {
            return redirect('/downloadreport')->with('error', 'Anda tidak memiliki akses untuk download report terkait area "' . $salespoint->name);
        }

        $budget_type = request()->budget_type;
        $year = request()->year;

        $budget = BudgetUpload::where('salespoint_id', $salespoint->id)
            ->where('status', 1)
            ->where('type', $budget_type)
            ->where('year', $year)
            ->first();

        if ($budget_type == "inventory") {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_area_inventory_report_template.xlsx");
        } else if ($budget_type == "armada") {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_area_armada_report_template.xlsx");
        } else {
            // assumption
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_area_assumption_report_template.xlsx");
        }
        $sheet = $spreadsheet->getSheetByName('BP vs Actual');

        if ($budget_type == "inventory") {
            $this->modifySheetInventoryBudget($sheet, $budget);
        } else if ($budget_type == "armada") {
            $this->modifySheetArmadaBudget($sheet, $budget);
        } else {
            // assumption
            $this->modifySheetAssumptionBudget($sheet, $budget);
        }

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $salespoint->name . ' ' . $budget_type . ' ' . now()->format("Ymd") . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function areaBudgetNonActive()
    {
        $salespoint = SalesPoint::find(request()->salespoint_id);
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($salespoint->id)) {
            return redirect('/downloadreport')->with('error', 'Anda tidak memiliki akses untuk download report terkait area "' . $salespoint->name);
        }

        $budget_type = request()->budget_type;
        $year = request()->year;

        $budget = BudgetUpload::where('salespoint_id', $salespoint->id)
            ->where('status', 2)
            ->where('type', $budget_type)
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($budget_type == "inventory") {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_area_inventory_report_template.xlsx");
        } else if ($budget_type == "armada") {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_area_armada_report_template.xlsx");
        } else {
            // assumption
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/budget_area_assumption_report_template.xlsx");
        }
        $sheet = $spreadsheet->getSheetByName('BP vs Actual');

        if ($budget_type == "inventory") {
            $this->modifySheetInventoryBudget($sheet, $budget);
        } else if ($budget_type == "armada") {
            $this->modifySheetArmadaBudget($sheet, $budget);
        } else {
            // assumption
            $this->modifySheetAssumptionBudget($sheet, $budget);
        }

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $salespoint->name . ' Tahun Sebelumnya ' . $budget_type . ' ' . now()->format("Ymd") . '.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function modifySheetInventoryBudget($sheet, $old_budget)
    {
        $budgets = BudgetPricing::all();
        $salespoint = SalesPoint::findOrFail(request()->salespoint_id);
        $sheet->setCellValue("B2", $old_budget->salespoint->name . " | " . $old_budget->salespoint->code);
        $sheet->setCellValue("B3", now()->format("d-m-Y"));
        $sheet->setCellValue("B4", $old_budget->code);
        $sheet->setCellValue("B5", $old_budget->year);
        $start_row = 8;
        $count_row = 0;

        $old_budgets_tickets = Ticket::where('budget_upload_id', $old_budget->id)->get();
        $items = [];
        $count = 0;
        foreach ($old_budgets_tickets as $ticket) {
            foreach ($ticket->ticket_item as $key => $item) {
                $formatted_item = new \stdClass();
                $formatted_item->code = $item->budget_pricing->code;
                $formatted_item->qty = $item->count;
                $formatted_item->price = $item->price;
                $formatted_item->ticket_month = $ticket->created_at->format('m');
                $formatted_item->ticket_year = $ticket->created_at->format('Y');
                // TEST MANIPULATE DATA
                // if($count>1){
                //     $formatted_item->ticket_month = "05";
                // }
                // if($count>2){
                //     $formatted_item->ticket_year = "2023";
                // }
                array_push($items, $formatted_item);
                $count++;
            }
        }
        $grouped_items = collect($items)->groupBy('ticket_year');
        foreach ($grouped_items as $key => $yeargroup) {
            $items_group_by_month = $yeargroup->groupBy('ticket_month');
            $grouped_items[$key] = $items_group_by_month;
        }
        $start_column = 7;
        $alphabet = range('A', 'Z');
        $max_column = 5;

        foreach ($grouped_items as $year => $items) {
            foreach ($items as $month => $item) {
                $sheet->mergeCells($alphabet[$start_column - 1] . "5:" . $alphabet[$start_column + 1] . "5");
                $sheet->mergeCells($alphabet[$start_column - 1] . "6:" . $alphabet[$start_column + 1] . "6");
                $cellname = Carbon::create()->year($year)->month($month)->translatedFormat('F Y');
                $sheet->setCellValue($alphabet[$start_column - 1] . "6", $cellname);
                $sheet->setCellValue($alphabet[$start_column - 1] . "5", "Actual");
                $sheet->setCellValue($alphabet[$start_column - 1] . "7", "Qty");
                $sheet->setCellValue($alphabet[$start_column] . "7", "Value");
                $sheet->setCellValue($alphabet[$start_column + 1] . "7", "Amount");
                $sheet->getStyle($alphabet[$start_column - 1] . "5:" . $alphabet[$start_column + 1] . "7")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                if ($start_column + 1 > $max_column) {
                    $max_column = $start_column + 1;
                }
                $start_column += 3;
            }
        }

        // saldo akhir header
        $max_column += 3;
        $sheet->mergeCells($alphabet[$max_column - 2] . "5:" . $alphabet[$max_column] . "5");
        $sheet->mergeCells($alphabet[$max_column - 2] . "6:" . $alphabet[$max_column] . "6");
        $sheet->setCellValue($alphabet[$max_column - 2] . "5", "Saldo Akhir");
        $sheet->setCellValue($alphabet[$max_column - 2] . "7", "Qty");
        $sheet->setCellValue($alphabet[$max_column - 1] . "7", "Value");
        $sheet->setCellValue($alphabet[$max_column] . "7", "Amount");
        $sheet->getStyle($alphabet[$max_column - 2] . "5:" . $alphabet[$max_column] . "7")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $total_row = 0;
        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $max_row_by_item = 1;
            $sheet->setCellValue("A" . $total_row, $budget->code);
            $sheet->setCellValue("B" . $total_row, $budget->budget_pricing_category->name);
            $sheet->setCellValue("C" . $total_row, $budget->name);

            $old_item = $old_budget->budget_detail->where('code', $budget->code)->first() ?? null;
            if ($old_item) {
                $sisa_qty = $old_item->qty;
                $sheet->setCellValue("D" . $total_row, $old_item->qty);
                $sheet->setCellValue("E" . $total_row, $old_item->value);
                $sheet->setCellValue("F" . $total_row, $old_item->qty * $old_item->value);

                $start_column = 7;
                foreach ($grouped_items as $year => $months) {
                    foreach ($months as $month => $items) {
                        $item_found_count = 0;
                        foreach ($items as $item) {
                            if ($item->code == $budget->code) {
                                $sisa_qty -= $item->qty;
                                $sheet->setCellValue($alphabet[$start_column - 1] . ($total_row + $item_found_count), $item->qty);
                                $sheet->setCellValue($alphabet[$start_column] . ($total_row + $item_found_count), $item->price);
                                $sheet->setCellValue($alphabet[$start_column + 1] . ($total_row + $item_found_count), $item->qty * $item->price);
                                $item_found_count++;
                            }
                        }
                        if ($item_found_count > $max_row_by_item) {
                            $max_row_by_item = $item_found_count;
                        }
                        $start_column += 3;
                    }
                }

                $sheet->setCellValue($alphabet[$start_column - 1] . $total_row, $sisa_qty);
                $sheet->setCellValue($alphabet[$start_column] . $total_row, $old_item->value);
                $sheet->setCellValue($alphabet[$start_column + 1] . $total_row, $sisa_qty * $old_item->value);
            }

            $count_row += $max_row_by_item;
        }

        $sheet->getStyle("A8:" . $alphabet[$max_column] . $total_row)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1");
        $sheet->getProtection()->setSheet(true);
        $protection = $sheet->getProtection();
        $protection->setPassword('pods2022');
    }
    public function modifySheetArmadaBudget($sheet, $old_budget)
    {
        $budgets = $old_budget->budget_detail;
        $salespoint = SalesPoint::findOrFail(request()->salespoint_id);
        $sheet->setCellValue("B2", $old_budget->salespoint->name . " | " . $old_budget->salespoint->code);
        $sheet->setCellValue("B3", now()->format("d-m-Y"));
        $sheet->setCellValue("B4", $old_budget->code);
        $sheet->setCellValue("B5", $old_budget->year);
        $start_row = 8;
        $count_row = 0;

        $old_budgets_tickets = ArmadaTicket::where('budget_upload_id', $old_budget->id)->get();
        $items = [];
        $count = 0;
        foreach ($old_budgets_tickets as $armadaticket) {
            $formatted_item                    = new \stdClass();
            $formatted_item->armada_type       = $armadaticket->armada_type->name;
            $formatted_item->vendor            = $armadaticket->vendor_recommendation_name ?? $armadaticket->vendor_name ?? "";
            $formatted_item->qty               = 1;
            // TODO armada price
            $formatted_item->price             = null;
            $formatted_item->ticket_month      = $armadaticket->created_at->format('m');
            $formatted_item->ticket_year       = $armadaticket->created_at->format('Y');
            array_push($items, $formatted_item);
            $count++;
        }
        $grouped_items = collect($items)->groupBy('ticket_year');
        foreach ($grouped_items as $key => $yeargroup) {
            $items_group_by_month = $yeargroup->groupBy('ticket_month');
            $grouped_items[$key] = $items_group_by_month;
        }
        $start_column = 6;
        $alphabet = range('A', 'Z');
        $max_column = 4;

        foreach ($grouped_items as $year => $items) {
            foreach ($items as $month => $item) {
                $sheet->mergeCells($alphabet[$start_column - 1] . "5:" . $alphabet[$start_column + 1] . "5");
                $sheet->mergeCells($alphabet[$start_column - 1] . "6:" . $alphabet[$start_column + 1] . "6");
                $cellname = Carbon::create()->year($year)->month($month)->translatedFormat('F Y');
                $sheet->setCellValue($alphabet[$start_column - 1] . "6", $cellname);
                $sheet->setCellValue($alphabet[$start_column - 1] . "5", "Actual");
                $sheet->setCellValue($alphabet[$start_column - 1] . "7", "Qty");
                $sheet->setCellValue($alphabet[$start_column] . "7", "Value");
                $sheet->setCellValue($alphabet[$start_column + 1] . "7", "Amount");
                $sheet->getStyle($alphabet[$start_column - 1] . "5:" . $alphabet[$start_column + 1] . "7")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                if ($start_column + 1 > $max_column) {
                    $max_column = $start_column + 1;
                }
                $start_column += 3;
            }
        }

        // saldo akhir header
        $max_column += 3;
        $sheet->mergeCells($alphabet[$max_column - 2] . "5:" . $alphabet[$max_column] . "5");
        $sheet->mergeCells($alphabet[$max_column - 2] . "6:" . $alphabet[$max_column] . "6");
        $sheet->setCellValue($alphabet[$max_column - 2] . "5", "Saldo Akhir");
        $sheet->setCellValue($alphabet[$max_column - 2] . "7", "Qty");
        $sheet->setCellValue($alphabet[$max_column - 1] . "7", "Value");
        $sheet->setCellValue($alphabet[$max_column] . "7", "Amount");
        $sheet->getStyle($alphabet[$max_column - 2] . "5:" . $alphabet[$max_column] . "7")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $total_row = 0;
        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $max_row_by_item = 1;
            $sheet->setCellValue("A" . $total_row, $budget->armada_type_name);
            $sheet->setCellValue("B" . $total_row, $budget->vendor_name);

            $old_item = $old_budget->budget_detail->where('armada_type_name', $budget->armada_type_name)->where('vendor_name', $budget->vendor_name)->first() ?? null;
            if ($old_item) {
                $sisa_qty = $old_item->qty;
                $sheet->setCellValue("C" . $total_row, $old_item->qty);
                $sheet->setCellValue("D" . $total_row, $old_item->value ?? "");
                $sheet->setCellValue("E" . $total_row, ($old_item->value) ? $old_item->qty * $old_item->value : "");

                $start_column = 6;
                foreach ($grouped_items as $year => $months) {
                    foreach ($months as $month => $items) {
                        $item_found_count = 0;
                        foreach ($items as $item) {
                            if ($item->armada_type == $budget->armada_type_name && $item->vendor == $budget->vendor_name) {
                                $sisa_qty -= $item->qty;
                                $sheet->setCellValue($alphabet[$start_column - 1] . ($total_row + $item_found_count), $item->qty);
                                $sheet->setCellValue($alphabet[$start_column] . ($total_row + $item_found_count), $item->price ?? "");
                                $sheet->setCellValue($alphabet[$start_column + 1] . ($total_row + $item_found_count), ($item->price) ? $item->qty * $item->price : "");
                                $item_found_count++;
                            }
                        }
                        if ($item_found_count > $max_row_by_item) {
                            $max_row_by_item = $item_found_count;
                        }
                        $start_column += 3;
                    }
                }

                $sheet->setCellValue($alphabet[$start_column - 1] . $total_row, $sisa_qty);
                $sheet->setCellValue($alphabet[$start_column] . $total_row, $old_item->value ?? "");
                $sheet->setCellValue($alphabet[$start_column + 1] . $total_row, ($old_item->value) ? $sisa_qty * $old_item->value : "");
            }

            $count_row += $max_row_by_item;
        }

        $sheet->getStyle("A8:" . $alphabet[$max_column] . $total_row)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1");
        $sheet->getProtection()->setSheet(true);
        $protection = $sheet->getProtection();
        $protection->setPassword('pods2022');
    }
    public function modifySheetAssumptionBudget($sheet, $old_budget)
    {
        $budgets = MaintenanceBudget::all();
        $salespoint = SalesPoint::findOrFail(request()->salespoint_id);
        $sheet->setCellValue("B2", $old_budget->salespoint->name . " | " . $old_budget->salespoint->code);
        $sheet->setCellValue("B3", now()->format("d-m-Y"));
        $sheet->setCellValue("B4", $old_budget->code);
        $sheet->setCellValue("B5", $old_budget->year);
        $start_row = 8;
        $count_row = 0;

        $old_budgets_tickets = Ticket::where('budget_upload_id', $old_budget->id)->get();
        $items = [];
        $count = 0;
        foreach ($old_budgets_tickets as $ticket) {
            foreach ($ticket->ticket_item as $key => $item) {
                $formatted_item = new \stdClass();
                $formatted_item->code = $item->maintenance_budget->code;
                $formatted_item->qty = $item->count;
                $formatted_item->price = $item->price;
                $formatted_item->ticket_month = $ticket->created_at->format('m');
                $formatted_item->ticket_year = $ticket->created_at->format('Y');
                array_push($items, $formatted_item);
                $count++;
            }
        }
        $grouped_items = collect($items)->groupBy('ticket_year');
        foreach ($grouped_items as $key => $yeargroup) {
            $items_group_by_month = $yeargroup->groupBy('ticket_month');
            $grouped_items[$key] = $items_group_by_month;
        }
        $start_column = 7;
        $alphabet = range('A', 'Z');
        $max_column = 5;

        foreach ($grouped_items as $year => $items) {
            foreach ($items as $month => $item) {
                $sheet->mergeCells($alphabet[$start_column - 1] . "5:" . $alphabet[$start_column + 1] . "5");
                $sheet->mergeCells($alphabet[$start_column - 1] . "6:" . $alphabet[$start_column + 1] . "6");
                $cellname = Carbon::create()->year($year)->month($month)->translatedFormat('F Y');
                $sheet->setCellValue($alphabet[$start_column - 1] . "6", $cellname);
                $sheet->setCellValue($alphabet[$start_column - 1] . "5", "Actual");
                $sheet->setCellValue($alphabet[$start_column - 1] . "7", "Qty");
                $sheet->setCellValue($alphabet[$start_column] . "7", "Value");
                $sheet->setCellValue($alphabet[$start_column + 1] . "7", "Amount");
                $sheet->getStyle($alphabet[$start_column - 1] . "5:" . $alphabet[$start_column + 1] . "7")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                if ($start_column + 1 > $max_column) {
                    $max_column = $start_column + 1;
                }
                $start_column += 3;
            }
        }

        // saldo akhir header
        $max_column += 3;
        $sheet->mergeCells($alphabet[$max_column - 2] . "5:" . $alphabet[$max_column] . "5");
        $sheet->mergeCells($alphabet[$max_column - 2] . "6:" . $alphabet[$max_column] . "6");
        $sheet->setCellValue($alphabet[$max_column - 2] . "5", "Saldo Akhir");
        $sheet->setCellValue($alphabet[$max_column - 2] . "7", "Qty");
        $sheet->setCellValue($alphabet[$max_column - 1] . "7", "Value");
        $sheet->setCellValue($alphabet[$max_column] . "7", "Amount");
        $sheet->getStyle($alphabet[$max_column - 2] . "5:" . $alphabet[$max_column] . "7")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $total_row = 0;
        foreach ($budgets as $budget) {
            $total_row = $start_row + $count_row;
            $max_row_by_item = 1;
            $sheet->setCellValue("A" . $total_row, $budget->code);
            $sheet->setCellValue("B" . $total_row, $budget->category_name);
            $sheet->setCellValue("C" . $total_row, $budget->name);

            $old_item = $old_budget->budget_detail->where('code', $budget->code)->first() ?? null;
            if ($old_item) {
                $sisa_qty = $old_item->qty;
                $sheet->setCellValue("D" . $total_row, $old_item->qty);
                $sheet->setCellValue("E" . $total_row, $old_item->value);
                $sheet->setCellValue("F" . $total_row, $old_item->qty * $old_item->value);

                $start_column = 7;
                foreach ($grouped_items as $year => $months) {
                    foreach ($months as $month => $items) {
                        $item_found_count = 0;
                        foreach ($items as $item) {
                            if ($item->code == $budget->code) {
                                $sisa_qty -= $item->qty;
                                $sheet->setCellValue($alphabet[$start_column - 1] . ($total_row + $item_found_count), $item->qty);
                                $sheet->setCellValue($alphabet[$start_column] . ($total_row + $item_found_count), $item->price);
                                $sheet->setCellValue($alphabet[$start_column + 1] . ($total_row + $item_found_count), $item->qty * $item->price);
                                $item_found_count++;
                            }
                        }
                        if ($item_found_count > $max_row_by_item) {
                            $max_row_by_item = $item_found_count;
                        }
                        $start_column += 3;
                    }
                }

                $sheet->setCellValue($alphabet[$start_column - 1] . $total_row, $sisa_qty);
                $sheet->setCellValue($alphabet[$start_column] . $total_row, $old_item->value);
                $sheet->setCellValue($alphabet[$start_column + 1] . $total_row, $sisa_qty * $old_item->value);
            }

            $count_row += $max_row_by_item;
        }

        $sheet->getStyle("A8:" . $alphabet[$max_column] . $total_row)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1");
        $sheet->getProtection()->setSheet(true);
        $protection = $sheet->getProtection();
        $protection->setPassword('pods2022');
    }
    public function getActiveBudget()
    {
        $activebudgetupload = BudgetUpload::where('type', request()->budget_type)
            ->where('salespoint_id', request()->salespoint_id)
            ->where('status', 1)
            ->where('year', request()->year)
            ->first();
        if ($activebudgetupload) {
            return response()->json([
                "error" => false,
                "data" => [
                    "budget_code" => $activebudgetupload->code,
                    "budget_created_at" => $activebudgetupload->created_at,
                ],
                "message" => "Budget ditemukan"
            ]);
        } else {
            return response()->json([
                "error" => true,
                "message" => "Budget aktif tidak ditemukan"
            ]);
        }
    }

    public function getNonActiveBudget()
    {
        $nonactivebudgetupload = BudgetUpload::where('type', request()->budget_type)
            ->where('salespoint_id', request()->salespoint_id)
            ->where('status', 2)
            ->where('year', request()->year)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($nonactivebudgetupload) {
            return response()->json([
                "error" => false,
                "data" => [
                    "budget_code" => $nonactivebudgetupload->code,
                    "budget_created_at" => $nonactivebudgetupload->created_at,
                ],
                "message" => "Budget Tahun Sebelumnya ditemukan"
            ]);
        } else {
            return response()->json([
                "error" => true,
                "message" => "Budget Tahun Sebelumnya tidak ditemukan"
            ]);
        }
    }

    public function nonBudget(Request $request)
    {

        $salespoint = SalesPoint::find(request()->salespoint_id);
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($salespoint->id)) {
            return redirect('/downloadreport')->with('error', 'Anda tidak memiliki akses untuk download report terkait area "' . $salespoint->name);
        }
        $start_date = request()->start_date;
        $end_date = request()->end_date;
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/non_budget_report_template.xlsx");

        $sheet = $spreadsheet->getSheetByName('Non Budget Report');
        $this->modifySheetNonBudget($sheet);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $salespoint->name . ' Non Budget Report (' . $start_date . '-' . $end_date . ').xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
    public function modifySheetNonBudget($sheet)
    {
        $salespoint = SalesPoint::find(request()->salespoint_id);
        $start_date = \Carbon\Carbon::parse(request()->start_date);
        $end_date = \Carbon\Carbon::parse(request()->end_date);

        $sheet->setCellValue("B2", $salespoint->name . " | " . $salespoint->code);
        $sheet->setCellValue("B3", $start_date->translatedFormat("d F Y") . "-" . $end_date->translatedFormat("d F Y"));
        $sheet->setCellValue("B4", now()->translatedFormat('d F Y'));
        $start_row = 8;
        $count_row = 0;

        // find non budget transaction between start and end date created transaction
        $tickets = Ticket::where('salespoint_id', $salespoint->id)
            ->where('status', '>', 0)
            ->where('budget_type', 1)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->get();
        foreach ($tickets as $ticket) {
            foreach ($ticket->ticket_item as $key => $ticket_item) {
                $total_row = $start_row + $count_row;
                $sheet->setCellValue("A" . $total_row, $ticket_item->name);
                $sheet->setCellValue("B" . $total_row, $ticket_item->brand);
                $sheet->setCellValue("C" . $total_row, $ticket_item->type);
                $sheet->setCellValue("D" . $total_row, $ticket_item->count);
                $sheet->setCellValue("E" . $total_row, $ticket_item->price);
                $sheet->setCellValue("F" . $total_row, $ticket_item->count * $ticket_item->price);
                $sheet->setCellValue("G" . $total_row, $ticket_item->ticket->code);
                $sheet->setCellValue("H" . $total_row, $ticket_item->ticket->created_at->translatedFormat('d-m-Y'));
                $po_array = $ticket_item->ticket->po->pluck("no_po_sap");
                $po_array = $po_array->filter(function ($po) {
                    if (trim($po) != "") {
                        return true;
                    } else {
                        return false;
                    }
                });
                $sheet->setCellValue("I" . $total_row, implode(", ", $po_array->toArray()));
                $sheet->setCellValue("J" . $total_row, $ticket_item->ticket->status());
                $count_row++;
            }
        }

        $sheet->getStyle("A1");
        $sheet->getProtection()->setSheet(true);
        $protection = $sheet->getProtection();
        $protection->setPassword('pods2022');
    }
    public function poReport(Request $request)
    {
        $salespoint_id = $request->salespoint_id;
        $salespoint_name = "";
        $salespoint_code = "";
        if ($salespoint_id == "all") {;
            $salespoint_name = "All";
            $salespoint_code = "All";
        } else {
            $salespoint_name = SalesPoint::find($salespoint_id)->name;
            $salespoint_code = SalesPoint::find($salespoint_id)->code;

            // validate budget detail has akses area
            $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
            if (!$user_location_access->contains($salespoint_id)) {
                return redirect('/downloadreport')->with('error', 'Anda tidak memiliki akses untuk download report terkait area "' . $salespoint_name);
            }
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/po_report_template.xlsx");
        $sheet = $spreadsheet->getSheetByName('PO Report');

        $start_date = \Carbon\Carbon::parse(request()->start_date);
        $end_date = \Carbon\Carbon::parse(request()->end_date);

        $sheet->setCellValue("B2", $salespoint_name . " | " . $salespoint_code);
        $sheet->setCellValue("B3", $start_date->translatedFormat("d F Y") . "-" . $end_date->translatedFormat("d F Y"));
        $sheet->setCellValue("B4", now()->translatedFormat('d F Y'));

        $this->modifySheetPoReport($sheet);

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $salespoint_name . ' PO Report (' . $start_date->format('Y-m-d') . '-' . $end_date->format('Y-m-d') . ').xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function modifySheetPoReport($sheet)
    {
        $start_row = 8;
        $count_row = 0;

        $salespoint_id = request()->salespoint_id;
        $start_date = request()->start_date;
        $end_date = request()->end_date;
        if ($salespoint_id == "all") {
            $salespoint_id = Auth::user()->location_access->pluck('salespoint_id');
        } else {
            $salespoint_id = [$salespoint_id];
        }
        // find non budget transaction between start and end date created transaction
        $pos = Po::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->get();

        foreach ($pos as $po) {
            $total_row = $start_row + $count_row;
            $sheet->setCellValue("A" . $total_row, $count_row + 1);
            try {
                $first_po_author = $po->po_authorization->first();
                $sheet->setCellValue("B" . $total_row, $first_po_author->employee_name . "(" . $first_po_author->employee_position . ")");
            } catch (\Exception $ex) {
            }
            if ($po->ticket) {
                $ticket = $po->ticket;
                $sheet->setCellValue("C" . $total_row, $ticket->code);

                // get update_at for fill column D 'tgl terima penawaran'
                $get_date = TicketAuthorization::where('ticket_id',$ticket->id)
                        ->where('status','=', '1')
                        ->select('updated_at')
                        ->orderBy('level', 'desc')
                        ->first();

                $sheet->setCellValue("D" . $total_row, $get_date->updated_at->format('Y-m-d'));

                $get_date_validate_file = TicketItemFileRequirement::leftJoin('ticket_item', 'ticket_item_file_requirement.ticket_item_id', '=', 'ticket_item.id')
                        ->where('ticket_item.ticket_id',$ticket->id)
                        ->where('ticket_item.deleted_at', '=', null)
                        ->select('ticket_item_file_requirement.updated_at')
                        ->orderBy('ticket_item_file_requirement.updated_at', 'desc')
                        ->first();
                if ($get_date_validate_file){
                    // get update_at for fill column E 'tgl validasi kelengkapan berkas'
                    $sheet->setCellValue("E" . $total_row, $get_date_validate_file->updated_at->format('Y-m-d'));
                }

                $sheet->setCellValue("F" . $total_row, $ticket->salespoint->name);
                $sheet->setCellValue("G" . $total_row, $ticket->salespoint->region_name());
                $sheet->setCellValue("H" . $total_row, implode(", ", $po->po_detail->pluck('item_name')->toArray()));
                $sheet->setCellValue("I" . $total_row, implode(", ", $ticket->ticket_item->pluck("brand")->toArray()));
                $sheet->setCellValue("J" . $total_row, $ticket->request_type());
                $selected_vendor_array = [];
                $vendor_array = [];
                $ready_array = [];
                $address_array = [];
                $approval_date_array = [];

                foreach ($ticket->bidding as $bidding) {
                    $selectedVendor = $bidding->selected_vendor();
                    if ($selectedVendor && $selectedVendor->ticket_vendor) {
                        array_push($selected_vendor_array, $selectedVendor->ticket_vendor->name);
                        array_push($ready_array, $selectedVendor->ready);
                        array_push($address_array, $selectedVendor->address);
                        array_push($approval_date_array, $bidding->updated_at->format('Y-m-d'));

                        foreach ($bidding->bidding_detail as $bidding_detail) {
                            if ($bidding_detail->ticket_vendor) {
                                array_push($vendor_array, $bidding_detail->ticket_vendor->name);
                            }
                        }
                    }
                }

                $vendor_filter = array_unique($vendor_array);
                $vendor_one = isset($vendor_filter[0]) ? $vendor_filter[0] : null;
                $vendor_two = isset($vendor_filter[1]) ? $vendor_filter[1] : null;

                $sheet->setCellValue("K" . $total_row, $vendor_one);
                $sheet->setCellValue("L" . $total_row, $vendor_two);
                $sheet->setCellValue("M" . $total_row, implode(",", array_unique($ready_array)));
                $sheet->setCellValue("N" . $total_row, implode(",", array_unique($selected_vendor_array)));
                $sheet->setCellValue("O" . $total_row, implode(",", array_unique($address_array)));
                $sheet->setCellValue("P" . $total_row, implode(",", array_unique($approval_date_array)));
                $asset_number_array = [];

                if ($ticket->pr) {
                    $sheet->setCellValue("R" . $total_row, $ticket->pr->updated_at->format('Y-m-d'));
                    foreach ($ticket->pr->pr_detail as $pr_detail) {
                        foreach ($pr_detail->asset_numbers_array() as $number) {
                            array_push($asset_number_array, $number);
                        }
                    }
                    $sheet->setCellValue("T" . $total_row, implode(",", array_unique($asset_number_array)));
                }
            }
            if ($po->armada_ticket) {
                $armada_ticket = $po->armada_ticket;
                $sheet->setCellValue("C" . $total_row, $armada_ticket->code);

                // get update_at for fill column D 'tgl terima penawaran'
                $get_date_armada = ArmadaTicketAuthorization::where('armada_ticket_id',$armada_ticket->id)
                        ->select('updated_at')
                        ->orderBy('level', 'desc')
                        ->first();

                if ($get_date_armada){
                    $sheet->setCellValue("D" . $total_row, $get_date_armada->updated_at->format('Y-m-d'));
                }

                $sheet->setCellValue("F" . $total_row, $armada_ticket->salespoint->name);
                $sheet->setCellValue("G" . $total_row, $armada_ticket->salespoint->region_name());
                $sheet->setCellValue("H" . $total_row, $armada_ticket->type());
                $sheet->setCellValue("I" . $total_row, $armada_ticket->vendor_recommendation_name ?? "");
                $sheet->setCellValue("J" . $total_row, $armada_ticket->vendor_name ?? "");
                if ($armada_ticket->pr) {
                    $sheet->setCellValue("R" . $total_row, $armada_ticket->pr->updated_at->format('Y-m-d'));
                }
            }
            if ($po->security_ticket) {
                $security_ticket = $po->security_ticket;
                $sheet->setCellValue("C" . $total_row, $security_ticket->code);

                // get update_at for fill column D 'tgl terima penawaran'
                $get_date_security = SecurityTicketAuthorization::where('security_ticket_id',$security_ticket->id)
                        ->select('updated_at')
                        ->orderBy('level', 'desc')
                        ->first();

                if ($get_date_security){
                    $sheet->setCellValue("D" . $total_row, $get_date_security->updated_at->format('Y-m-d'));
                }

                $sheet->setCellValue("F" . $total_row, $security_ticket->salespoint->name);
                $sheet->setCellValue("G" . $total_row, $security_ticket->salespoint->region_name());
                $sheet->setCellValue("H" . $total_row, $security_ticket->type());
                $sheet->setCellValue("I" . $total_row, $security_ticket->vendor_recommendation_name ?? "");
                $sheet->setCellValue("J" . $total_row, $security_ticket->vendor_name ?? "");
                if ($security_ticket->pr) {
                    $sheet->setCellValue("R" . $total_row, $security_ticket->pr->updated_at->format('Y-m-d'));
                }
            }
            $sheet->setCellValue("S" . $total_row, $po->no_pr_sap);
            $sheet->setCellValue("U" . $total_row, $po->no_po_sap);
            $total = 0;
            $qty = 0;
            foreach ($po->po_detail as $po_detail) {
                $qty += $po_detail->qty;
                $total += $po_detail->qty * $po_detail->item_price;
            }

            $count_row++;
        }

        $sheet->getStyle("A1");
        $sheet->getProtection()->setSheet(true);
        $protection = $sheet->getProtection();
        $protection->setPassword('pods2022');
    }

    public function get_recived_date_po($code)
    {
        $recived_date =  Ticket::leftJoin('ticket_authorization', 'ticket_authorization.ticket_id', '=', 'ticket.id')
            ->where(DB::raw('ticket.code'), '=', $code)
            ->select('ticket.id, ticket_authorization.updated_at, ticket_authorization.level')
            ->orderBy('ticket_authorization.level', 'desc')
            ->distinct('ticket.id')
            ->first();
    }

    public function has_pr_sap()
    {
        $ticketing =  Ticket::leftJoin('salespoint', 'salespoint.id', '=', 'ticket.salespoint_id')
            ->leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->leftJoin('ticket_authorization', 'ticket_authorization.ticket_id', '=', 'ticket.id')
            ->leftJoin('employee', 'employee.id', '=', 'ticket.created_by')
            ->leftJoin('po', 'ticket.id', '=', 'po.ticket_id')
            ->where(DB::raw('MONTH(ticket.created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(ticket.created_at)'), '=', date('Y'))
            ->select('ticket.*')
            ->orderBy('ticket.status', 'desc')
            ->orderBy('ticket.created_at', 'desc')
            ->distinct('ticket.id')
            ->get();

        $check_pr_by_ticket_code = DB::table('pr_sap')->where("data", "like", '%' . '"textnote":"' . $ticketing->code . '"' . '%')->first();
    }

    public function has_pr_sap_armada()
    {
        $armada_ticketing =  ArmadaTicket::leftJoin('salespoint', 'salespoint.id', '=', 'armada_ticket.salespoint_id')
            ->leftJoin('employee', 'employee.id', '=', 'armada_ticket.created_by')
            ->leftJoin('armada_ticket_authorization', 'armada_ticket_authorization.armada_ticket_id', '=', 'armada_ticket.id')
            ->leftJoin('po', 'armada_ticket.id', '=', 'po.armada_ticket_id')
            ->where(DB::raw('MONTH(armada_ticket.created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(armada_ticket.created_at)'), '=', date('Y'))
            ->select('armada_ticket.*')
            ->orderBy('armada_ticket.status', 'desc')
            ->orderBy('armada_ticket.created_at', 'desc')
            ->distinct('armada_ticket.id')
            ->get();

        $check_pr_by_ticket_code = DB::table('pr_sap')->where("data", "like", '%' . '"textnote":"' . $armada_ticketing->code . '"' . '%')->first();
    }

    public function has_pr_sap_security()
    {
        $security_ticketing =  SecurityTicket::leftJoin('salespoint', 'salespoint.id', '=', 'security_ticket.salespoint_id')
            ->leftJoin('employee', 'employee.id', '=', 'security_ticket.created_by')
            ->leftJoin('security_ticket_authorization', 'security_ticket_authorization.security_ticket_id', '=', 'security_ticket.id')
            ->leftJoin('po', 'security_ticket.id', '=', 'po.security_ticket_id')
            ->where(DB::raw('MONTH(security_ticket.created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(security_ticket.created_at)'), '=', date('Y'))
            ->select('security_ticket.*')
            ->orderBy('security_ticket.status', 'desc')
            ->orderBy('security_ticket.created_at', 'desc')
            ->distinct('security_ticket.id')
            ->get();

        $check_pr_by_ticket_code = DB::table('pr_sap')->where("data", "like", '%' . '"textnote":"' . $security_ticketing->code . '"' . '%')->first();
    }

    public function reportTicketing()
    {
        $spreadsheet   = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/ticketing_report_template.xlsx");
        $sheetTicket   = $spreadsheet->getSheetByName('Ticket');
        $sheetArmada   = $spreadsheet->getSheetByName('Armada');
        $sheetSecurity = $spreadsheet->getSheetByName('Security');

        #ticketing
        $ticketing =  Ticket::leftJoin('salespoint', 'salespoint.id', '=', 'ticket.salespoint_id')
            ->leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->leftJoin('ticket_authorization', 'ticket_authorization.ticket_id', '=', 'ticket.id')
            ->leftJoin('employee', 'employee.id', '=', 'ticket.created_by')
            ->leftJoin('po', 'ticket.id', '=', 'po.ticket_id')
            ->where(DB::raw('MONTH(ticket.created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(ticket.created_at)'), '=', date('Y'))
            ->select('ticket.*')
            ->orderBy('ticket.status', 'desc')
            ->orderBy('ticket.created_at', 'desc')
            ->distinct('ticket.id')
            ->get();

        $rowTicket = 2;

        foreach ($ticketing as $ticket) {
            $sheetTicket->setCellValue("A" . $rowTicket, $ticket->code);
            $sheetTicket->setCellValue("B" . $rowTicket, $ticket->salespoint->name);
            $sheetTicket->setCellValue("C" . $rowTicket, $ticket->created_at->translatedFormat('d F Y'));

            if ($ticket->created_by == null) {
                $sheetTicket->setCellValue("D" . $rowTicket, '-');
            } else {
                $sheetTicket->setCellValue("D" . $rowTicket, $ticket->created_by_employee->name);
            }

            $sheetTicket->setCellValue("E" . $rowTicket, Carbon::parse($ticket->requirement_date)->translatedFormat('d F Y'));
            if ($ticket->status > 2) {
                $sheetTicket->setCellValue("F" . $rowTicket, 'Done');
            } else {
                $sheetTicket->setCellValue("F" . $rowTicket, 'Undone');
            }
            if ($ticket->status > 4) {
                $sheetTicket->setCellValue("G" . $rowTicket, 'Done');
            } else {
                $sheetTicket->setCellValue("G" . $rowTicket, 'Undone');
            }
            if ($ticket->status > 5 && $ticket->has_pr_sap()) {
                $sheetTicket->setCellValue("H" . $rowTicket, 'Done');
            } else {
                $sheetTicket->setCellValue("H" . $rowTicket, 'Undone');
            }
            if ($ticket->status > 5) {
                if ($ticket->po) {
                    foreach ($ticket->po as $tpo) {
                        if ($tpo->status == 3) {
                            $sheetTicket->setCellValue("I" . $rowTicket, 'Done');
                        } elseif ($tpo->deleted_at) {
                            $sheetTicket->setCellValue("I" . $rowTicket, 'Undone');
                        } else {
                            $sheetTicket->setCellValue("I" . $rowTicket, 'Undone');
                        }
                    }
                } else {
                    $sheetTicket->setCellValue("I" . $rowTicket, 'Undone');
                }
            } else {
                $sheetTicket->setCellValue("I" . $rowTicket, 'Undone');
            }
            if ($ticket->status > 5) {
                if ($ticket->ticket_item) {
                    foreach ($ticket->ticket_item as $titem) {
                        if ($titem->lpb_filepath) {
                            if ($titem->isCancelled == 0) {
                                $sheetTicket->setCellValue("J" . $rowTicket, 'Done');
                            } elseif ($titem->isCancelled == 1) {
                                $sheetTicket->setCellValue("J" . $rowTicket, 'Undone');
                            }
                        }
                    }
                }
            } else {
                $sheetTicket->setCellValue("J" . $rowTicket, 'Undone');
            }
            $rowTicket++;
        }

        #armada
        $armada_ticketing =  ArmadaTicket::leftJoin('salespoint', 'salespoint.id', '=', 'armada_ticket.salespoint_id')
            ->leftJoin('employee', 'employee.id', '=', 'armada_ticket.created_by')
            ->leftJoin('armada_ticket_authorization', 'armada_ticket_authorization.armada_ticket_id', '=', 'armada_ticket.id')
            ->leftJoin('po', 'armada_ticket.id', '=', 'po.armada_ticket_id')
            ->where(DB::raw('MONTH(armada_ticket.created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(armada_ticket.created_at)'), '=', date('Y'))
            ->select('armada_ticket.*')
            ->orderBy('armada_ticket.status', 'desc')
            ->orderBy('armada_ticket.created_at', 'desc')
            ->distinct('armada_ticket.id')
            ->get();

        $rowArmada = 2;

        foreach ($armada_ticketing as $armada) {
            $sheetArmada->setCellValue("A" . $rowArmada, $armada->code);
            $sheetArmada->setCellValue("B" . $rowArmada, $armada->salespoint->name);
            $sheetArmada->setCellValue("C" . $rowArmada, $armada->created_at->translatedFormat('d F Y'));
            $sheetArmada->setCellValue("D" . $rowArmada, $armada->created_by_employee->name);
            if ($armada->type() == 'Perpanjangan') {
                $sheetArmada->setCellValue("E" . $rowArmada, $armada->type() . "\n( " . $armada->perpanjangan_form->perpanjangan_length . " Bulan )");
            } else {
                $sheetArmada->setCellValue("E" . $rowArmada, $armada->type());
            }
            $sheetArmada->setCellValue("F" . $rowArmada, Carbon::parse($armada->requirement_date)->translatedFormat('d F Y'));

            if ($armada->status > 3) {
                $sheetArmada->setCellValue("G" . $rowArmada, 'Done');
            } else {
                $sheetArmada->setCellValue("G" . $rowArmada, 'Undone');
            }
            if ($armada->status > 3 && $armada->has_pr_sap()) {
                $sheetArmada->setCellValue("H" . $rowArmada, 'Done');
            } else {
                $sheetArmada->setCellValue("H" . $rowArmada, 'Undone');
            }
            if ($armada->status > 3) {
                if ($armada->po) {
                    foreach ($armada->po as $tpo) {
                        if ($tpo->status == 3) {
                            $sheetArmada->setCellValue("I" . $rowArmada, 'Done');
                        } else {
                            $sheetArmada->setCellValue("I" . $rowArmada, 'Undone');
                        }
                    }
                } else {
                    $sheetArmada->setCellValue("I" . $rowArmada, 'Undone');
                }
            } else {
                $sheetArmada->setCellValue("I" . $rowArmada, 'Undone');
            }
            if ($armada->status > 4 && (in_array($armada->type(), ['Pengadaan', 'Replace', 'Renewal']))) {
                if ($armada->bastk_path) {
                    $sheetArmada->setCellValue("J" . $rowArmada, 'Done');
                } else {
                    $sheetArmada->setCellValue("J" . $rowArmada, 'Undone');
                }
            } else {
                $sheetArmada->setCellValue("J" . $rowArmada, 'Undone');
            }
            $rowArmada++;
        }

        #Security
        $security_ticketing =  SecurityTicket::leftJoin('salespoint', 'salespoint.id', '=', 'security_ticket.salespoint_id')
            ->leftJoin('employee', 'employee.id', '=', 'security_ticket.created_by')
            ->leftJoin('security_ticket_authorization', 'security_ticket_authorization.security_ticket_id', '=', 'security_ticket.id')
            ->leftJoin('po', 'security_ticket.id', '=', 'po.security_ticket_id')
            ->where(DB::raw('MONTH(security_ticket.created_at)'), '=', date('m'))
            ->where(DB::raw('YEAR(security_ticket.created_at)'), '=', date('Y'))
            ->select('security_ticket.*')
            ->orderBy('security_ticket.status', 'desc')
            ->orderBy('security_ticket.created_at', 'desc')
            ->distinct('security_ticket.id')
            ->get();

        $rowSecurity = 2;

        foreach ($security_ticketing as $security) {
            $sheetSecurity->setCellValue("A" . $rowSecurity, $security->code);
            $sheetSecurity->setCellValue("B" . $rowSecurity, $security->salespoint->name);
            $sheetSecurity->setCellValue("C" . $rowSecurity, $security->created_at->translatedFormat('d F Y'));
            $sheetSecurity->setCellValue("D" . $rowSecurity, $security->created_by_employee->name);
            $sheetSecurity->setCellValue("E" . $rowSecurity, $security->type());
            $sheetSecurity->setCellValue("F" . $rowSecurity, Carbon::parse($security->requirement_date)->translatedFormat('d F Y'));

            if ($security->status > 3) {
                $sheetSecurity->setCellValue("G" . $rowSecurity, 'Done');
            } else {
                $sheetSecurity->setCellValue("G" . $rowSecurity, 'Undone');
            }
            if ($security->status > 3 && $security->has_pr_sap()) {
                $sheetSecurity->setCellValue("H" . $rowSecurity, 'Done');
            } else {
                $sheetSecurity->setCellValue("H" . $rowSecurity, 'Undone');
            }

            if ($security->status > 3) {
                if ($security->po) {
                    foreach ($security->po as $tpo) {
                        if ($tpo->status == 3) {
                            $sheetSecurity->setCellValue("I" . $rowSecurity, 'Done');
                        } else {
                            $sheetSecurity->setCellValue("I" . $rowSecurity, 'Undone');
                        }
                    }
                } else {
                    $sheetSecurity->setCellValue("I" . $rowSecurity, 'Undone');
                }
            } else {
                $sheetSecurity->setCellValue("I" . $rowSecurity, 'Undone');
            }

            if ($security->status > 0) {
                if ($security->evaluasi_form) {
                    foreach ($security->evaluasi_form as $sform) {
                        if ($sform->status == 1) {
                            $sheetSecurity->setCellValue("J" . $rowSecurity, 'Done');
                        } elseif ($sform->status == 0) {
                            $sheetSecurity->setCellValue("J" . $rowSecurity, 'Undone');
                        }
                    }
                } else {
                    $sheetSecurity->setCellValue("J" . $rowSecurity, 'Undone');
                }
            } else {
                $sheetSecurity->setCellValue("J" . $rowSecurity, 'Undone');
            }

            if ($security->status > 4 && $security->ticketing_type == 4) {
                if ($security->lpb_path != null) {
                    $sheetSecurity->setCellValue("K" . $rowSecurity, 'Done');
                } else {
                    $sheetSecurity->setCellValue("K" . $rowSecurity, 'Undone');
                }
            } else {
                $sheetSecurity->setCellValue("K" . $rowSecurity, 'Tidak Ada');
            }
            $rowSecurity++;
        }

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Report_Ticketing.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function reportMonitoring(Request $request)
    {
        $spreadsheet           = \PhpOffice\PhpSpreadsheet\IOFactory::load("template/report_monitoring_template.xlsx");
        $sheetArmada           = $spreadsheet->getSheetByName('Monitoring PO Armada');
        $sheetSecurity         = $spreadsheet->getSheetByName('Monitoring PO Security');
        $sheetCit              = $spreadsheet->getSheetByName('Monitoring PO CIT');
        $sheetPest             = $spreadsheet->getSheetByName('Monitoring PO Pest Control');
        $sheetMerchan          = $spreadsheet->getSheetByName('Monitoring PO Merchandiser');
        $sheetStatusArmada     = $spreadsheet->getSheetByName('Monitoring Status Armada');
        $sheetStatusSecurity   = $spreadsheet->getSheetByName('Monitoring Status Security');

        #armada PO
        $armada_po =  PO::leftJoin('armada_ticket', 'armada_ticket.id', '=', 'po.armada_ticket_id')
            ->leftJoin('salespoint', 'salespoint.id', '=', 'armada_ticket.salespoint_id')
            ->where('po.armada_ticket_id', '!=', null)
            ->whereIn('po.status', [1, 2, 3])
            ->orderBy('armada_ticket.created_at', 'desc')
            ->where(DB::raw('MONTH(po.start_date)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(po.start_date)'), '=', $request->year_filter)
            ->where(DB::raw('po.end_date'), '<=', $request->end_period_filter)
            ->get();

        $rowArmada = 2;

        foreach ($armada_po as $armada) {
            $sheetArmada->setCellValue("A" . $rowArmada, $armada->armada_ticket->code);
            $sheetArmada->setCellValue("B" . $rowArmada, $armada->no_po_sap);
            $sheetArmada->setCellValue("C" . $rowArmada, $armada->armada_ticket->type());
            $sheetArmada->setCellValue("D" . $rowArmada, Carbon::parse($armada->start_date)->translatedFormat('d F Y'));
            $sheetArmada->setCellValue("E" . $rowArmada, Carbon::parse($armada->end_date)->translatedFormat('d F Y'));
            $sheetArmada->setCellValue("F" . $rowArmada, $armada->vendor_name);

            if ($armada->armada_ticket->mutation_salespoint_id != null) {
                $sheetArmada->setCellValue("G" . $rowArmada, $armada->armada_ticket->mutation_salespoint->name);
            } else if ($armada->armada_ticket->ticketing_type == 2) {
                $sheetArmada->setCellValue("G" . $rowArmada, $armada->armada_ticket->mutasi_form->receiver_salespoint_name);
            } else {
                $sheetArmada->setCellValue("G" . $rowArmada, $armada->armada_ticket->salespoint->name);
            }

            if ($armada->armada_ticket->armada_id != null) {
                $sheetArmada->setCellValue("H" . $rowArmada, $armada->armada_ticket->armada->plate);
            } else {
                $sheetArmada->setCellValue("H" . $rowArmada, $armada->armada_ticket->gs_plate);
            }

            if ($armada->armada_ticket->armada_id != null) {
                $sheetArmada->setCellValue("I" . $rowArmada, $armada->armada_ticket->armada->plate);
            } else {
                $sheetArmada->setCellValue("I" . $rowArmada, $armada->armada_ticket->gt_plate);
            }

            $sheetArmada->setCellValue("J" . $rowArmada, $armada->armada_ticket->armada_type->brand_name . ' ' . $armada->armada_ticket->armada_type->name);

            if ($armada->start_date && $armada->end_date) {
                if ($armada->armada_ticket->current_ticketing) {
                    $sheetArmada->setCellValue("K" . $rowArmada, $armada->armada_ticket->current_ticketing->type() . ' ' . $armada->armada_ticket->current_ticketing->code);
                } else {
                    $sheetArmada->setCellValue("K" . $rowArmada, 'Belum di proses');
                }
            } else {
                $sheetArmada->setCellValue("K" . $rowArmada, '-');
            }

            $sheetArmada->setCellValue("L" . $rowArmada, $armada->armada_ticket->status());

            $rowArmada++;
        }

        #armada status
        $armada_status =  ArmadaTicket::where('armada_ticket.status', '!=', -1)
            ->orderBy('armada_ticket.status', 'desc')
            ->where(DB::raw('MONTH(armada_ticket.created_at)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(armada_ticket.created_at)'), '=', $request->year_filter)
            ->get();

        $rowArmadaStatus = 2;

        foreach ($armada_status as $armada) {
            $sheetStatusArmada->setCellValue("A" . $rowArmadaStatus, $armada->code);
            $sheetStatusArmada->setCellValue("B" . $rowArmadaStatus, $armada->salespoint->name);
            $sheetStatusArmada->setCellValue("C" . $rowArmadaStatus, $armada->created_at->translatedFormat('d F Y'));
            $sheetStatusArmada->setCellValue("D" . $rowArmadaStatus, $armada->created_at->diffForHumans(now()));
            $sheetStatusArmada->setCellValue("E" . $rowArmadaStatus, $armada->status());

            $rowArmadaStatus++;
        }

        #security po
        $security_po =  PO::leftJoin('security_ticket', 'security_ticket.id', '=', 'po.security_ticket_id')
            ->leftJoin('salespoint', 'salespoint.id', '=', 'security_ticket.salespoint_id')
            ->where('po.security_ticket_id', '!=', null)
            ->whereIn('po.status', [1, 2, 3])
            ->where(DB::raw('MONTH(po.start_date)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(po.start_date)'), '=', $request->year_filter)
            ->orderBy('security_ticket.created_at', 'desc')
            ->get();

        $rowSecurity = 2;

        foreach ($security_po as $security) {
            $sheetSecurity->setCellValue("A" . $rowSecurity, $security->security_ticket->code);
            $sheetSecurity->setCellValue("B" . $rowSecurity, $security->no_po_sap);
            $sheetSecurity->setCellValue("C" . $rowSecurity, $security->security_ticket->type());
            $sheetSecurity->setCellValue("D" . $rowSecurity, Carbon::parse($security->start_date)->translatedFormat('d F Y'));
            $sheetSecurity->setCellValue("E" . $rowSecurity, Carbon::parse($security->end_date)->translatedFormat('d F Y'));
            $sheetSecurity->setCellValue("F" . $rowSecurity, $security->vendor_name);
            $sheetSecurity->setCellValue("G" . $rowSecurity, $security->salespoint_name);

            if ($security->start_date && $security->end_date) {
                if ($security->security_ticket->current_ticketing) {
                    $sheetSecurity->setCellValue("H" . $rowSecurity, $security->security_ticket->current_ticketing->type() . ' ' .
                        $security->security_ticket->current_ticketing->code);
                } else {
                    $sheetSecurity->setCellValue("H" . $rowSecurity, 'Belum di proses');
                }
            } else {
                $sheetSecurity->setCellValue("H" . $rowSecurity, '-');
            }

            $sheetSecurity->setCellValue("I" . $rowSecurity, $security->security_ticket->status());

            $rowSecurity++;
        }

        #security status

        $security_status =  SecurityTicket::where('security_ticket.status', '!=', -1)
            ->where(DB::raw('MONTH(security_ticket.created_at)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(security_ticket.created_at)'), '=', $request->year_filter)
            ->orderBy('security_ticket.created_at', 'desc')
            ->get();

        $rowSecurityStatus = 2;

        foreach ($security_status as $security) {
            $sheetStatusSecurity->setCellValue("A" . $rowSecurityStatus, $security->code);
            $sheetStatusSecurity->setCellValue("B" . $rowSecurityStatus, $security->salespoint->name);
            $sheetStatusSecurity->setCellValue("C" . $rowSecurityStatus, $security->created_at->translatedFormat('d F Y'));
            $sheetStatusSecurity->setCellValue("D" . $rowSecurityStatus, $security->created_at->diffForHumans(now()));
            $sheetStatusSecurity->setCellValue("E" . $rowSecurityStatus, $security->status());

            $rowSecurityStatus++;
        }

        #CIT
        $cit_po =  PO::leftJoin('ticket', 'ticket.id', '=', 'po.ticket_id')
            ->leftJoin('salespoint', 'salespoint.id', '=', 'ticket.salespoint_id')
            ->leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('po.ticket_id', '!=', null)
            ->where('ticket_item.name', 'LIKE', "CIT" . '%')
            ->whereIn('po.status', [1, 2, 3])
            ->where(DB::raw('MONTH(po.start_date)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(po.start_date)'), '=', $request->year_filter)
            ->orderBy('ticket.created_at', 'desc')
            ->get();

        $rowCit = 2;

        foreach ($cit_po as $cit) {
            $sheetCit->setCellValue("A" . $rowCit, $cit->ticket->code);
            $sheetCit->setCellValue("B" . $rowCit, $cit->no_po_sap);
            $sheetCit->setCellValue("C" . $rowCit, $cit->ticket->request_type());
            $sheetCit->setCellValue("D" . $rowCit, Carbon::parse($cit->start_date)->translatedFormat('d F Y'));
            $sheetCit->setCellValue("E" . $rowCit, Carbon::parse($cit->end_date)->translatedFormat('d F Y'));
            $sheetCit->setCellValue("F" . $rowCit, $cit->send_name);
            $sheetCit->setCellValue("G" . $rowCit, $cit->ticket->salespoint->name);

            if ($cit->start_date && $cit->end_date) {
                if ($cit->ticket->current_ticketing) {
                    $sheetCit->setCellValue("H" . $rowCit, $cit->ticket->current_ticketing->type() . ' ' .
                        $cit->ticket->current_ticketing->code);
                } else {
                    $sheetCit->setCellValue("H" . $rowCit, 'Belum di proses');
                }
            } else {
                $sheetCit->setCellValue("H" . $rowCit, '-');
            }

            $sheetCit->setCellValue("I" . $rowCit, $cit->ticket->status());

            $rowCit++;
        }

        #Pest Control
        $pest_po =  PO::leftJoin('ticket', 'ticket.id', '=', 'po.ticket_id')
            ->leftJoin('salespoint', 'salespoint.id', '=', 'ticket.salespoint_id')
            ->leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('po.ticket_id', '!=', null)
            ->where('ticket_item.name', 'LIKE', "Pest Control" . '%')
            ->whereIn('po.status', [1, 2, 3])
            ->where(DB::raw('MONTH(po.start_date)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(po.start_date)'), '=', $request->year_filter)
            ->orderBy('ticket.created_at', 'desc')
            ->get();

        $rowPest = 2;

        foreach ($pest_po as $pest) {
            $sheetPest->setCellValue("A" . $rowPest, $pest->ticket->code);
            $sheetPest->setCellValue("B" . $rowPest, $pest->no_po_sap);
            $sheetPest->setCellValue("C" . $rowPest, $pest->ticket->request_type());
            $sheetPest->setCellValue("D" . $rowPest, Carbon::parse($pest->start_date)->translatedFormat('d F Y'));
            $sheetPest->setCellValue("E" . $rowPest, Carbon::parse($pest->end_date)->translatedFormat('d F Y'));
            $sheetPest->setCellValue("F" . $rowPest, $pest->send_name);
            $sheetPest->setCellValue("G" . $rowPest, $pest->ticket->salespoint->name);

            if ($pest->start_date && $pest->end_date) {
                if ($pest->ticket->current_ticketing) {
                    $sheetPest->setCellValue("H" . $rowPest, $pest->ticket->current_ticketing->type() . ' ' .
                        $pest->ticket->current_ticketing->code);
                } else {
                    $sheetPest->setCellValue("H" . $rowPest, 'Belum di proses');
                }
            } else {
                $sheetPest->setCellValue("H" . $rowPest, '-');
            }

            $sheetPest->setCellValue("I" . $rowPest, $pest->ticket->status());

            $rowPest++;
        }

        #Merchandiser
        $merchandiser_po =  PO::leftJoin('ticket', 'ticket.id', '=', 'po.ticket_id')
            ->leftJoin('salespoint', 'salespoint.id', '=', 'ticket.salespoint_id')
            ->leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('po.ticket_id', '!=', null)
            ->where('ticket_item.name', 'LIKE', "Pest Control" . '%')
            ->whereIn('po.status', [1, 2, 3])
            ->where(DB::raw('MONTH(po.start_date)'), '=', $request->month_filter)
            ->where(DB::raw('YEAR(po.start_date)'), '=', $request->year_filter)
            ->orderBy('ticket.created_at', 'desc')
            ->get();

        $rowMerchandiser = 2;

        foreach ($merchandiser_po as $merchandiser) {
            $sheetMerchan->setCellValue("A" . $rowMerchandiser, $merchandiser->ticket->code);
            $sheetMerchan->setCellValue("B" . $rowMerchandiser, $merchandiser->no_po_sap);
            $sheetMerchan->setCellValue("C" . $rowMerchandiser, $merchandiser->ticket->request_type());
            $sheetMerchan->setCellValue("D" . $rowMerchandiser, Carbon::parse($merchandiser->start_date)->translatedFormat('d F Y'));
            $sheetMerchan->setCellValue("E" . $rowMerchandiser, Carbon::parse($merchandiser->end_date)->translatedFormat('d F Y'));
            $sheetMerchan->setCellValue("F" . $rowMerchandiser, $merchandiser->send_name);
            $sheetMerchan->setCellValue("G" . $rowMerchandiser, $merchandiser->ticket->salespoint->name);

            if ($merchandiser->start_date && $merchandiser->end_date) {
                if ($merchandiser->ticket->current_ticketing) {
                    $sheetMerchan->setCellValue("H" . $rowMerchandiser, $merchandiser->ticket->current_ticketing->type() . ' ' .
                        $merchandiser->ticket->current_ticketing->code);
                } else {
                    $sheetMerchan->setCellValue("H" . $rowMerchandiser, 'Belum di proses');
                }
            } else {
                $sheetMerchan->setCellValue("H" . $rowMerchandiser, '-');
            }

            $sheetMerchan->setCellValue("I" . $rowMerchandiser, $merchandiser->ticket->status());

            $rowMerchandiser++;
        }

        $writer = new Xlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        ob_end_clean();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Report_Monitoring.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}
