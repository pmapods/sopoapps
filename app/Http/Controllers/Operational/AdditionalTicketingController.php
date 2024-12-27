<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SalesPoint;
use App\Models\Authorization;
use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Models\TicketItem;
use App\Models\TicketVendor;
use App\Models\TicketAuthorization;
use App\Models\PoManual;
use App\Models\Vendor;
use App\Models\BudgetUpload;
use App\Models\CancelAuthorization;

use Auth;
use DB;
use Carbon\Carbon;

class AdditionalTicketingController extends Controller
{
    public function createAdditionalTicket(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->id);
            $isnew = true;
            if ($ticket == null) {
                $ticket = new Ticket;
            } else {
                $isnew = false;
            }

            //validasi ticket blocking
            if (
                $request->ticket_type == 'CIT' && $request->request_type == 3 ||
                $request->ticket_type == 'CIT' && $request->request_type == 1 ||
                $request->ticket_type == 'CIT' && $request->request_type == 4
            ) {
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "cit",
                    'block_type' => "block_day",
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }

            if (
                $request->ticket_type == 'PEST CONTROL' && $request->request_type == 3 ||
                $request->ticket_type == 'PEST CONTROL' && $request->request_type == 1 ||
                $request->ticket_type == 'PEST CONTROL' && $request->request_type == 4
            ) {
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "pest_control",
                    'block_type' => "block_day",
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }

            if (
                $request->ticket_type == 'MERCHANDISER' && $request->request_type == 3 ||
                $request->ticket_type == 'MERCHANDISER' && $request->request_type == 1 ||
                $request->ticket_type == 'MERCHANDISER' && $request->request_type == 4
            ) {
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "merchendiser",
                    'block_type' => "block_day",
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
                // End validasi ticket blocking
            }

            // VALIDATE IF CIT AND TICKETING TYPE BUDGET
            $budget_upload_id = null;
            if ($request->ticket_type == "cit" && $request->budget_type == 0) {
                if ($request->months < 1) {
                    return back()->with('error', 'Jumlah bulan untuk pengadaan cit minimal 1');
                }
                if ($request->expense < 1000) {
                    return back()->with('error', 'Jumlah nilai Expense untuk pengadaan cit minimal Rp 1.000');
                }
                // validate personil count with security budget on assumption
                $budget = BudgetUpload::where('salespoint_id', $request->salespoint_id)
                    ->where('status', 1)
                    ->where('type', 'assumption')
                    ->where('year', '=', 2023)
                    ->first();
                $budget_upload_id = $budget->id;

                if ($budget == null) {
                    return back()->with('error', 'Budget belum tersedia. harap melakukan request budget terlebih dahulu');
                } else {
                    $cit_budget = $budget->budget_detail->where('code', 'CIT')->first();
                    if ($cit_budget == null) {
                        return back()->with('error', 'Budget untuk pengadaan CIT belum tersedia. harap melakukan request budget terlebih dahulu');
                    } else {
                        $maxrequest = $cit_budget->qty - $cit_budget->pending_quota - $cit_budget->used_quota;
                        if ($maxrequest < $request->months) {
                            return back()->with('error', 'Jumlah maksimal bulan yang dapat di request adalah ' . $maxrequest . '. Jumlah bulan yang di input ' . $request->months . '(' . $budget->code . ')');
                        }
                        $maxvalue = $cit_budget->value;
                        if ($maxvalue < $request->expense) {
                            return back()->with('error', 'Jumlah maksimal nilai expense yang dapat di request adalah ' . $maxvalue . '. Jumlah nilai yang di input ' . $request->expense . '(' . $budget->code . ')');
                        }
                    }
                }
            }

