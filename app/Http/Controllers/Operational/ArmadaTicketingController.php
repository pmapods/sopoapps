<?php

namespace App\Http\Controllers\Operational;

use DB;
use PDF;
use Auth;
use Mail;
use Storage;
use App\Models\Po;
use Carbon\Carbon;

use App\Models\Armada;
use App\Models\Ticket;
use App\Models\Vendor;
use App\Models\Employee;
use App\Models\PoDetail;
use App\Models\PoManual;
use App\Models\ArmadaType;
use App\Models\MutasiForm;
use App\Models\SalesPoint;
use Carbon\CarbonImmutable;
use App\Models\ArmadaBudget;
use App\Models\ArmadaTicket;
use App\Models\BudgetUpload;
use App\Models\FacilityForm;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Mail\NotificationMail;
use App\Models\SecurityTicket;
use App\Models\EmailAdditional;
use App\Models\EmployeePosition;
use App\Models\PerpanjanganForm;
use App\Jobs\SendBackgroundEmail;
use App\Http\Controllers\Controller;
use App\Models\ArmadaTicketMonitoring;
use App\Models\EmployeeLocationAccess;
use App\Models\MutasiFormAuthorization;
use App\Models\ArmadaTicketAuthorization;
use App\Models\FacilityFormAuthorization;
use App\Models\TicketingBlockOpenRequest;
use App\Models\PerpanjanganFormAuthorization;

class ArmadaTicketingController extends Controller
{
    public function createArmadaticket(Request $request)
    {
        try {
            DB::beginTransaction();
            // ARMADA TERMASUK JASA
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

            // validasi budget untuk pengadaan baru berdasarkan vendor dan tipe armada
            // NEW tambah validasi untuk pengadaan baru "NON NIAGA" dengan jenis selain "GM PASSENGER" bisa skip validasi
            // HARDCODE --START
            $needvalidation = true;
            if ($request->isNiaga == false && $request->armada_type_id != 8) {
                $needvalidation = false;
            }
            // HARDCODE --END
            // tambahan jika non budget maka skip validasi
            if (!$request->isBudget) {
                $needvalidation = false;
            }

            if ($request->pengadaan_type == 0 && $needvalidation == true) {
                // check apakah budget armada salespoint sudah tersedia
                $budget = BudgetUpload::where('salespoint_id', $request->salespoint_id)
                    ->where('status', 1)->where('type', 'armada')
                    ->where('year', '=', 2023)
                    ->first();
                if ($budget == null) {
                    return back()->with('error', 'Budget belum tersedia. harap melakukan request budget terlebih dahulu');
                }
                $armadatype = ArmadaType::find($request->armada_type_id);
                $vendorname = $request->vendor_recommendation_name;
                $checkBudget = $budget->budget_detail->filter(function ($item) use ($armadatype, $vendorname) {
                    $vendor = Vendor::where('code', trim($item->vendor_code))->first();
                    if (trim($item->armada_type_id) == trim($armadatype->id) && trim($vendor->alias) == trim($vendorname)) {
                        return true;
                    } else {
                        return false;
                    }
                });
                $checkBudget = $checkBudget->first();
                if ($checkBudget != null && $checkBudget->qty - $checkBudget->pending_quota - $checkBudget->used_quota > 0) {
                    $selected_budget_upload_id = $budget->id;
                } else {
                    return back()->with('error', "Budget tidak tersedia untuk tipe " . $armadatype->name . " dengan vendor " . $vendorname);
                }
            }

            $newTicket                   = new ArmadaTicket;
            $newTicket->code             = $code;
            $newTicket->budget_upload_id = $selected_budget_upload_id ?? null;
            $newTicket->requirement_date = $request->requirement_date;
            $newTicket->salespoint_id    = $request->salespoint_id;
            $newTicket->isNiaga          = $request->isNiaga;

            $isBudget = $request->isBudget ?? null;
            // jika non niaga selain GM passenger (armada_type_id = 8) auto budget true
            if ($request->isNiaga == false && $request->armada_type_id == "8") {
                $isBudget = true;
            }
            $newTicket->isBudget         = $isBudget;
            // if ($request->pengadaan_type == 4) {
            // jika pilihan percepatan end kontrak masukin ke pilihan perpanjangan
            // $newTicket->ticketing_type   = 1;
            // } else {
            $newTicket->ticketing_type   = $request->pengadaan_type;
            // }

            $newTicket->vendor_recommendation_name   = $request->vendor_recommendation_name;
            if ($newTicket->ticketing_type == 0) {
                $newTicket->armada_type_id   = $request->armada_type_id;
            }
            if (in_array($newTicket->ticketing_type, [1, 2, 3, 4])) {
                $po = Po::where('no_po_sap', $request->po_id)->first();
                $pomanual = PoManual::where('po_number', $request->po_id)->first();
                if (!isset($po) && !isset($pomanual)) {
                    throw new \Exception('PO belum dipilih');
                }
                if ($po) {
                    $newTicket->armada_type_id      = $po->armada_ticket->armada_type_id;
                    $newTicket->armada_id           = $po->armada_ticket->armada_id;
                    $newTicket->po_reference_number = $po->no_po_sap;
                }
                if ($pomanual) {
                    $plate = ($pomanual->gt_plate != "") ? $pomanual->gt_plate : $pomanual->gs_plate;
                    $armada = Armada::where(DB::raw('trim(lower(plate))'), 'LIKE', '%' . trim(strtolower($plate)))->first();
                    $armadatype = ArmadaType::where(DB::raw('trim(lower(name))'), 'LIKE', '%' . trim(strtolower($pomanual->armada_name)))->first();
                    if ($armadatype == null) {
                        throw new \Exception('Tipe Armada ' . $pomanual->armada_name . ' tidak ditemukan di master jenis armada');
                    }
                    if ($armada == null) {
                        // buat Armada baru
                        $newArmada = new Armada;
                        $newArmada->armada_type_id  = $armadatype->id;
                        $newArmada->plate           = str_replace(' ', '', strtoupper($plate));
                        $newArmada->vehicle_year    = now()->format('Y') . '-01-01';
                        $newArmada->status          = 1;
                        $newArmada->booked_by       = Auth::user()->id;
                        $newArmada->save();

                        $armada = $newArmada;
                    }

                    $newTicket->armada_type_id      = $armadatype->id;
                    $newTicket->armada_id           = $armada->id ?? null;
                    $newTicket->po_reference_number = $pomanual->po_number;
                }

                if ($newTicket->armada_type_id == null || $newTicket->armada_id == null) {
                    throw new \Exception('Armada / Tipe Armada belum terdaftar di master Armada. Harap Menghubungi Admin');
                }

                // jika perpanjangan dan bukan percepatan end kontrak, validasi apakah po terkait end date nya di bulan berjalan
                if ($newTicket->ticketing_type == 1 && $request->pengadaan_type != 4) {
                    if ($po && $po->end_date != null) {
                        $po_end_date = CarbonImmutable::parse($po->end_date);
                        $po_available_date = $po_end_date->subDays(30);
                        if (now() < $po_available_date) {
                            return back()->with('error', "PO dengan nomor: " . $po->no_po_sap . " hanya dapat diperpanjang di H-30 dari tanggal Akhir PO. (PO End Date: " . $po_end_date->translatedFormat('d M Y') . ")");
                        }
                    }
                    if ($pomanual && $pomanual->end_date != null) {
                        $po_end_date = CarbonImmutable::parse($pomanual->end_date);
                        $po_available_date = $po_end_date->subDays(30);
                        if (now() < $po_available_date) {
                            return back()->with('error', "PO dengan nomor: " . $pomanual->po_number . " hanya dapat diperpanjang di H-30 dari tanggal akhir PO. (PO End Date: " . $po_end_date->translatedFormat('d M Y') . ")");
                        }
                    }
                }
            }
            $newTicket->created_by       = Auth::user()->id;
            $newTicket->save();

            if (($newTicket->ticketing_type == 0 && $newTicket->isNiaga == true) || $request->authorSelect == "pr_manual") {
                // hanya pengadaan baru yang butuh otorisasi
                // REVISI 09-06-2022 approval dipindah ke matriks form fasilitas
                $authorization = Authorization::findOrFail($request->authorization_id);
                if (!$authorization) {
                    throw new \Exception("Matriks Approval terkait form belum tersedia. Silahkan hubungi admin");
                }
                foreach ($authorization->authorization_detail as $key => $authorization) {
                    $newAuthorization                    = new ArmadaTicketAuthorization;
                    $newAuthorization->armada_ticket_id  = $newTicket->id;
                    $newAuthorization->employee_id       = $authorization->employee_id;
                    $newAuthorization->employee_name     = $authorization->employee->name;
                    $newAuthorization->as                = $authorization->sign_as;
                    $newAuthorization->employee_position = $authorization->employee_position->name;
                    $newAuthorization->level             = $key + 1;
                    $newAuthorization->save();
                }
            }
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $newTicket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Pengadaan Armada Baru Dibuat';
            $monitor->save();
            DB::commit();

            // 0 Pengadaan Baru
            // 1 Perpanjangan/Replace/Renewal/Stop Sewa
            // 2 Mutasi
            // 3 COP
            // 4 Percepatan Replace/Renewal/Stop Sewa
            $dilanjutkan_text = "";
            if ($newTicket->ticketing_type == 0) {
                if ($newTicket->isNiaga == true || $request->authorSelect == "pr_manual") {
                    $dilanjutkan_text = "Harap melakukan approval tiket";
                } else {
                    $dilanjutkan_text = "Harap mengisi form fasilitas";
                }
            } else if ($newTicket->ticketing_type == 1) {
                $dilanjutkan_text = "Harap mengisi form perpanjangan";
            } else if ($newTicket->ticketing_type == 2) {
                $dilanjutkan_text = "Harap mengisi form mutasi";
            } else if ($newTicket->ticketing_type == 4) {
                $dilanjutkan_text = "Harap mengisi form percepatan";
            } else {
            }
            return redirect('/armadaticketing/' . $newTicket->code)->with('success', 'Berhasil membuat ticketing armada. ' . $dilanjutkan_text);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat ticketing armada (' . $ex->getMessage() . " [" . $ex->getLine() . "] " . ')');
        }
    }

