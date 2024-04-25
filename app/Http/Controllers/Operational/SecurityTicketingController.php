<?php

namespace App\Http\Controllers\Operational;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ArmadaTicket;
use App\Models\TicketingBlockOpenRequest;
use App\Models\SecurityTicket;
use App\Models\SecurityTicketMonitoring;
use App\Models\Ticket;
use App\Models\Authorization;
use App\Models\SecurityTicketAuthorization;
use App\Models\EmployeePosition;
use App\Models\EmployeeLocationAccess;
use App\Models\SalesPoint;
use App\Models\EvaluasiForm;
use App\Models\EvaluasiFormAuthorization;
use App\Models\Po;
use App\Models\PoManual;
use App\Models\BudgetUpload;

use DB;
use Carbon\Carbon;
use Auth;
use PDF;
use Storage;
use Mail;
use App\Mail\NotificationMail;

class SecurityTicketingController extends Controller
{
    public function createSecurityTicket(Request $request)
    {
        try {
            //validasi ticket blocking
            if (
                $request->ticketing_type == 1 ||
                $request->ticketing_type == 2 ||
                $request->ticketing_type == 3
            ) {
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "security",
                    'block_type' => "block_day",
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }

            DB::beginTransaction();
            // SECURITY TERMASUK JASA
            $code_type = 'P02';

            // ambil kode inisial salespoint
            $salespoint = SalesPoint::find($request->salespoint_id);
            $code_salespoint_initial = strtoupper($salespoint->initial);

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

            // validasi budget jika pengadaan
            $budget_upload_id = null;
            $personil_count = null;
            if ($request->ticketing_type == 0) {
                if ($request->personil_count < 1) {
                    return back()->with('error', 'Jumlah personil untuk pengadaan security minimal 1');
                }
                // validate personil count with security budget on assumption
                $budget = BudgetUpload::where('salespoint_id', $request->salespoint_id)
                    ->where('status', 1)
                    ->where('type', 'assumption')
                    ->where('year', '=', Carbon::now()->year)
                    ->first();
                $budget_upload_id = $budget->id;
                if ($budget == null) {
                    return back()->with('error', 'Budget belum tersedia. harap melakukan request budget terlebih dahulu');
                } else {
                    $security_budget = $budget->budget_detail->where('code', 'SCRT')->first();
                    if ($security_budget == null) {
                        return back()->with('error', 'Budget belum tersedia. harap melakukan request budget terlebih dahulu');
                    } else {
                        $maxrequest = $security_budget->qty - $security_budget->pending_quota - $security_budget->used_quota;
                        if ($maxrequest < $request->personil_count) {
                            return back()->with('error', 'Jumlah maksimal personil yang dapat di request adalah ' . $maxrequest . '. Jumlah Personil yang diminta ' . $request->personil_count);
                        }
                    }
                }
                $personil_count = $request->personil_count;
            }
            $newTicket                               = new SecurityTicket;
            $newTicket->code                         = $code;
            $newTicket->budget_upload_id             = $budget_upload_id;
            $newTicket->salespoint_id                = $request->salespoint_id;
            $newTicket->ticketing_type               = $request->ticketing_type;
            if (in_array($request->ticketing_type, [1, 2, 3, 5, 6])) {
                // cek apakah nomor po tersedia di po manual ataupun po pods
                $po = Po::where('no_po_sap', $request->po_number)->first();
                $pomanual = PoManual::where('po_number', $request->po_number)->first();
                if (!$po && !$pomanual) {
                    throw new \Exception('Nomor PO ' . $request->po_number . ' tidak ditemukan. harap mencoba dengan nomor PO lainnya.');
                }
                // jika perpanjangan, validasi apakah po terkait end date nya di bulan berjalan
                if ($request->ticketing_type == 1) {
                    if ($po && $po->end_date != null) {
                        $po_end_date = Carbon::parse($po->end_date);
                        $po_available_date = $po_end_date->subDays(60);
                        if (now() < $po_available_date) {
                            return back()->with('error', "PO dengan nomor: " . $po->no_po_sap . " hanya dapat diperpanjang di H-60 dari tanggal Akhir PO. (PO End Date: " . $po_end_date->translatedFormat('d M Y') . ")");
                        }
                    }
                    if ($pomanual && $pomanual->end_date != null) {
                        $po_end_date = Carbon::parse($pomanual->end_date);
                        $po_available_date = $po_end_date->subDays(60);
                        if (now() < $po_available_date) {
                            return back()->with('error', "PO dengan nomor: " . $pomanual->po_number . " hanya dapat diperpanjang di H-60 dari tanggal akhir PO. (PO End Date: " . $po_end_date->translatedFormat('d M Y') . ")");
                        }
                    }
                }
                $newTicket->po_reference_number      = $request->po_number;
            }
            if ($request->ticketing_type == 4) {
                $newTicket->reason               = $request->reason;
            }
            $newTicket->requirement_date             = $request->requirement_date;
            $newTicket->personil_count               = ($personil_count != null) ? intval($personil_count) : null;
            $newTicket->created_by                   = Auth::user()->id;
            $newTicket->save();

            if ($request->ticketing_type == 5 || $request->ticketing_type == 6) {
                $salespointname = str_replace(' ', '_', $newTicket->salespoint->name);
                $ba_upload_ext  = pathinfo($request->file('upload_ba')->getClientOriginalName(), PATHINFO_EXTENSION);
                $name = "BA_Upload_" . $salespointname . '.' . $ba_upload_ext;
                $path = "/attachments/ticketing/security/" . $newTicket->code . '/' . $name;
                $file = pathinfo($path);
                $path = $request->file('upload_ba')->storeAs($file['dirname'], $file['basename'], 'public');
                $newTicket->ba_upload_security_ticket = $path;
                $newTicket->save();
            } else {
                $newTicket->ba_upload_security_ticket = null;
                $newTicket->save();
            }

            if (in_array($request->ticketing_type, [0, 4])) {
                $newTicket->save();
                $authorization = Authorization::findOrFail($request->authorization_id);
                if ($request->ticketing_type == 4) {
                    // jika pengadaan lembur, pastikan di notes matriks "Pengadaan Lembur"
                    if (strtolower(trim($authorization->notes)) != "pengadaan lembur") {
                        throw new \Exception("Otorisasi yang dipilih bukan untuk pengadaan lembur");
                    }
                }
                foreach ($authorization->authorization_detail as $key => $authorization) {
                    $newAuthorization                    = new SecurityTicketAuthorization;
                    $newAuthorization->security_ticket_id  = $newTicket->id;
                    $newAuthorization->employee_id       = $authorization->employee_id;
                    $newAuthorization->employee_name     = $authorization->employee->name;
                    $newAuthorization->as                = $authorization->sign_as;
                    $newAuthorization->employee_position = $authorization->employee_position->name;
                    $newAuthorization->level             = $key + 1;
                    $newAuthorization->save();
                }
            }
            DB::commit();
            return redirect('/ticketing?menu=Security')->with('success', 'Berhasil membuat ticketing security');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat ticketing security (' . $ex->getMessage() . $ex->getLine() . ')');
        }
    }