            $ticket->budget_upload_id = $budget_upload_id;
            $ticket->po_reference_number = $request->po_number ?? null;
            $ticket->requirement_date   = $request->requirement_date;
            $ticket->salespoint_id      = $request->salespoint_id;
            $ticket->authorization_id   = $request->authorization_id;
            // item type default jasa
            $ticket->item_type          = 1;
            $ticket->request_type       = $request->request_type;
            $ticket->budget_type        = $request->budget_type;
            $ticket->reason             = "";
            $ticket->created_by         = Auth::user()->id;
            $ticket->save();
            if ($ticket->code == null) {
                // CIT PEST MERCHANDISER MASUK JASA
                $code_type = 'P02';
                // ambil kode inisial salespoint
                $code_salespoint_initial = strtoupper($ticket->salespoint->initial);

                // ambil jumlah urutan ticketing d terkait dalam bulan dan tahun ini
                $armada_ticket_count = ArmadaTicket::whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ])
                    ->withTrashed()
                    ->count();

                $security_ticket_count = SecurityTicket::whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ])
                    ->withTrashed()
                    ->count();

                $barang_ticket_count = Ticket::whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ])
                    ->where('status', '>', 0)
                    ->withTrashed()
                    ->count();

                $code_total_count = $armada_ticket_count + $security_ticket_count + $barang_ticket_count;
                do {
                    $code = $code_type . "-" . $code_salespoint_initial . "-" . now()->translatedFormat('dmy') . str_repeat("0", 4 - strlen($code_total_count + 1)) . ($code_total_count + 1);
                    $code_total_count++;
                    $checkbarang = Ticket::where('code', $code)->first();
                    $checkarmada = ArmadaTicket::where('code', $code)->first();
                    $checksecurity = SecurityTicket::where('code', $code)->first();
                    ($checkbarang != null || $checkarmada != null) ? $flag = false : $flag = true;
                } while (!$flag);
                $ticket->code           = $code;
                $ticket->save();
            }

            if ($request->request_type == 5 || $request->request_type == 6) {
                $salespointname = str_replace(' ', '_', $ticket->salespoint->name);
                $ba_upload_ext  = pathinfo($request->file('upload_ba')->getClientOriginalName(), PATHINFO_EXTENSION);
                $name = "BA_Additional_" . $salespointname . '.' . $ba_upload_ext;
                $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/' . $name;
                $file = pathinfo($path);
                $path = $request->file('upload_ba')->storeAs($file['dirname'], $file['basename'], 'public');
                $ticket->ba_additional_ticketing = $path;
                $ticket->save();
            } else {
                $ticket->ba_additional_ticketing = null;
                $ticket->save();
            }

            $salespoint = $ticket->salespoint;

            switch ($request->ticket_type) {
                case 'CIT':
                    $ticket_item = 'CIT';
                    $qty = $request->months;
                    $value = $request->expense;
                    break;
                case 'PEST CONTROL':
                    $ticket_item = 'Pest Control';
                    $qty = 1;
                    $value = 0;
                    break;
                case 'MERCHANDISER':
                    $ticket_item = 'Merchandiser';
                    $qty = 1;
                    $value = 0;
                    break;

                default:
                    return back()->with('error', 'Tipe item tidak ditemukan');
                    break;
            }
            // tambahin ke ticket item default nama itemnya
            $newTicketItem                        = new TicketItem;
            $newTicketItem->ticket_id             = $ticket->id;
            $newTicketItem->budget_pricing_id     = -1;
            if ($request->po_number) {
                $newTicketItem->name                  = $ticket_item . " (" . $ticket->request_type() . " " . $request->po_number . ")";
            } else {
                $newTicketItem->name                  = $ticket_item;
            }
            $newTicketItem->brand                 = "";
            $newTicketItem->type                  = "";
            $newTicketItem->price                 = $value;
            $newTicketItem->count                 = $qty;
            $newTicketItem->save();

            if ($request->request_type == 0) {
                // Pengadaan Baru ubah status ke draft untuk mengisi vendor, attachment, alasan pengadaan
                $authorizations = Authorization::find($request->authorization_id);
                foreach ($authorizations->authorization_detail as $detail) {
                    $newTicketAuthorization                     = new TicketAuthorization;
                    $newTicketAuthorization->ticket_id          = $ticket->id;
                    $newTicketAuthorization->employee_id        = $detail->employee_id;
                    $newTicketAuthorization->employee_name      = $detail->employee->name;
                    $newTicketAuthorization->as                 = $detail->sign_as;
                    $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                    $newTicketAuthorization->level              = $detail->level;
                    $newTicketAuthorization->save();
                }
                $ticket->reason = "Pengadaan Baru";
                $ticket->status = 1;
                $ticket->save();
                DB::commit();
                return redirect('/ticketing')->with('success', 'Berhasil menambah pengadaan baru. Silahkan Melakukan Otorisasi');
            } else if ($request->request_type == 1 || $request->request_type == 5) {
                // Pengadaan Replace / Percepatan Replace
                $authorizations = Authorization::find($request->authorization_id);
                foreach ($authorizations->authorization_detail as $detail) {
                    $newTicketAuthorization                     = new TicketAuthorization;
                    $newTicketAuthorization->ticket_id          = $ticket->id;
                    $newTicketAuthorization->employee_id        = $detail->employee_id;
                    $newTicketAuthorization->employee_name      = $detail->employee->name;
                    $newTicketAuthorization->as                 = $detail->sign_as;
                    $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                    $newTicketAuthorization->level              = $detail->level;
                    $newTicketAuthorization->save();
                }

                if ($request->request_type == 1) {
                    $ticket->reason = "Pengadaan Replace " . $request->po_number;
                } else {
                    $ticket->reason = "Percepatan Pengadaan Replace " . $request->po_number;
                }

                $ticket->status = 1;
                $ticket->save();

                $po = $ticket->po_reference;
                $pomanual = PoManual::where('po_number', $ticket->po_reference_number)->first();

                DB::commit();
                return redirect('/ticketing')->with('success', 'Berhasil menambah pengadaan replace. Silahkan melakukan otorisasi');
            } else if ($request->request_type == 3) {
                $authorizations = Authorization::find($request->authorization_id);
                foreach ($authorizations->authorization_detail as $detail) {
                    $newTicketAuthorization                     = new TicketAuthorization;
                    $newTicketAuthorization->ticket_id          = $ticket->id;
                    $newTicketAuthorization->employee_id        = $detail->employee_id;
                    $newTicketAuthorization->employee_name      = $detail->employee->name;
                    $newTicketAuthorization->as                 = $detail->sign_as;
                    $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                    $newTicketAuthorization->level              = $detail->level;
                    $newTicketAuthorization->save();
                }
                $ticket->reason = "Perpanjangan PO " . $request->po_number;
                $ticket->status = 1;
                $ticket->save();

                $po = $ticket->po_reference;
                $pomanual = PoManual::where('po_number', $ticket->po_reference_number)->first();

                $newTicketVendor = new TicketVendor;
                $newTicketVendor->ticket_id         = $ticket->id;
                $newTicketVendor->vendor_id         = null;
                $newTicketVendor->name              = $po->sender_name ?? $pomanual->vendor_name;
                $newTicketVendor->salesperson       = "";
                $newTicketVendor->phone             = "";
                $newTicketVendor->type              = 0;
                $newTicketVendor->save();
                DB::commit();
                return redirect('/ticketing')->with('success', 'Berhasil menambah pengadaan perpanjangan. Silahkan melakukan otorisasi');
            } else if ($request->request_type == 4 || $request->request_type == 6) {
                // END KONTRAK ubah ticket ke status selesai dan ubah status po menjadi closed
                $authorizations = Authorization::find($request->authorization_id);
                foreach ($authorizations->authorization_detail as $detail) {
                    $newTicketAuthorization                     = new TicketAuthorization;
                    $newTicketAuthorization->ticket_id          = $ticket->id;
                    $newTicketAuthorization->employee_id        = $detail->employee_id;
                    $newTicketAuthorization->employee_name      = $detail->employee->name;
                    $newTicketAuthorization->as                 = $detail->sign_as;
                    $newTicketAuthorization->employee_position  = $detail->employee_position->name;
                    $newTicketAuthorization->level              = $detail->level;
                    $newTicketAuthorization->save();
                }

                if ($request->request_type == 4) {
                    $ticket->reason = "End Kontrak PEST Control PO " . $request->po_number;
                } else {
                    $ticket->reason = "Perpanjangan End Kontrak PEST Control PO " . $request->po_number;
                }

                $ticket->status = 1;
                $ticket->save();

                $po = $ticket->po_reference;
                $pomanual = PoManual::where('po_number', $ticket->po_reference_number)->first();
                // if ($po) {
                //     $po->status = 4;
                //     $po->save();
                // } else if ($pomanual) {
                //     $pomanual->status = 4;
                //     $pomanual->save();
                // } else {
                // }

                $newTicketVendor = new TicketVendor;
                $newTicketVendor->ticket_id         = $ticket->id;
                $newTicketVendor->vendor_id         = null;
                $newTicketVendor->name              = $po->sender_name ?? $pomanual->vendor_name;
                $newTicketVendor->salesperson       = "";
                $newTicketVendor->phone             = "";
                $newTicketVendor->type              = 0;
                $newTicketVendor->save();

                DB::commit();
                return redirect('/ticketing')->with('success', 'Berhasil melakukan End Kontrak ' . $ticket->code .'. Silahkan Melakukan Otorisasi');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat tiket. Harap mencoba kembali.' . ' "' . $ex->getMessage() . '"');
        }
    }

    public function getAuthorization(Request $request)
    {
        $salespoint_id = $request->salespoint_id;
        $form_type = $request->form_type;
        
        if ($request->notes != '' || $request->notes != null) {
            $notes = $request->notes;
        }
        else {
            $notes = $request->notes_select;
        }

        $authorizations = Authorization::where('salespoint_id', $salespoint_id)
            ->where('form_type', $form_type)
            ->where(function($query) use ($notes) {
                    $query->where('notes', 'LIKE', '%' . $notes . '%');
                        // ->orWhereNull('notes')
                        // ->orWhere('notes', '=', '');
                }
            )
            ->get();

        foreach ($authorizations as $authorization) {
            $authorization->list = $authorization->authorization_detail;
            foreach ($authorization->list as $item) {
                $item->employee_name = $item->employee->name;
            }
        }

        return response()->json([
            'data' => $authorizations,
        ]);
    }

    public function cancelEndKontrakPEST(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($id);

            // Get authorization end kontrak
            $authorizations = Authorization::find($request->authorization_id);
                foreach ($authorizations->authorization_detail as $detail) {
                    $newCancelAuthorization                     = new CancelAuthorization;
                    $newCancelAuthorization->ticket_id          = $ticket->id;
                    $newCancelAuthorization->employee_id        = $detail->employee_id;
                    $newCancelAuthorization->employee_name      = $detail->employee->name;
                    $newCancelAuthorization->as                 = $detail->sign_as;
                    $newCancelAuthorization->employee_position  = $detail->employee_position->name;
                    $newCancelAuthorization->level              = $detail->level;
                    $newCancelAuthorization->save();
                }
                
            $ticket->is_cancel_end = 1;
            $ticket->cancel_end_reason = $request->reason;
            $ticket->cancel_end_by = Auth::user()->id;
            $ticket->cancel_end_at = now();
            $ticket->cancel_end_reason = $request->reason;
            $ticket->status = 1;

            $ticket->save();

            DB::commit();
            return redirect('/ticketing')->with('success', 'Berhasil melakukan Cancel ' . $ticket->reason . ' Dengan nomor tiket ' . $ticket->code . '. Silahkan Melakukan Otorisasi');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal cancel end kontrak. Harap mencoba kembali.' . ' "' . $ex->getMessage() . '"');
        }     

    }
}