    public function armadaTicketDetail(Request $request, $code)
    {
        $armadaticket = ArmadaTicket::where('code', $code)->first();
        // validate budget detail has akses area
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        if (!$user_location_access->contains($armadaticket->salespoint_id)) {
            return redirect('/ticketing')->with('error', 'Anda tidak memiliki akses untuk tiket berikut. Tidak memiliki akses salespoint "' . $armadaticket->salespoint->name . '"');
        }
        $employee_positions = EmployeePosition::all();

        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');

        $salespoint = SalesPoint::find($armadaticket->salespoint_id);
        $formperpanjangan_authorizations = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 6)->get();
        $formfasilitas_authorizations = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 4)->get();
        $formmutasi_authorizations = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 5)->get();

        $po = Po::where('no_po_sap', $armadaticket->po_reference_number)->first();
        $pomanual = PoManual::where('po_number', $armadaticket->po_reference_number)->first();
        $salespoints = SalesPoint::all();

        $available_armadas = Armada::where('salespoint_id', $armadaticket->salespoint_id)
            ->where('armada_type_id', $armadaticket->armada_type_id)
            ->where('status', 0)
            ->get();
        try {
            if (!$armadaticket) {
                throw new \Exception('Ticket armada dengan kode ' . $code . 'tidak ditemukan');
            }
            return view('Operational.Armada.armadaticketdetail', compact('armadaticket', 'employee_positions', 'available_salespoints', 'formperpanjangan_authorizations', 'formfasilitas_authorizations', 'available_armadas', 'formmutasi_authorizations', 'salespoints', 'po', 'pomanual'));
        } catch (\Exception $ex) {
            return redirect('/ticketing?menu=Armada')->with('error', 'Gagal membukan detail ticket armada ' . $ex->getMessage());
        }
    }

    public function addFacilityForm(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::find($request->armada_ticket_id);

            $salespoint_initial = $armadaticket->salespoint->initial;
            $currentmonth = date('m');
            $currentyear = date('Y');
            $count = FacilityForm::join('armada_ticket', 'facility_form.armada_ticket_id', 'armada_ticket.id')
                ->where('armada_ticket.salespoint_id', $armadaticket->salespoint->id)
                ->whereYear('facility_form.created_at', Carbon::now()->year)
                ->whereMonth('facility_form.created_at', Carbon::now()->month)
                ->withTrashed()
                ->count();

            do {
                $flag = true;
                $code = $salespoint_initial . '/' . $count . '/FF/' . numberToRoman(intval($currentmonth)) . '/' . $currentyear;
                $count++;
                $checkFacilityForm = FacilityForm::where('code', $code)->first();
                if ($checkFacilityForm != null) {
                    $flag = false;
                }
            } while (!$flag);

            $form                       = new FacilityForm;
            $form->armada_ticket_id     = $request->armada_ticket_id;
            $form->salespoint_id        = $request->salespoint_id;
            $form->code                 = $code;
            $form->nama                 = $request->nama;
            $form->divisi               = $request->divisi;
            $form->phone                = $request->phone;
            $form->jabatan              = $request->jabatan;
            $form->tanggal_mulai_kerja  = $request->tanggal_mulai_kerja;
            $form->golongan             = $request->golongan;
            $form->status_karyawan      = $request->status_karyawan;
            $form->facilitylist         = json_encode($request->fasilitasdanperlengkapan);
            $form->notes                = $request->notes;
            $form->created_by           = Auth::user()->id;
            $form->save();

            $authorization = Authorization::find($request->authorization_id);
            if (!$authorization) {
                throw new \Exception("Matriks Approval terkait form belum tersedia. Silahkan hubungi admin");
            }
            foreach ($authorization->authorization_detail as $detail) {
                $newAuthorization                     = new FacilityFormAuthorization;
                $newAuthorization->facility_form_id   = $form->id;
                $newAuthorization->employee_id        = $detail->employee_id;
                $newAuthorization->employee_name      = $detail->employee->name;
                $newAuthorization->as                 = $detail->sign_as;
                $newAuthorization->employee_position  = $detail->employee_position->name;
                $newAuthorization->level              = $detail->level;
                $newAuthorization->save();
            }
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Form Fasilitas Baru Dibuat';
            $monitor->save();

            $mail_to = $authorization->authorization_detail->first()->employee->email;
            $name_to = $authorization->authorization_detail->first()->employee->name;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $armadaticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'facilityform_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();
            return back()->with('success', 'Berhasil membuat form fasilitas. Menunggu Otorisasi oleh ' . $authorization->authorization_detail->first()->employee->name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat form fasilitas');
        }
    }

    public function addPerpanjanganForm(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
        try {
            if ($armadaticket->ticketing_type != 4) {
                DB::beginTransaction();
                $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
                $newrequest = new Request;
                $newrequest->replace([
                    'type' => "armada",
                    'block_type' => "block_day",
                    'ticket_code' => $armadaticket->code,
                ]);
                $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
                $responseData = $response->getData();
                if ($responseData->error) {
                    throw new \Exception($responseData->message);
                }
            }

            $form                   = new PerpanjanganForm;
            $form->armada_ticket_id = $request->armada_ticket_id;
            $form->salespoint_id    = $request->salespoint_id;
            $form->armada_id        = $request->armada_id;
            $form->nama             = $request->name;
            $form->nik              = $request->nik;
            $form->jabatan          = $request->jabatan;
            $form->nama_salespoint  = $request->salespoint_name;
            $form->tipe_armada      = $request->armada_type;
            $form->jenis_kendaraan  = $request->jenis_kendaraan;
            $form->nopol            = $request->nopol;
            $form->unit             = $request->unit;
            $form->is_vendor_lokal  = ($request->lokal_vendor_name != null) ? true : false;
            $form->nama_vendor      = ($form->is_vendor_lokal) ? $request->lokal_vendor_name : $request->vendor_name;
            $form->form_type        = $request->form_type;
            if ($form->form_type == "perpanjangan") {
                $form->perpanjangan_length = $request->perpanjangan_length;
            }
            if ($form->form_type == "stopsewa") {
                $form->stopsewa_date = $request->stopsewa_date;
                $form->stopsewa_reason = $request->alasan;
                if ($request->po_before_end_date == true) {
                    $form->is_percepatan = true;
                }
            }
            $form->created_by       = Auth::user()->id;
            $form->save();

            $authorization = Authorization::findOrFail($request->authorization_id);
            if (!$authorization) {
                throw new \Exception("Matriks Approval terkait form belum tersedia. Silahkan hubungi admin");
            }
            foreach ($authorization->authorization_detail as $detail) {
                $newAuthorization                           = new PerpanjanganFormAuthorization;
                $newAuthorization->perpanjangan_form_id     = $form->id;
                $newAuthorization->employee_id              = $detail->employee_id;
                $newAuthorization->employee_name            = $detail->employee->name;
                $newAuthorization->as                       = $detail->sign_as;
                $newAuthorization->employee_position        = $detail->employee_position->name;
                $newAuthorization->level                    = $detail->level;
                $newAuthorization->save();
            }
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $request->armada_ticket_id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membuat Form Perpanjangan';
            $monitor->save();

            $mail_to = $form->current_authorization()->employee->email;
            $name_to = $form->current_authorization()->employee->name;
            $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $armadaticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'perpanjanganform_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            DB::commit();
            return back()->with('success', 'Berhasil membuat form perpanjangan perhentian. Menunggu Otorisasi oleh ' . $authorization->authorization_detail->first()->employee->name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat form perpanjangan (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }

    public function addMutasiForm(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
            $salespoint_initial = $armadaticket->salespoint->initial;
            $currentmonth = date('m');
            $currentyear = date('Y');

            $count = MutasiForm::join('armada_ticket', 'mutasi_form.armada_ticket_id', 'armada_ticket.id')
                ->where('armada_ticket.salespoint_id', $armadaticket->salespoint->id)
                ->whereYear('mutasi_form.created_at', Carbon::now()->year)
                ->whereMonth('mutasi_form.created_at', Carbon::now()->month)
                ->withTrashed()
                ->count();

            do {
                $flag = true;
                $code = $salespoint_initial . '/' . $count . '/MA/' . numberToRoman(intval($currentmonth)) . '/' . $currentyear;
                $count++;
                $checkMutasiForm = MutasiForm::where('code', $code)->first();
                if ($checkMutasiForm != null) {
                    $flag = false;
                }
            } while (!$flag);
            $form                           = new MutasiForm;
            $form->armada_ticket_id         = $request->armada_ticket_id;
            $form->salespoint_id            = $request->sender_salespoint_id;
            $form->receiver_salespoint_id   = $request->receive_salespoint_id;
            $form->armada_id                = $request->armada_id;
            $form->code                     = $code;
            $form->sender_salespoint_name   = $request->sender_salespoint_name;
            $receiver_salespoint_name = SalesPoint::find($request->receive_salespoint_id)->name;
            $form->receiver_salespoint_name = $receiver_salespoint_name;
            $form->mutation_date            = $request->mutation_date;
            $form->received_date            = $request->received_date;
            $form->nopol                    = $request->nopol;
            $form->vendor_name              = $request->vendor_name;
            $form->brand_name               = $request->merk;
            $form->jenis_kendaraan          = $request->jenis_kendaraan;
            $form->nomor_rangka             = $request->nomor_rangka;
            $form->nomor_mesin              = $request->nomor_mesin;
            $form->tahun_pembuatan          = $request->tahun_pembuatan;
            $form->stnk_date                = $request->stnk_date;
            $form->p3k                      = $request->p3k;
            $form->segitiga                 = $request->segitiga;
            $form->dongkrak                 = $request->dongkrak;
            $form->toolkit                  = $request->toolkit;
            $form->ban                      = $request->ban;
            $form->gembok                   = $request->gembok;
            $form->bongkar                  = $request->bongkar;
            $form->buku                     = $request->buku;
            $form->nama_tempat              = $request->nama_tempat;
            $form->created_by               = Auth::user()->id;
            $form->alasan_mutasi            = $request->alasan_mutasi;
            $form->save();

            $authorization = Authorization::find($request->authorization_id);
            if (!$authorization) {
                throw new \Exception("Matriks Approval terkait form belum tersedia. Silahkan hubungi admin");
            }
            foreach ($authorization->authorization_detail as $detail) {
                $newAuthorization                           = new MutasiFormAuthorization;
                $newAuthorization->mutasi_form_id           = $form->id;
                $newAuthorization->employee_id              = $detail->employee_id;
                $newAuthorization->employee_name            = $detail->employee->name;
                $newAuthorization->as                       = $detail->sign_as;
                $newAuthorization->employee_position        = $detail->employee_position->name;
                $newAuthorization->level                    = $detail->level;
                $newAuthorization->save();
            }

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $request->armada_ticket_id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membuat Form Mutasi';
            $monitor->save();

            $mail_to = $form->current_authorization()->employee->email;
            $name_to = $form->current_authorization()->employee->name;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $armadaticket->code,
                'mutasi_form' => $form,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'mutasiform_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();
            return back()->with('success', 'Berhasil membuat form mutasi. Menunggu Otorisasi oleh ' . $authorization->authorization_detail->first()->employee->name . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat form mutasi');
        }
    }

    public function completeArmadaBookedBy(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->armada as $armada) {
                $available_armada               = Armada::find($armada['armada_id']);
                $available_armada->booked_by    = $armada['booked_by'];
                $available_armada->status       = 1;
                $available_armada->save();
            }

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $request->armada_ticket_id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Melengkapi data available armada dengan nomor polisi ' . $available_armada->plate;
            $monitor->save();
            DB::commit();

            return back()->with('success', 'Berhasil Melengkapi data available armada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Melengkapi data armada');
        }
    }

    public function approveFacilityForm(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $facility_form = FacilityForm::findOrFail($request->facility_form_id);
            if ($facility_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }
            $authorization = $facility_form->current_authorization();
            $authorization->status = 1;
            $authorization->save();

            // recall the new one
            $authorization = $facility_form->current_authorization();
            if ($authorization == null) {
                $facility_form->status = 1;
                $facility_form->save();
            }

            $armadaticket = $facility_form->armada_ticket;
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Approve form fasilitas';
            $monitor->save();

            $authorization = $facility_form->current_authorization();
            if ($authorization == null) {
                // START NEW LOGIC
                $armadaticket->status = 4;
                $message = "Silahkan melanjutkan ke Menu PO (Non Niaga).";
                $armadaticket->save();

                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Menyelesaikan Otorisasi Form Fasilitas';
                $monitor->save();

                $region_purchasing_emails = EmailAdditional::where('type', $armadaticket->salespoint->region_type)
                    ->where('category', 'purchasing')->first()->emails ?? [];
                $region_purchasing_emails = json_decode($region_purchasing_emails);
                $national_purchasing_emails = EmailAdditional::where('type', 'national')->where('category', 'purchasing')->first()->emails ?? [];
                $national_purchasing_emails = json_decode($national_purchasing_emails);

                $mail_to = array_unique(array_merge($national_purchasing_emails, $region_purchasing_emails));
                $name_to = 'Purchasing Team';

                array_merge($mail_to, $armadaticket->ga_emails());
                $ccs = $armadaticket->additional_emails();
                $name_to .= ', GA Team';
                $attachments = [
                    'facilityform' => ($armadaticket->facility_form) ? $armadaticket->facility_form->getPath() : null
                ];

                $data = array(
                    'original_emails' => $mail_to,
                    'original_ccs' => $ccs ?? [],
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $armadaticket->code,
                    'attachments' => $attachments ?? []
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_approved'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }
                // END NEW LOGIC
            }
            DB::commit();
            if ($authorization == null) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Seluruh Otorisasi Form fasilitas telah selesai. Proses selanjutnya PR SAP'
                    ]);
                } else {
                    return back()->with('success', 'Seluruh Otorisasi Form fasilitas telah selesai. Proses selanjutnya PR SAP');
                }
            } else {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan otorisasi, Otorisasi selanjutnya oleh ' . $authorization->employee_name
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan otorisasi, Otorisasi selanjutnya oleh ' . $authorization->employee_name);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi form fasilitas (' . $ex->getMessage() . '[' . $ex->getLine() . '])'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi form fasilitas (' . $ex->getMessage() . '[' . $ex->getLine() . '])');
            }
        }
    }

    public function rejectFacilityForm(Request $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $facility_form = FacilityForm::findOrFail($request->facility_form_id);
            if ($facility_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }

            $authorization = $facility_form->current_authorization();
            $authorization->status = -1;
            $authorization->save();

            $facility_form->status              = -1;
            $facility_form->terminated_by       = Auth::user()->id;
            $facility_form->termination_reason  = $request->reason;
            $facility_form->save();
            $facility_form->delete();

            $armadaticket = $facility_form->armada_ticket;
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membatalkan Pengadaan Armada';
            $monitor->save();

            $employee_ids = $armadaticket->authorizations->pluck('employee_id');
            $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
            $mail_to = $employee_emails->toArray();
            $name_to = "Bapak/Ibu";
            $data = array(
                'original_emails' => $mail_to,
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $armadaticket->code,
                'reason' => $request->reason,
                'facility_form' => $facility_form,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'facilityform_reject'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Formulir fasilitas berhasil dibatalkan. Silahkan membuat formulir fasilitas baru' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Formulir fasilitas berhasil dibatalkan. Silahkan membuat formulir fasilitas baru' . $emailmessage);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi form fasilitas'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi form fasilitas');
            }
        }
    }

    public function approvePerpanjanganForm(Request $request, $return_data_type = 'view')
    {
        try {
            $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);

            $newrequest = new Request;
            $newrequest->replace([
                'type' => "armada",
                'block_type' => "block_day",
                'ticket_code' => $perpanjangan_form->armada_ticket->code,
            ]);
            $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
            $responseData = $response->getData();
            if ($responseData->error) {
                throw new \Exception($responseData->message);
            }

            DB::beginTransaction();
            if ($perpanjangan_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }
            $authorization = $perpanjangan_form->current_authorization();
            $authorization->status = 1;
            $authorization->save();

            // recall the new one
            $authorization = $perpanjangan_form->current_authorization();
            if ($authorization == null) {
                $perpanjangan_form->status = 1;
                $perpanjangan_form->save();
            }

            $armadaticket = $perpanjangan_form->armada_ticket;
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Menyelesaikan otorisasi form perpanjangan';
            $monitor->save();
            if ($authorization == null) {
                switch ($armadaticket->type()) {
                    case 'Percepatan Replace':
                        $armadaticket->status = 5;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'Percepatan Renewal':
                        $armadaticket->status = 5;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'Percepatan End Kontrak':
                        $armadaticket->status = 5;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'Percepatan Replace/Renewal/Stop Sewa':
                        $armadaticket->status = 5;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'Perpanjangan':
                        $armadaticket->status = 4;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'Replace':
                        $armadaticket->status = 4;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'Renewal':
                        $armadaticket->status = 4;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;

                    case 'End Kontrak':
                        $armadaticket->status = 4;
                        $message = "Kelengkapan akan diteruskan ke GA untuk dilakukan proses validasi.";
                        $armadaticket->save();
                        DB::commit();
                        break;
                }
                if ($armadaticket->status = 4) {
                    $mail_to = $armadaticket->ga_emails();
                    $ccs = $armadaticket->additional_emails();
                    $attachments = [
                        'perpanjanganform' => $perpanjangan_form->getPath()
                    ];
                    $data = array(
                        'original_emails' => $mail_to,
                        'original_ccs' => $ccs,
                        'transaction_type' => $armadaticket->type(),
                        'ticketing_type' => 'Armada',
                        'salespoint_name' => $armadaticket->salespoint->name,
                        'from' => Auth::user()->name,
                        'to' => 'Tim GA',
                        'code' => $armadaticket->code,
                        'attachments' => $attachments
                    );
                    if (config('app.env') == 'local') {
                        $mail_to = [config('mail.testing_email')];
                        $ccs = [];
                    }
                    $emailflag = true;
                    try {
                        Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'perpanjanganform_approved'));
                    } catch (\Exception $ex) {
                        $emailflag = false;
                    }
                    $emailmessage = "";
                    if (!$emailflag) {
                        $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                    }
                }
                // $armadaticket->save();
                // DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Seluruh Otorisasi Form Perpanjangan telah selesai. ' . $message . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Seluruh Otorisasi Form Perpanjangan telah selesai. ' . $message . $emailmessage);
                }
            } else {
                $mail_to = $perpanjangan_form->current_authorization()->employee->email;
                $name_to = $perpanjangan_form->current_authorization()->employee->name;
                $data = array(
                    'original_emails' => [$mail_to],
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $armadaticket->code,
                    'perpanjangan_form' => $perpanjangan_form,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    // SendBackgroundEmail::dispatch($mail_to,[],new NotificationMail($data, 'perpanjanganform_approval'));
                    Mail::to($mail_to)->send(new NotificationMail($data, 'perpanjanganform_approval'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }

                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan otorisasi formulir perpanjangan, Otorisasi selanjutnya oleh ' . $authorization->employee_name . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan otorisasi formulit perpanjangan, Otorisasi selanjutnya oleh ' . $authorization->employee_name . $emailmessage);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi form fasilitas'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi form fasilitas (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
            }
        }
    }

    public function rejectPerpanjanganForm(Request $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);

            $newrequest = new Request;
            $newrequest->replace([
                'type' => "armada",
                'block_type' => "block_day",
                'ticket_code' => $perpanjangan_form->armada_ticket->code,
            ]);
            $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
            $responseData = $response->getData();
            if ($responseData->error) {
                throw new \Exception($responseData->message);
            }

            DB::beginTransaction();
            if ($perpanjangan_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }

            $authorization = $perpanjangan_form->current_authorization();
            $authorization->status = -1;
            $authorization->save();

            $perpanjangan_form->status              = -1;
            $perpanjangan_form->terminated_by       = Auth::user()->id;
            $perpanjangan_form->termination_reason  = $request->reason;
            $perpanjangan_form->save();
            $perpanjangan_form->delete();

            $armadaticket = $perpanjangan_form->armada_ticket;
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membatalkan Formulir Perpanjangan';
            $monitor->save();

            $employee_ids = $perpanjangan_form->authorizations->pluck('employee_id');
            $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
            $mail_to = $employee_emails->toArray();
            $name_to = 'Bapak/Ibu';
            $data = array(
                'original_emails' => $mail_to,
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $armadaticket->code,
                'reason' => $request->reason,
                'perpanjangan_form' => $perpanjangan_form,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'perpanjanganform_reject'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();

            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Formulir perpanjangan berhasil dibatalkan. Silahkan membuat formulir perpanjangan baru' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Formulir perpanjangan berhasil dibatalkan. Silahkan membuat formulir perpanjangan baru' . $emailmessage);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal membatalkan formulir perpanjangan'
                ]);
            } else {
                return back()->with('error', 'Gagal membatalkan formulir perpanjangan (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
            }
        }
    }

    public function approveMutasiForm(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $mutasi_form = MutasiForm::findOrFail($request->mutasi_form_id);
            if ($mutasi_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }
            $authorization = $mutasi_form->current_authorization();
            $authorization->status = 1;
            $authorization->save();

            // recall the new one
            $authorization = $mutasi_form->current_authorization();
            if ($authorization == null) {
                $mutasi_form->status = 1;
                $mutasi_form->save();
            }

            $armadaticket = $mutasi_form->armada_ticket;
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Menyelesaikan Otorisasi Form Mutasi';
            $monitor->save();
            if ($authorization == null) {
                $armadaticket->status = 4;
                $armadaticket->save();

                // mail form mutasi ke GA
                $mail_to = $armadaticket->ga_emails();
                $ccs = $armadaticket->additional_emails();
                $attachments = [
                    'mutasiform' => $mutasi_form->getPath()
                ];
                $data = array(
                    'original_emails' => $mail_to,
                    'original_ccs' => $ccs,
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => 'Tim GA',
                    'code' => $armadaticket->code,
                    'attachments' => $attachments
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                    $ccs = [];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'mutasiform_approved'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }
                DB::commit();

                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Seluruh Otorisasi Form Mutasi telah selesai. Silahkan melanjutkan ke Menu PO.' . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Seluruh Otorisasi Form Mutasi telah selesai. Silahkan melanjutkan ke Menu PO.' . $emailmessage);
                }
            } else {
                $mail_to = $mutasi_form->current_authorization()->employee->email;
                $name_to = $mutasi_form->current_authorization()->employee->name;
                $data = array(
                    'original_emails' => [$mail_to],
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $armadaticket->code,
                    'mutasi_form' => $mutasi_form,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'mutasiform_approval'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }
                DB::commit();

                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan otorisasi formulir mutasi, Otorisasi selanjutnya oleh ' . $authorization->employee_name . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan otorisasi formulir mutasi, Otorisasi selanjutnya oleh ' . $authorization->employee_name . $emailmessage);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi form fasilitas'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi form fasilitas' . $ex->getMessage() . '"');
            }
        }
    }

    public function rejectMutasiForm(Request $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $mutasi_form = MutasiForm::findOrFail($request->mutasi_form_id);
            if ($mutasi_form->current_authorization()->employee_id != Auth::user()->id) {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Login tidak sesuai dengan otorisasi'
                    ]);
                } else {
                    return back()->with('error', 'Login tidak sesuai dengan otorisasi');
                }
            }

            $authorization = $mutasi_form->current_authorization();
            $authorization->status = -1;
            $authorization->save();

            $mutasi_form->status              = -1;
            $mutasi_form->terminated_by       = Auth::user()->id;
            $mutasi_form->termination_reason  = $request->reason;
            $mutasi_form->save();
            $mutasi_form->delete();

            $armadaticket = $mutasi_form->armada_ticket;
            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membatalkan Formulir Mutasi';
            $monitor->save();

            // $mail_to = $mutasi_form->current_authorization()->employee->email;
            // $name_to = $mutasi_form->current_authorization()->employee->name;

            $data = array(
                // 'original_emails' => [$mail_to],
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                // 'to' => $name_to,
                'code' => $armadaticket->code,
                'mutasi_form' => $mutasi_form,
                'reason' => $request->reason,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'mutasiform_reject'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            DB::commit();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Formulir mutasi berhasil dibatalkan. Silahkan membuat formulir mutasi baru' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Formulir mutasi berhasil dibatalkan. Silahkan membuat formulir mutasi baru' . $emailmessage);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan otorisasi form mutasi'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan otorisasi form mutasi' . $ex->getMessage());
            }
        }
    }

    public function uploadBASTK(Request $request)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);

            $salespointname = str_replace(' ', '_', $armadaticket->salespoint->name);
            $ext = pathinfo($request->file('bastk_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BASTK_" . $salespointname . '.' . $ext;
            $path = "/attachments/ticketing/armada/" . $armadaticket->code . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('bastk_file')->storeAs($file['dirname'], $file['basename'], 'public');
            $armadaticket->bastk_path = $path;

            if (in_array($armadaticket->type(), ['Replace', 'Renewal', 'End Kontrak'])) {
                $po = Po::where('no_po_sap', $armadaticket->po_reference_number)->first();
                $pomanual = PoManual::where('po_number', $armadaticket->po_reference_number)->first();
                $plate = $po->armada_ticket->armada->plate ?? $pomanual->plate();

                // hapus armada lama
                $oldarmada = Armada::where('plate', $plate)->first();
                if ($oldarmada != null) {
                    $oldarmada->status = 0;
                    $oldarmada->save();
                    $oldarmada->delete();
                }

                // close PO terkait
                $po = $armadaticket->po_reference;
                $po->status = 4;
                $po->save();
            }
            if (in_array($armadaticket->type(), ['Perpanjangan', 'Replace', 'Renewal', 'Mutasi', 'End Kontrak'])) {
                // ubah status po lama ke closed(4)
                $po = $armadaticket->po_reference;
                $pomanual = PoManual::where('po_number', $armadaticket->po_reference_number)->first();
                if ($po) {
                    $po->status = 4;
                    $po->save();
                }
                if ($pomanual) {
                    $pomanual->status = 4;
                    $pomanual->save();
                }
            }

            $armadaticket->finished_date = date('Y-m-d');
            $armadaticket->status = 6;
            $armadaticket->save();

            if (in_array($armadaticket->type(), ['Pengadaan', 'Replace', 'Renewal'])) {
                // tambahkan armada ke master armada
                $armada_by_plate = Armada::where('plate', str_replace(' ', '', strtoupper($request->plate)))->first();
                if ($armada_by_plate) {
                    $newArmada                  = $armada_by_plate;
                } else {
                    $newArmada                  = new Armada;
                }

                if ($request->plate == "" || $request->plate == null) {
                    throw new \Exception('Nomor Plat tidak boleh kosong.');
                }
                $newArmada->salespoint_id   = $armadaticket->salespoint_id;
                $newArmada->armada_type_id  = $armadaticket->armada_type_id;
                $newArmada->plate           = str_replace(' ', '', strtoupper($request->plate));
                $newArmada->vehicle_year    = $request->vehicle_year . '-01-01';
                $newArmada->status          = ($request->booked_by == null) ? 0 : 1;
                $newArmada->booked_by       = $request->booked_by ?? null;
                $newArmada->save();
                $armadaticket->armada_id = $newArmada->id;
                if ($request->type == 'gs') {
                    $armadaticket->gs_plate = str_replace(' ', '', strtoupper($newArmada->plate));
                    $armadaticket->gs_received_date = date('Y-m-d');
                }
                if ($request->type == 'gt') {
                    $armadaticket->gt_plate = str_replace(' ', '', strtoupper($newArmada->plate));
                    $armadaticket->gt_received_date = date('Y-m-d');
                }
                $armadaticket->save();
            }

            if ($armadaticket->type() == "Mutasi") {
                // ubah salespoint armada
                $armadaticket->salespoint_id = $armadaticket->mutasi_form->receiver_salespoint_id;
                $armadaticket->save();
            }

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Mengupload dokumen kelengkapan';
            $monitor->save();
            DB::commit();
            return back()->with('success', 'Berhasil melakukan upload dokumen kelengkapan, armada dengan Nopol ' . str_replace(' ', '', strtoupper($request->plate)) . ' telah diupdate di master armada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan upload dokumen kelengkapan ' . $ex->getMessage());
        }
    }

    public function uploadOldBASTK(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($id);

            $bastk_file                 = $request->file()['bastk_old_file'];
            $bastk_ext                  = pathinfo($bastk_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $armadaticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $armadaticket->code;

            $path                       = 'attachments/ticketing/armada/' . $code . '/' . 'BASTK_OLD_' . $salespointname . '.' . $bastk_ext;
            $info                       = pathinfo($path);
            $bastk_path                 = $bastk_file->storeAs($info['dirname'], $info['basename'], 'public');

            $armadaticket->bastk_replace_filepath   = $bastk_path;
            $armadaticket->save();

            DB::commit();
            return back()->with('success', 'Berhasil Melakukan Upload Berkas Penyerahan (Replace dan Percepatan Replace)');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('Gagal Melakukan Upload Berkas Penyerahan (Replace dan Percepatan Replace) ' . $ex->getMessage());
        }
    }

    public function uploadBASTKGT(Request $request)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            $armada = $armadaticket->armada;
            $armada->plate = $request->gt_plate;
            $armada->vehicle_year = $request->vehicle_year . "-01-01";
            $armada->save();

            $salespointname = str_replace(' ', '_', $armadaticket->salespoint->name);
            $ext = pathinfo($request->file('bastk_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BASTK_GT_" . $salespointname . '.' . $ext;
            $path = "/attachments/ticketing/armada/" . $armadaticket->code . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('bastk_file')->storeAs($file['dirname'], $file['basename'], 'public');

            $armadaticket->bastk_path = $path;
            $armadaticket->gt_plate = str_replace(' ', '', strtoupper($request->gt_plate));
            $armadaticket->gt_received_date = date('Y-m-d');
            $armadaticket->save();

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Melakukan Upload BASTK Unit GT';
            $monitor->save();

            DB::commit();
            return back()->with('success', 'Berhasil update armada dan upload bastk');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal update armada dan upload bastk ' . $ex->getMessage());
        }
    }

    public function reviseOldBASTK(Request $request)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);

            $bastk_file                 = $request->file()['file_old_bastk'];
            $bastk_ext                  = pathinfo($bastk_file->getClientOriginalName(), PATHINFO_EXTENSION);

            $salespoint                 = $armadaticket->salespoint;
            $salespointname             = str_replace(' ', '_', $salespoint->name);
            $code                       = $armadaticket->code;

            $path                       = 'attachments/ticketing/armada/' . $code . '/' . 'BASTK_OLD_' . $salespointname . '.' . $bastk_ext;
            $info                       = pathinfo($path);
            $bastk_path                 = $bastk_file->storeAs($info['dirname'], $info['basename'], 'public');

            $armadaticket->bastk_replace_filepath   = $bastk_path;
            $armadaticket->save();

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Revisi Upload BASTK Lama';
            $monitor->save();
            DB::commit();
            return back()->with('success', 'Berhasil revisi BASTK Lama');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal revisi BASTK Lama ' . $ex->getMessage());
        }
    }

    public function reviseBASTK(Request $request)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            $file = pathinfo($armadaticket->bastk_path);
            $request->file('file_bastk')->storeAs($file['dirname'], $file['basename'], 'public');

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Revisi Upload BASTK';
            $monitor->save();
            DB::commit();
            return back()->with('success', 'Berhasil revisi BASTK');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal revisi BASTK ' . $ex->getMessage());
        }
    }

    public function verifyPO(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);

            $po = $armadaticket->po_reference;
            $pomanual = PoManual::where('po_number', $armadaticket->po_reference_number)->first();
            if ($po) {
                $po->status = 4;
                $po->save();
            }
            if ($pomanual) {
                $pomanual->status = 4;
                $pomanual->save();
            }

            $armadaticket->finished_date = date('Y-m-d');
            $armadaticket->status = 6;
            $armadaticket->save();

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Melakukan Verifikasi PO';
            $monitor->save();
            DB::commit();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil melakukan verifikasi PO.'
                ]);
            } else {
                return back()->with('success', 'Berhasil melakukan verifikasi PO.');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan verifikasi PO.'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan verifikasi PO.');
            }
        }
    }

    public function startArmadaAuthorization(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            if ($armadaticket->updated_at != $request->updated_at) {
                return back()->with('error', 'Data terbaru sudah diupdate. Silahkan coba kembali');
            } else {
                $armadaticket->requirement_date = $request->requirement_date;
                $armadaticket->status += 1;
                $armadaticket->save();
            }
            // reset authorization
            foreach ($armadaticket->authorizations as $authorization) {
                $authorization->status = 0;
                $authorization->reject_notes = null;
                $authorization->save();
            }

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Memulai Otorisasi Armada';
            $monitor->save();

            $mail_to = $armadaticket->current_authorization()->employee->email;
            $name_to = $armadaticket->current_authorization()->employee_name;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $armadaticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $armadaticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $name_to,
                'code' => $armadaticket->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            return back()->with('success', 'Berhasil memulai otorisasi pengadaan armada ' . $armadaticket->code . '. Otorisasi selanjutnya oleh ' . $armadaticket->current_authorization()->employee_name . $emailmessage);
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memulai otorisasi ' . $ex->getMessage());
        }
    }

    public function approveArmadaAuthorization(Request $request, $return_data_type = 'view')
    {
        // hanya untuk pengadaan baru
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            $current_authorization = $armadaticket->current_authorization();
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

            $current_authorization = $armadaticket->current_authorization();
            if ($current_authorization == null) {
                $armadaticket->status = 2;
                $message = "Silahkan melanjutkan ke Menu PR (Niaga).";
                $armadaticket->save();

                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Menyelesaikan Otorisasi Ticket Armada';
                $monitor->save();

                // ambil semua otorisasi
                $employee_ids = $armadaticket->authorizations->pluck('employee_id');
                $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
                $mail_to = $employee_emails->toArray();
                $name_to = 'Bapak / Ibu';

                $data = array(
                    'original_emails' => $mail_to,
                    'original_ccs' => $ccs ?? [],
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $armadaticket->code,
                    'attachments' => $attachments ?? []
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_approved'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }

                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Otorisasi pengadaan armada ' . $armadaticket->code . ' telah selesai. Dilanjutkan proses PR Manual ' . $message . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Otorisasi pengadaan armada ' . $armadaticket->code . ' telah selesai. Dilanjutkan proses PR Manual ' . $message . $emailmessage);
                }
            } else {
                $armadaticket->save();
                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Approve Otorisasi Ticket Armada';
                $monitor->save();

                $mail_to = $armadaticket->current_authorization()->employee->email;
                $name_to = $armadaticket->current_authorization()->employee_name;
                $data = array(
                    'original_emails' => [$mail_to],
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $armadaticket->code,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'ticketing_approval'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }

                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan approval otorisasi pengadaan armada ' . $armadaticket->code . '. Otorisasi selanjutnya oleh ' . $armadaticket->current_authorization()->employee_name . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan approval otorisasi pengadaan armada ' . $armadaticket->code . '. Otorisasi selanjutnya oleh ' . $armadaticket->current_authorization()->employee_name . $emailmessage);
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

    public function rejectArmadaAuthorization(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            $current_authorization = $armadaticket->current_authorization();
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
                $current_authorization->status = -1;
                $current_authorization->reject_notes = $request->reject_notes;
                $current_authorization->save();

                $armadaticket->status -= 1;
                $armadaticket->save();

                $employee_ids = $armadaticket->authorizations->pluck('employee_id');
                $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
                $mail_to = $employee_emails->toArray();
                $ccs = $armadaticket->purchasing_emails();
                $name_to = 'Bapak/Ibu';
                $data = array(
                    'original_emails' => $mail_to,
                    'original_ccs' => $ccs,
                    'transaction_type' => $armadaticket->type(),
                    'ticketing_type' => 'Armada',
                    'salespoint_name' => $armadaticket->salespoint->name,
                    'from' => Auth::user()->name,
                    'to' => $name_to,
                    'code' => $armadaticket->code,
                    'reason' => $request->reject_notes,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'ticketing_reject'));
                } catch (\Exception $ex) {
                    $emailflag = false;
                }
                $emailmessage = "";
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }

                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => 'Berhasil melakukan reject otorisasi pengadaan armada ' . $armadaticket->code . ' dengan alasan ' . $request->reject_notes . $emailmessage
                    ]);
                } else {
                    return back()->with('success', 'Berhasil melakukan reject otorisasi pengadaan armada ' . $armadaticket->code . ' dengan alasan ' . $request->reject_notes . $emailmessage);
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Gagal memulai otorisasi ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('success', 'Gagal memulai otorisasi ' . $ex->getMessage());
            }
        }
    }

    public function terminateArmadaTicket(Request $request)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            $isAllowed = false;
            if ($armadaticket->ticketing_type == 0 && $armadaticket->isNiaga == true) {
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

            $armadaticket->terminated_by = Auth::user()->id;
            $armadaticket->termination_reason = $request->reason;
            $armadaticket->status = -1;
            $armadaticket->save();

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Membatalkan Pengadaan Armada';
            $monitor->save();
            DB::commit();
            return redirect('/ticketing?menu=Armada')->with('success', 'Berhasil Membatalkan Pengadaan');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/ticketing?menu=Armada')->with('error', 'Gagal Membatalkan Pengadaan "' . $ex->getMessage() . '"');
        }
    }

    public function printFacilityForm($armada_ticket_code, $method = 'stream')
    {
        $armadaticket = ArmadaTicket::where('code', $armada_ticket_code)->first();
        try {
            $facility_form = $armadaticket->facility_form;
            if (!$facility_form) {
                return 'facility form for this ticket is unavailable';
            }
            if ($facility_form->status < 1) {
                return 'facility form unavaiable / incomplete';
            }

            $pdf = PDF::loadView('pdf.facilityformpdf', compact('facility_form'))->setPaper('a4', 'portrait');
            if ($method == 'stream') {
                return $pdf->stream('Form Fasilitas (' . $armada_ticket_code . ').pdf');
            } else if ($method == 'path') {
                $path = "/temporary/facilityform/Form Fasilitas (" . $armada_ticket_code . ").pdf";
                Storage::disk('public')->put($path, $pdf->output());
                return $path;
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak Form Fasilitas ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function printPerpanjanganForm($armada_ticket_code, $method = 'stream')
    {
        $armadaticket = ArmadaTicket::where('code', $armada_ticket_code)->first();
        try {
            $perpanjangan_form = $armadaticket->perpanjangan_form;
            if (!$perpanjangan_form) {
                return 'perpajangan form for this ticket is unavailable';
            }
            if ($perpanjangan_form->status < 1) {
                return 'perpanjangan form unavaiable / incomplete';
            }

            $pdf = PDF::loadView('pdf.perpanjanganformpdf', compact('perpanjangan_form'))->setPaper('a4', 'portrait');
            if ($method == 'stream') {
                return $pdf->stream('Form Perpanjangan (' . $armada_ticket_code . ').pdf');
            } else if ($method == 'path') {
                $path = "/temporary/perpanjanganform/Form Perpanjangan (" . $armada_ticket_code . ").pdf";
                Storage::disk('public')->put($path, $pdf->output());
                return $path;
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak Form Perpajngan ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function printMutasiForm($armada_ticket_code, $method = 'stream')
    {
        $armadaticket = ArmadaTicket::where('code', $armada_ticket_code)->first();
        try {
            $mutasi_form = $armadaticket->mutasi_form;
            if (!$mutasi_form) {
                return 'perpajangan form for this ticket is unavailable';
            }
            if ($mutasi_form->status < 1) {
                return 'mutasi form unavaiable / incomplete';
            }

            $pdf = PDF::loadView('pdf.mutasiformpdf', compact('mutasi_form'))->setPaper('a4', 'portrait');
            if ($method == 'stream') {
                return $pdf->stream('Form Mutasi (' . $armada_ticket_code . ').pdf');
            } else if ($method == 'path') {
                $path = "/temporary/mutasiform/Form Mutasi (" . $armada_ticket_code . ").pdf";
                Storage::disk('public')->put($path, $pdf->output());
                return $path;
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak Form Mutasi ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function uploadBAPerpanjangan(Request $request)
    {
        try {
            $armada_ticket = ArmadaTicket::where('code', $request->ticket_code)->first();
            if (!isset($armada_ticket)) {
                throw new \Exception('Tiket armada ditemukan untuk kode ' . $request->ticket_code);
            } else {
                DB::beginTransaction();
                $new_open_request                  = new TicketingBlockOpenRequest;
                $new_open_request->ticket_code     = $request->ticket_code;
                $new_open_request->po_number       = $request->po_number;


                $extension = pathinfo($request->file('ba_file')->getClientOriginalName(), PATHINFO_EXTENSION);
                $filepath = '/attachments/ticketing/armada/' . $armada_ticket->code . '/BA_PERPANJANGAN_ARMADA_' . now()->format('YmdHi') . $extension;
                $file = pathinfo($filepath);
                $path = $request->file('ba_file')->storeAs($file['dirname'], $file['basename'], 'public');
                $new_open_request->ba_file_path    = $path;
                // $new_open_request->notes        =   null;
                $new_open_request->created_by      = Auth::user()->id;
                $new_open_request->save();

                // TODO send email untuk semua approver di perpanjangan form
                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armada_ticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Upload BA Perpanjangan Armada';
                $monitor->save();

                DB::commit();
                return back()->with('success', 'Berhasil Upload BA Perpanjangan. Proses ticketing dapat dilanjutkan setelah di konfirmasi oleh PCM');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Upload BA Perpanjangan (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }

    public function setMutasiLocation(Request $request)
    {
        try {
            DB::beginTransaction();
            $armadaticket = ArmadaTicket::findOrFail($request->armada_ticket_id);
            $armadaticket->mutation_salespoint_id = $request->to_salespoint_id;
            $armadaticket->save();

            $to_salespoint = SalesPoint::findOrFail($request->to_salespoint_id);

            $monitor                        = new ArmadaTicketMonitoring;
            $monitor->armada_ticket_id      = $armadaticket->id;
            $monitor->employee_id           = Auth::user()->id;
            $monitor->employee_name         = Auth::user()->name;
            $monitor->message               = 'Mutasi perpanjangan PO ke ' . $to_salespoint->name;
            $monitor->save();
            DB::commit();
            return back()->with('success', 'Berhasil Setting Mutasi lokasi perpanjangan ke ' . $to_salespoint->name);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Setting Mutasi lokasi perpanjangan (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }
}