    public function securityTicketDetail(Request $request, $code)
    {
        $securityticket = SecurityTicket::where('code', $code)->first();

        $employee_positions = EmployeePosition::all();

        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');

        // validate detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($securityticket->salespoint_id)) {
            return redirect('/inventorybudget')->with('error', 'Anda tidak memiliki akses untuk tiket berikut. Tidak memiliki akses salespoint "' . $securityticket->salespoint->name . '"');
        }

        $evaluasiform_authorizations = Authorization::whereIn('salespoint_id', $securityticket->salespoint->salespoint_id_list())->where('form_type', 9)->get();

        $salespoints = SalesPoint::all();
        $po = Po::where('no_po_sap', $securityticket->po_reference_number)->first();
        $pomanual = PoManual::where('po_number', $securityticket->po_reference_number)->first();
        try {
            if (!$securityticket) {
                throw new \Exception('Ticket security dengan kode ' . $code . 'tidak ditemukan');
            }
            return view('Operational.Security.securityticketdetail', compact('securityticket', 'employee_positions', 'salespoints', 'evaluasiform_authorizations', 'po', 'pomanual'));
        } catch (\Exception $ex) {
            return redirect('/ticketing?menu=Security')->with('error', 'Gagal membukan detail ticket security ' . $ex->getMessage());
        }
    }

    public function startSecurityAuthorization(Request $request)
    {
        try {
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);
            if ($securityticket->updated_at != $request->updated_at) {
                return back()->with('error', 'Data terbaru sudah diupdate. Silahkan coba kembali');
            } else {
                $securityticket->requirement_date = $request->requirement_date;
                $securityticket->status = 1;
                $securityticket->save();

                foreach ($securityticket->authorizations as $authorization) {
                    $authorization->status = 0;
                    $authorization->reject_notes = null;
                    $authorization->save();
                }
            }
            return back()->with('success', 'Berhasil memulai otorisasi pengadaan security ' . $securityticket->code . '. Otorisasi selanjutnya oleh ' . $securityticket->current_authorization()->employee_name);
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memulai otorisasi ' . $ex->getMessage());
        }
    }

    public function approveSecurityAuthorization(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);
            $current_authorization = $securityticket->current_authorization();
            if ($current_authorization->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
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

            $current_authorization = $securityticket->current_authorization();
            if ($current_authorization == null) {
                switch ($securityticket->type()) {
                    case 'Pengadaan':
                        $securityticket->status = 2;
                        $message = "Silahkan melanjutkan ke Menu PR.";
                        break;
                    case 'Pengadaan Lembur':
                        $securityticket->status = 4;
                        $message = "Dilanjutkan dengan proses PR dan PO SAP";
                        break;
                }
                $securityticket->save();
                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Otorisasi pengadaan security ' . $securityticket->code . ' telah selesai. ' . $message
                    ]);
                } else {
                    return back()->with('success', 'Otorisasi pengadaan security ' . $securityticket->code . ' telah selesai. ' . $message);
                }
            } else {
                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan approval otorisasi pengadaan security ' . $securityticket->code . '. Otorisasi selanjutnya oleh ' . $securityticket->current_authorization()->employee_name
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan approval otorisasi pengadaan security ' . $securityticket->code . '. Otorisasi selanjutnya oleh ' . $securityticket->current_authorization()->employee_name);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal memulai otorisasi ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal memulai otorisasi ' . $ex->getMessage());
            }
        }
    }

    public function terminateSecurityTicketing(Request $request)
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);
            $isAllowed = false;
            if ($securityticket->status == 0) {
                $isAllowed = true;
            }

            if (Auth::user()->id == 1) {
                $isAllowed = true;
            } elseif (Auth::user()->id == 115) {
                $isAllowed = true;
            } elseif (Auth::user()->id == 116) {
                $isAllowed = true;
            } elseif (Auth::user()->id == 117) {
                $isAllowed = true;
            } elseif (Auth::user()->id == 197) {
                $isAllowed = true;
            } elseif (Auth::user()->id == 717) {
                $isAllowed = true;
            } elseif (Auth::user()->id == 118) {
                $isAllowed = true;
            }

            if (!$isAllowed) {
                throw new \Exception('Anda tidak dapat membatalkan tiket (Request Admin untuk membatalkan tiket');
            }
            $securityticket->terminated_by = Auth::user()->id;
            $securityticket->termination_reason = $request->reason;
            $securityticket->status = -1;
            $securityticket->save();

            $monitor                        = new SecurityTicketMonitoring;
            $monitor->security_ticket_id    = $securityticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membatalkan Pengadaan Security';
            $monitor->save();
            DB::commit();
            return redirect('/ticketing?menu=Security')->with('success', 'Berhasil Membatalkan Pengadaan');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            return redirect('/ticketing?menu=Security')->with('error', 'Gagal Membatalkan Pengadaan "' . $ex->getMessage() . '"');
        }
    }

    public function rejectSecurityAuthorization(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);
            $current_authorization = $securityticket->current_authorization();
            if ($current_authorization->employee_id != Auth::user()->id) {
                return back()->with('error', 'Otorisasi saat ini tidak sesuai dengan akun login.');
            } else {
                $current_authorization->status = -1;
                $current_authorization->reject_notes = $request->reject_notes;
                $current_authorization->save();

                $securityticket->status -= 1;
                $securityticket->save();
                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan reject otorisasi pengadaan security dengan alasan ' . $request->reject_notes
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan reject otorisasi pengadaan security dengan alasan ' . $request->reject_notes);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal memulai otorisasi ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal memulai otorisasi ' . $ex->getMessage());
            }
        }
    }

    public function uploadSecurityBA(Request $request)
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);

            $salespointname = str_replace(' ', '_', $securityticket->salespoint->name);
            $ext = pathinfo($request->file('ba_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BA_" . $salespointname . '.' . $ext;
            $path = "/attachments/ticketing/security/" . $securityticket->code . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('ba_file')->storeAs($file['dirname'], $file['basename'], 'public');
            $securityticket->ba_path = $path;

            $securityticket->status = 4;
            $securityticket->save();

            DB::commit();
            return redirect('/pr')->with('success', 'Berhasil melakukan upload berkas BA. Silahkan melanjutkan ke proses PO.');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan upload berkas LPB ' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function uploadSecurityLPB(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);
            if (in_array($securityticket->type(), ["Pengadaan", "Replace","Pengadaan Lembur"])) {
                $salespointname = str_replace(' ', '_', $securityticket->salespoint->name);
                $ext = pathinfo($request->file('lpb_file')->getClientOriginalName(), PATHINFO_EXTENSION);
                $name = "LPB_" . $salespointname . '.' . $ext;
                $path = "/attachments/ticketing/security/" . $securityticket->code . '/' . $name;
                $file = pathinfo($path);
                $path = $request->file('lpb_file')->storeAs($file['dirname'], $file['basename'], 'public');
                $securityticket->lpb_path = $path;
            }

            if (in_array($securityticket->type(), ["Perpanjangan", "Replace", "End Kontrak", "Percepatan Replace", "Percepatan End Kontrak"])) {
                $po = $securityticket->po_reference;
                $pomanual = PoManual::where('po_number', $securityticket->po_reference_number)->first();
                if ($po) {
                    $po->status = 4;
                    $po->save();
                }
                if ($pomanual) {
                    $pomanual->status = 4;
                    $pomanual->save();
                }
            }

            $securityticket->finished_date = date('Y-m-d');
            $securityticket->status = 6;
            $securityticket->save();

            DB::commit();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil melakukan upload berkas LPB. Pengadaan Selesai.'
                ]);
            } else {
                return back()->with('success', 'Berhasil melakukan upload berkas LPB. Pengadaan Selesai.');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan upload berkas LPB ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan upload berkas LPB ' . $ex->getMessage());
            }
        }
    }

    public function uploadSecurityEndKontrak(Request $request)
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);

            $salespointname = str_replace(' ', '_', $securityticket->salespoint->name);
            $ext = pathinfo($request->file('endkontrak_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "EndSewa_" . $salespointname . '.' . $ext;
            $path = "/attachments/ticketing/security/" . $securityticket->code . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('endkontrak_file')->storeAs($file['dirname'], $file['basename'], 'public');
            $securityticket->endkontrak_path = $path;

            $po = $securityticket->po_reference;
            $pomanual = PoManual::where('po_number', $securityticket->po_reference_number)->first();
            if ($po) {
                $po->status = 4;
                $po->save();
            }
            if ($pomanual) {
                $pomanual->status = 4;
                $pomanual->save();
            }

            $securityticket->finished_date = date('Y-m-d');
            $securityticket->status = 6;
            $securityticket->save();

            DB::commit();
            return back()->with('success', 'Berhasil melakukan upload berkas End Kontrak Security. Pengadaan Selesai.');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            return back()->with('error', 'Gagal melakukan upload berkas End Kontrak Security ' . $ex->getMessage());
        }
    }

    public function noteEvaluasiForm(Request $request)
    {
        // dd($request);
        try {
            DB::beginTransaction();
            $evaluasi_form = EvaluasiForm::findOrFail($request->id);
            $evaluasi_form->notes_form_evaluasi = $request->notes_form_evaluasi;
            $evaluasi_form->save();
            DB::commit();

            return back()->with('success', 'Berhasil membuat notes form evaluasi. Silahkan Approve / Reject Form Evaluasi');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat notes form evaluasi (' . $ex->getMessage() . ')');
        }
    }

    public function addEvaluasiForm(Request $request)
    {
        try {
            DB::beginTransaction();
            $securityticket = SecurityTicket::findOrFail($request->security_ticket_id);

            if ($securityticket->type() == "Perpanjangan") {
                // validasi ticketing block jika perpanjangan
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "security",
                    'block_type' => "block_day",
                    'ticket_code' => $securityticket->code,
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }

            $form                       = new EvaluasiForm;
            $form->security_ticket_id   = $securityticket->id;
            $form->salespoint_id        = $securityticket->salespoint_id;
            $po = $securityticket->po_reference;
            $pomanual = PoManual::where('po_number', $securityticket->po_reference_number)->first();
            $poflag = false;
            if ($po) {
                $form->vendor_name          = $po->sender_name;
                $form->salespoint_name      = $po->security_ticket->salespoint->name;
                $poflag = true;
            }
            if ($pomanual) {
                $form->vendor_name          = $pomanual->vendor_name;
                $form->salespoint_name      = $pomanual->salespoint_name;
                $poflag = true;
            }
            if (!$poflag) {
                throw new \Exception('PO ' . $securityticket->po_reference_number . ' tidak ditemukan.');
            }
            $form->period               = date('Y-m-d');
            $form->personil             = json_encode($request->personil);
            $form->lembaga              = json_encode($request->lembaga);
            $form->kesimpulan           = $request->kesimpulan;
            $form->created_by           = Auth::user()->id;
            $form->save();

            $authorization = Authorization::find($request->authorization_id);
            foreach ($authorization->authorization_detail as $detail) {
                $newAuthorization                     = new EvaluasiFormAuthorization;
                $newAuthorization->evaluasi_form_id   = $form->id;
                $newAuthorization->employee_id        = $detail->employee_id;
                $newAuthorization->employee_name      = $detail->employee->name;
                $newAuthorization->as                 = $detail->sign_as;
                $newAuthorization->employee_position  = $detail->employee_position->name;
                $newAuthorization->level              = $detail->level;
                $newAuthorization->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil membuat form evaluasi. Menunggu Otorisasi oleh ' . $authorization->authorization_detail->first()->employee->name);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat form evaluasi (' . $ex->getMessage() . ')');
        }
    }

    public function approveEvaluasiForm(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $evaluasi_form = EvaluasiForm::findOrFail($request->evaluasi_form_id);

            if ($evaluasi_form->security_ticket->type() == "Perpanjangan") {
                // validasi ticketing block jika perpanjangan
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "security",
                    'block_type' => "block_day",
                    'ticket_code' => $evaluasi_form->security_ticket->code,
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }
            if (($evaluasi_form->current_authorization()->employee_id ?? -1) != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }
            $authorization = $evaluasi_form->current_authorization();
            $authorization->status = 1;
            $authorization->save();

            // recall the new one
            $authorization = $evaluasi_form->current_authorization();
            if ($authorization == null) {
                $evaluasi_form->status = 1;
                $evaluasi_form->save();
            }

            // cek apakah ada evalusi form lain yang belum selesai
            if ($authorization == null) {
                if ($this->isAllEvalusiFormFinished($evaluasi_form->security_ticket_id) == true) {
                    $securityticket  = SecurityTicket::find($evaluasi_form->security_ticket_id);
                    switch ($securityticket->type()) {
                        case 'Perpanjangan':
                            $securityticket->status = 4;
                            $message = "Silahkan melanjutkan ke Menu PO.";
                            break;
                        case 'Replace':
                            $securityticket->status = 4;
                            $message = "Silahkan melanjutkan ke Menu PO.";
                            break;
                        case 'End Kontrak':
                            $securityticket->status = 5;
                            $message = "Silahkan melanjutkan upload surat Pemutusan kontrak.";
                            break;
                        case 'Percepatan Replace':
                            $securityticket->status = 4;
                            $message = "Silahkan melanjutkan ke Menu PO.";
                            break;
                        case 'Percepatan End Kontrak':
                            $securityticket->status = 5;
                            $message = "Silahkan melanjutkan upload surat Pemutusan kontrak.";
                            break;
                    }
                    if ($securityticket->status == 4) {
                        // email ke tim GA form evaluasinya
                        $mail_to = $securityticket->ga_emails();
                        $ccs = $securityticket->additional_emails();
                        $attachments = [];
                        foreach ($securityticket->evaluasi_form as $evaluasiform) {
                            array_push($attachments, $evaluasiform->getPath());
                        }
                        $data = array(
                            'original_emails' => $mail_to,
                            'original_ccs' => $ccs,
                            'transaction_type' => $securityticket->type(),
                            'ticketing_type' => 'Security',
                            'salespoint_name' => $securityticket->salespoint->name,
                            'from' => Auth::user()->name,
                            'to' => 'Tim GA',
                            'code' => $securityticket->code,
                            'attachments' => $attachments
                        );
                        if (config('app.env') == 'local') {
                            $mail_to = [config('mail.testing_email')];
                            $ccs = [];
                        }
                        $emailflag = true;
                        try {
                            Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'evaluasiform_approved'));
                        } catch (\Exception $ex) {
                            $emailflag = false;
                        }
                        // $emailmessage = "";
                        // if (!$emailflag) {
                        //     $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                        // }
                    }
                    $securityticket->save();
                    DB::commit();
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => false,
                            // "message" => 'Seluruh Otorisasi Form evaluasi telah selesai. ' . $message . $emailmessage
                            "message" => 'Seluruh Otorisasi Form evaluasi telah selesai. ' . $message

                        ]);
                    } else {
                        // return back()->with('success', 'Seluruh Otorisasi Form evaluasi telah selesai. ' . $message . $emailmessage);
                        return back()->with('success', 'Seluruh Otorisasi Form evaluasi telah selesai. ' . $message);
                    }
                } else {
                    DB::commit();
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => false,
                            "message" => 'Otorisasi Form evaluasi telah selesai.'
                        ]);
                    } else {
                        return back()->with('success', 'Otorisasi Form evaluasi telah selesai.');
                    }
                }
            } else {
                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan otorisasi form evaluasi, Otorisasi selanjutnya oleh ' . $authorization->employee_name
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan otorisasi form evaluasi, Otorisasi selanjutnya oleh ' . $authorization->employee_name);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi form evaluasi (' . $ex->getMessage() . ' [' . $ex->getLine() . '])'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi form evaluasi (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
            }
        }
    }

    public function rejectEvaluasiForm(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $evaluasi_form = EvaluasiForm::findOrFail($request->evaluasi_form_id);
            if ($evaluasi_form->security_ticket->type() == "Perpanjangan") {
                // validasi ticketing block jika perpanjangan
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "security",
                    'block_type' => "block_day",
                    'ticket_code' => $evaluasi_form->security_ticket->code,
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }
            if ($evaluasi_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }

            $authorization = $evaluasi_form->current_authorization();
            $authorization->status = -1;
            $authorization->save();

            $evaluasi_form->status              = -1;
            $evaluasi_form->terminated_by       = Auth::user()->id;
            $evaluasi_form->termination_reason  = $request->reason;
            $evaluasi_form->save();
            $evaluasi_form->delete();
            DB::commit();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Formulir evaluasi berhasil dibatalkan. Silahkan membuat formulir evaluasi baru'
                ]);
            } else {
                return back()->with('success', 'Formulir evaluasi berhasil dibatalkan. Silahkan membuat formulir evaluasi baru');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil melakukan reject otorisasi form evaluasi .'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan reject otorisasi form evaluasi (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
            }
        }
    }

    public function isAllEvalusiFormFinished($security_ticket_id)
    {
        $securityticket = SecurityTicket::find($security_ticket_id);
        $flag = true;
        foreach ($securityticket->evaluasi_form as $form) {
            if ($form->status != 1) {
                $flag = false;
            }
        }
        return $flag;
    }

    public function printEvaluasiForm($crypt_evaluasi_form_id, $method = "stream")
    {
        $id = \Crypt::decryptString($crypt_evaluasi_form_id);
        $evaluasiform = EvaluasiForm::find($id);
        $security_ticket_code = $evaluasiform->security_ticket->code;
        try {
            if (!$evaluasiform) {
                return 'evaluasi form for this ticket is unavailable';
            }
            if ($evaluasiform->status < 1) {
                return 'evaluasi form unavaiable / incomplete';
            }

            $pdf = PDF::loadView('pdf.evaluasiformpdf', compact('evaluasiform'))->setPaper('tabloid', 'portrait');
            if ($method == 'stream') {
                return $pdf->stream('Form Evaluasi (' . $security_ticket_code . '_' . $id . ').pdf');
            } else if ($method == 'path') {
                $path = "/temporary/evaluasiform/Form Evaluasi (" . $security_ticket_code . "_" . $id . ").pdf";
                Storage::disk('public')->put($path, $pdf->output());
                return $path;
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak Form Fasilitas ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function uploadBAPerpanjangan(Request $request)
    {
        try {
            $security_ticket = SecurityTicket::where('code', $request->ticket_code)->first();
            if (!isset($security_ticket)) {
                throw new \Exception('Tiket security ditemukan untuk kode ' . $request->ticket_code);
            } else {
                DB::beginTransaction();
                $new_open_request                  = new TicketingBlockOpenRequest;
                $new_open_request->ticket_code     = $request->ticket_code;
                $new_open_request->po_number       = $request->po_number;


                $extension = pathinfo($request->file('ba_file')->getClientOriginalName(), PATHINFO_EXTENSION);
                $filepath = '/attachments/ticketing/security/' . $security_ticket->code . '/BA_PERPANJANGAN_SECURITY_' . now()->format('YmdHi') . $extension;
                $file = pathinfo($filepath);
                $path = $request->file('ba_file')->storeAs($file['dirname'], $file['basename'], 'public');
                $new_open_request->ba_file_path    = $path;
                // $new_open_request->notes        =   null;
                $new_open_request->created_by      = Auth::user()->id;
                $new_open_request->save();

                // TODO send email untuk semua approver di perpanjangan form
                $monitor                        = new SecurityTicketMonitoring;
                $monitor->security_ticket_id    = $security_ticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Upload BA Perpanjangan Security';
                $monitor->save();

                DB::commit();
                return back()->with('success', 'Berhasil Upload BA Perpanjangan. Proses ticketing dapat dilanjutkan setelah BA di confirm');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Upload BA Perpanjangan (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }
}
