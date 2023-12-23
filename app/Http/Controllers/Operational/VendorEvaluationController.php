<?php

namespace App\Http\Controllers\Operational;

use DB;
use Auth;
use Mail;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Employee;
use PDF;
use App\Models\SalesPoint;
use Illuminate\Support\Str;
use App\Models\ArmadaTicket;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Mail\NotificationMail;
use App\Models\SecurityTicket;
use App\Models\VendorEvaluation;
use App\Http\Controllers\Controller;
use App\Models\VendorEvaluationDetail;
use App\Models\VendorEvaluationAuthorization;

class VendorEvaluationController extends Controller
{
    public function vendorEvaluationView(Request $request)
    {
        $status_not_active = [3, 4];
        $status_active = [0, 1, 2];

        if ($request->input('status') == 3) {
            $vendors = VendorEvaluation::with('vendor_evaluation_authorizations')
                ->whereIn('status', $status_not_active)
                ->get()
                ->sortByDesc('created_at');
        } else {
            $vendors = VendorEvaluation::with('vendor_evaluation_authorizations')
                ->whereIn('status', $status_active)
                ->get()
                ->sortByDesc('created_at');
        }

        return view('Operational.vendorevaluation', compact('vendors'));
    }

    public function addVendorEvaluation(Request $request)
    {
        try {
            // validasi ticketing block
            $newrequest = new Request;
            $newrequest->replace([
                'type' => "vendor_evaluation",
                'block_type' => "block_day",
            ]);
            $response = app('App\Http\Controllers\Operational\TicketingBlockController')->checkTicketingAvailable($newrequest);
            $responseData = $response->getData();
            if ($responseData->error) {
                return back()->with('error', '' . ($responseData->message));
            }

            //validasi kalo data sama di bulan sama tidak bisa buat lagi
            $start_date = empty($request->start_periode_penilaian) ? date('Y-m-01', strtotime("-1 months")) : $request->start_periode_penilaian;
            $end_date = empty($request->end_periode_penilaian) ? date('Y-m-t', strtotime("-1 months")) : $request->end_periode_penilaian;
            $validation_data_exist = VendorEvaluation::where('vendor', '=', $request->vendor)
                ->where('salespoint_id', '=', $request->salespoint_id)
                ->where('status', '!=', 3)
                ->where('status', '!=', 4)
                ->where('start_periode_penilaian', '=', $start_date)
                ->where('end_periode_penilaian', '=', $end_date)
                ->get();

            if ($validation_data_exist->count()) {
                return back()->with('error', 'Gagal membuat tiket. Data sudah ada');
            }

            DB::beginTransaction();
            $vendor_evaluation = DB::table('vendor_evaluations');

            $vendor_evaluation                          = new VendorEvaluation;
            $vendor_evaluation->vendor                  = $request->vendor;
            $vendor_evaluation->salespoint_id           = $request->salespoint_id;
            $vendor_evaluation->created_by              = Auth::user()->id;
            $vendor_evaluation->start_periode_penilaian = $start_date;
            $vendor_evaluation->end_periode_penilaian   = $end_date;
            $vendor_evaluation->status = 1;
            $vendor_evaluation->save();

            $code_type = "P04";

            // ambil kode inisial salespoint
            $code_salespoint_initial = strtoupper($vendor_evaluation->salespoint->initial);

            // ambil jumlah urutan ticketing d terkait dalam bulan dan tahun ini
            $armada_ticket_count = ArmadaTicket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->withTrashed()->count();

            $security_ticket_count = SecurityTicket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->withTrashed()->count();

            $barang_ticket_count = Ticket::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->where('status', '>', 0)->withTrashed()->count();

            $vendor_evaluation_ticket_count = VendorEvaluation::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->where('status', '>', 0)->count();

            $code_total_count = $armada_ticket_count + $security_ticket_count + $barang_ticket_count + $vendor_evaluation_ticket_count;
            do {
                $code = $code_type . "-" . $code_salespoint_initial . "-" . now()->translatedFormat('dmy') . str_repeat("0", 4 - strlen($code_total_count + 1)) . ($code_total_count + 1);
                $code_total_count++;
                $checkbarang = Ticket::where('code', $code)->first();
                $checkarmada = ArmadaTicket::where('code', $code)->first();
                $checksecurity = SecurityTicket::where('code', $code)->first();
                $checkvendorevaluation = SecurityTicket::where('code', $code)->first();
                ($checkbarang != null || $checkarmada != null || $checksecurity != null) ? $flag = false : $flag = true;
            } while (!$flag);
            $vendor_evaluation->code = $code;
            $vendor_evaluation->save();
            DB::commit();
            return redirect('/vendor-evaluation/' . $vendor_evaluation->code)->with('success', 'Berhasil menambah ticket. Silahkan Melakukan Penilaian dan Otorisasi');
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('error', 'Gagal membuat tiket. Harap mencoba kembali"' . $ex->getMessage() . '"');
        }
    }

    public function createVendorEvaluation(Request $request)
    {
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
        $available_salespoints = SalesPoint::whereIn('id', $user_location_access)->get();
        $available_salespoints = $available_salespoints->groupBy('region');
        $indirect_salespoints = SalesPoint::where('region', 19)->get();

        return view('Operational.vendorevaluationform', compact('available_salespoints', 'indirect_salespoints'));

        return back()->with('error', 'Terjadi Kesalahan silahkan mencoba lagi');
    }

    public function vendorEvaluationDetail($code)
    {
        $vendors = VendorEvaluation::with('vendor_evaluation_detail', 'vendor_evaluation_authorizations')->where('code', $code)->first();
        $salespoint = SalesPoint::find($vendors->salespoint_id);
        $authorizations = Authorization::whereIn('salespoint_id', $salespoint->salespoint_id_list())->where('form_type', 13)->get();

        return view('Operational.vendorevaluationdetail', compact('vendors', 'salespoint', 'authorizations'));
    }

    public function addVendorEvaluationDetail(Request $request, $id)
    {
        $vendor_evaluation = VendorEvaluation::findOrFail($request->id);

        if ($request->submit == 'Submit') {
            $this->addNewVendorEvaluationDetail($request, $id);
            return redirect('/vendor-evaluation/')->with('success', 'Berhasil membuat form evaluasi vendor. Silahkan Menunggu Otorisasi');
        } elseif ($request->submit == 'Revisi Data') {
            $this->updateVendorEvaluationDetail($request, $id);
            return redirect('/vendor-evaluation/' . $vendor_evaluation->code)->with('success', 'Berhasil update data evaluasi vendor.');
        } elseif ($request->submit == 'Approval Ulang') {
            $this->ApproveRevisionVendorEvaluation($request, $id);
            return redirect('/vendor-evaluation/')->with('success', 'Berhasil otorisasi ulang evaluasi vendor. Silahkan Menunggu Otorisasi');
        }
    }

    public function updateVendorEvaluationDetail(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $vendor_evaluation_detail = DB::table('vendor_evaluation_detail');
            $vendor_evaluation                                                  = VendorEvaluation::findOrFail($request->id);
            $vendor_evaluation_detail                                           = VendorEvaluationDetail::where('vendor_evaluation_id', $vendor_evaluation->id)->first();

            $vendor_evaluation_detail->vendor_evaluation_id                     = $id;
            $vendor_evaluation_detail->harga_score                              = $request->harga_score;
            $vendor_evaluation_detail->treatment_plan_score                     = $request->treatment_plan_score;
            $vendor_evaluation_detail->pelayanan_rentokill_score                = $request->pelayanan_rentokill_score;
            $vendor_evaluation_detail->laporan_score                            = $request->laporan_score;
            $vendor_evaluation_detail->kelengkapan_adm_score                    = $request->kelengkapan_adm_score;
            $vendor_evaluation_detail->waktu_penilaian_score                    = $request->waktu_penilaian_score;
            $vendor_evaluation_detail->koordinasi_dan_komunikasi_score          = $request->koordinasi_dan_komunikasi_score;
            $vendor_evaluation_detail->pelayanan_cit_score                      = $request->pelayanan_cit_score;
            $vendor_evaluation_detail->kuantitas_score                          = $request->kuantitas_score;
            $vendor_evaluation_detail->kualitas_score                           = $request->kualitas_score;
            $vendor_evaluation_detail->waktu_score                              = $request->waktu_score;
            $vendor_evaluation_detail->pelayanan_sicepat_score                  = $request->pelayanan_sicepat_score;
            $vendor_evaluation_detail->harga_score_reason                       = $request->harga_score_reason;
            $vendor_evaluation_detail->treatment_plan_score_reason              = $request->treatment_plan_score_reason;
            $vendor_evaluation_detail->pelayanan_rentokill_reason               = $request->pelayanan_rentokill_reason;
            $vendor_evaluation_detail->laporan_score_reason                     = $request->laporan_score_reason;
            $vendor_evaluation_detail->kelengkapan_adm_score_reason             = $request->kelengkapan_adm_score_reason;
            $vendor_evaluation_detail->waktu_penilaian_score_reason             = $request->waktu_penilaian_score_reason;
            $vendor_evaluation_detail->koordinasi_dan_komunikasi_score_reason   = $request->koordinasi_dan_komunikasi_score_reason;
            $vendor_evaluation_detail->pelayanan_cit_score_reason               = $request->pelayanan_cit_score_reason;
            $vendor_evaluation_detail->kuantitas_score_reason                   = $request->kuantitas_score_reason;
            $vendor_evaluation_detail->kualitas_score_reason                    = $request->kualitas_score_reason;
            $vendor_evaluation_detail->waktu_score_reason                       = $request->waktu_score_reason;
            $vendor_evaluation_detail->pelayanan_sicepat_score_reason           = $request->pelayanan_sicepat_score_reason;
            $vendor_evaluation_detail->save();

            $vendor_evaluation->revision_by                                     = Auth::user()->id;
            $vendor_evaluation->revision_at                                     = now()->format('Y-m-d');
            $vendor_evaluation->save();
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('error', 'Gagal revisi data evaluasi vendor. Harap mencoba kembali"' . $ex->getMessage() . '"');
        }
    }

    public function ApproveRevisionVendorEvaluation(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $finished_date = null;
            $rejected_at = null;
            $rejected_by = null;
            $reason = null;

            $vendor_evaluation                                          = VendorEvaluation::findOrFail($request->id);
            $vendor_evaluation->status                                  = 2;
            $vendor_evaluation->rejected_by                             = $rejected_by;
            $vendor_evaluation->rejected_at                             = $rejected_at;
            $vendor_evaluation->finished_date                           = $finished_date;
            $vendor_evaluation->reason                                  = $reason;
            $vendor_evaluation->save();

            $authorizations = Authorization::find($request->authorization);
            if (isset($authorizations)) {
                foreach ($authorizations->authorization_detail as $detail) {
                    $new_vendor_evaluation_authorization                         = new VendorEvaluationAuthorization;
                    $new_vendor_evaluation_authorization->vendor_evaluation_id   = $id;
                    $new_vendor_evaluation_authorization->employee_id            = $detail->employee_id;
                    $new_vendor_evaluation_authorization->employee_name          = $detail->employee->name;
                    $new_vendor_evaluation_authorization->as                     = $detail->sign_as;
                    $new_vendor_evaluation_authorization->employee_position      = $detail->employee_position->name;
                    $new_vendor_evaluation_authorization->level                  = $detail->level;
                    $new_vendor_evaluation_authorization->save();
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('error', 'Gagal membuat tiket. Harap mencoba kembali"' . $ex->getMessage() . '"');
        }
    }

    public function addNewVendorEvaluationDetail(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $vendor_evaluation_detail                                           = DB::table('vendor_evaluation_detail');

            $vendor_evaluation_detail                                           = new VendorEvaluationDetail;
            $vendor_evaluation_detail->vendor_evaluation_id                     = $id;
            $vendor_evaluation_detail->harga_score                              = $request->harga_score;
            $vendor_evaluation_detail->treatment_plan_score                     = $request->treatment_plan_score;
            $vendor_evaluation_detail->pelayanan_rentokill_score                = $request->pelayanan_rentokill_score;
            $vendor_evaluation_detail->laporan_score                            = $request->laporan_score;
            $vendor_evaluation_detail->kelengkapan_adm_score                    = $request->kelengkapan_adm_score;
            $vendor_evaluation_detail->waktu_penilaian_score                    = $request->waktu_penilaian_score;
            $vendor_evaluation_detail->koordinasi_dan_komunikasi_score          = $request->koordinasi_dan_komunikasi_score;
            $vendor_evaluation_detail->pelayanan_cit_score                      = $request->pelayanan_cit_score;
            $vendor_evaluation_detail->kuantitas_score                          = $request->kuantitas_score;
            $vendor_evaluation_detail->kualitas_score                           = $request->kualitas_score;
            $vendor_evaluation_detail->waktu_score                              = $request->waktu_score;
            $vendor_evaluation_detail->pelayanan_sicepat_score                  = $request->pelayanan_sicepat_score;
            $vendor_evaluation_detail->harga_score_reason                       = $request->harga_score_reason;
            $vendor_evaluation_detail->treatment_plan_score_reason              = $request->treatment_plan_score_reason;
            $vendor_evaluation_detail->pelayanan_rentokill_reason               = $request->pelayanan_rentokill_reason;
            $vendor_evaluation_detail->laporan_score_reason                     = $request->laporan_score_reason;
            $vendor_evaluation_detail->kelengkapan_adm_score_reason             = $request->kelengkapan_adm_score_reason;
            $vendor_evaluation_detail->waktu_penilaian_score_reason             = $request->waktu_penilaian_score_reason;
            $vendor_evaluation_detail->koordinasi_dan_komunikasi_score_reason   = $request->koordinasi_dan_komunikasi_score_reason;
            $vendor_evaluation_detail->pelayanan_cit_score_reason               = $request->pelayanan_cit_score_reason;
            $vendor_evaluation_detail->kuantitas_score_reason                   = $request->kuantitas_score_reason;
            $vendor_evaluation_detail->kualitas_score_reason                    = $request->kualitas_score_reason;
            $vendor_evaluation_detail->waktu_score_reason                       = $request->waktu_score_reason;
            $vendor_evaluation_detail->pelayanan_sicepat_score_reason           = $request->pelayanan_sicepat_score_reason;

            $vendor_evaluation_detail->save();

            $vendor_evaluation                                                  = VendorEvaluation::findOrFail($request->id);
            $vendor_evaluation->status                                          = 2;
            $vendor_evaluation->save();

            $authorizations = Authorization::find($request->authorization);
            if (isset($authorizations)) {
                foreach ($authorizations->authorization_detail as $detail) {
                    $new_vendor_evaluation_authorization                         = new VendorEvaluationAuthorization;
                    $new_vendor_evaluation_authorization->vendor_evaluation_id   = $id;
                    $new_vendor_evaluation_authorization->employee_id            = $detail->employee_id;
                    $new_vendor_evaluation_authorization->employee_name          = $detail->employee->name;
                    $new_vendor_evaluation_authorization->as                     = $detail->sign_as;
                    $new_vendor_evaluation_authorization->employee_position      = $detail->employee_position->name;
                    $new_vendor_evaluation_authorization->level                  = $detail->level;
                    $new_vendor_evaluation_authorization->save();
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex);
            DB::rollback();
            return back()->with('error', 'Gagal membuat tiket. Harap mencoba kembali"' . $ex->getMessage() . '"');
        }
    }

    public function printVendorEvaluation(Request $request, $code)
    {
        try {
            $vendors = VendorEvaluation::with('vendor_evaluation_detail', 'vendor_evaluation_authorizations')->where('code', $code)->first();
            $pdf = PDF::loadView('pdf.vendorevaluationpdf', compact('vendors'))->setPaper('legal', 'portrait');
            return $pdf->stream('Vendor Evaluation (' . $code . ').pdf');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak Vendor Evaluation ' . $ex->getMessage() . $ex->getLine());
        }
    }

    public function approveVendorEvaluation(Request $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $vendor_evaluation = VendorEvaluation::findOrFail($request->id);
            $authorization = $vendor_evaluation->current_authorization();
            if ($authorization->employee_id == Auth::user()->id) {

                // set status jadi approve
                $authorization->status = 1;
                $authorization->save();

                // mail ke otorisasi selanjutnya
                if ($vendor_evaluation->current_authorization() != null) {
                    $mail_to = $vendor_evaluation->current_authorization()->employee->email;
                    $name_to = $vendor_evaluation->current_authorization()->employee->name;
                    $data = array(
                        'original_emails' => [$mail_to],
                        'transaction_type' => 'Vendor Evaluation',
                        'ticketing_type' => 'Vendor Evaluation',
                        'salespoint_name' => $vendor_evaluation->salespoint->name,
                        'from' => Auth::user()->name,
                        'to' => $name_to,
                        'code' => $vendor_evaluation->code,
                    );
                    if (config('app.env') == 'local') {
                        $mail_to = [config('mail.testing_email')];
                    }

                    try {
                        Mail::to($mail_to)->send(new NotificationMail($data, 'vendor_evaluation_approval'));
                    } catch (\Exception $ex) {
                        $emailflag = false;
                    }
                    if (!$emailflag) {
                        $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                    }
                }
                $this->checkVendorEvaluationApproval($vendor_evaluation->id);
                $vendor_evaluation->refresh();
                $current_authorization = $vendor_evaluation->current_authorization();
                if ($current_authorization) {
                    $returnmessage = 'Berhasil melakukan approve, Otorisasi selanjutnya oleh ' . $current_authorization->employee_name;
                } else {
                    $returnmessage = 'Approval terkait Vendor Evaluation dengan kode ticket ' . $vendor_evaluation->code . ' sudah full approval. (Status tiket saat ini : ' . $vendor_evaluation->status() . ')';
                }
                DB::commit();
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => false,
                        "message" => $returnmessage . $emailmessage
                    ]);
                } else {
                    return back()->with('success', $returnmessage . $emailmessage);
                }
            } else {
                if ($return_data_type == 'api') {
                    return response()->json([
                        "error" => true,
                        "message" => 'Vendor evaluasi sudah di approve sebelumnya'
                    ]);
                } else {
                    return back()->with('error', 'Vendor evaluasi sudah di approve sebelumnya');
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal melakukan approve Vendor Evaluation (' . $ex->getMessage() . ') [' . $ex->getLine() . ']'
                ]);
            } else {
                return back()->with('error', 'Gagal melakukan approve Vendor Evaluation (' . $ex->getMessage() . ') [' . $ex->getLine() . ']');
            }
        }
    }

    public function checkVendorEvaluationApproval($id)
    {
        try {
            $vendor_evaluation = VendorEvaluation::findOrFail($id);
            $flag = true;
            foreach ($vendor_evaluation->vendor_evaluation_authorizations as $authorization) {
                if ($authorization->status != 1) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $vendor_evaluation->status = 3;
                $vendor_evaluation->finished_date = now()->format('Y-m-d');
                $vendor_evaluation->save();
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Approval Vendor Evaluation checker error please contact admin ' . $ex->getMessage());
        }
    }

    public function rejectVendorEvaluation(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $vendor_evaluation = VendorEvaluation::findOrFail($request->id);
            $authorization = $vendor_evaluation->current_authorization();
            $finished_date = null;
            // reject vendor evaluation
            $vendor_evaluation->status         = 0;
            $vendor_evaluation->rejected_by    = Auth::user()->id;
            $vendor_evaluation->rejected_at    = now()->format('Y-m-d');
            $vendor_evaluation->reason         = $request->reason;
            $vendor_evaluation->finished_date  = $finished_date;
            $vendor_evaluation->save();

            // mulai authorization dari awal lagi
            foreach ($vendor_evaluation->vendor_evaluation_authorizations as $authorization) {
                $authorization->delete();
            }

            // ambil semua otorisasi
            $employee_ids = $authorization->pluck('employee_id');
            $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
            $mail_to = $employee_emails->toArray();
            $data = array(
                'original_emails' => $mail_to,
                'transaction_type' => 'Vendor Evaluation',
                'ticketing_type' => 'Vendor Evaluation',
                'salespoint_name' => $vendor_evaluation->salespoint->name,
                'from' => Auth::user()->name,
                'to' => 'Bapak/Ibu',
                'code' => $vendor_evaluation->code,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            $emailflag = true;
            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'vendor_evaluation_reject'));
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
                    "message" => 'Berhasil Reject Vendor Evaluation' . $emailmessage
                ]);
            } else {
                return redirect('/vendor-evaluation')->with('success', 'Berhasil Reject Vendor Evaluation' . $emailmessage);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal Reject Vendor Evaluation ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal Reject Vendor Evaluation ' . $ex->getMessage());
            }
        }
    }

    public function terminatedVendorEvaluation(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $vendor_evaluation = VendorEvaluation::findOrFail($request->id);
            // reject vendor evaluation
            $vendor_evaluation->status         = 4;
            $vendor_evaluation->rejected_by    = Auth::user()->id;
            $vendor_evaluation->rejected_at    = now()->format('Y-m-d');
            $vendor_evaluation->reason         = $request->reason;
            $vendor_evaluation->save();
            DB::commit();

            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil Membatalkan Vendor Evaluation'
                ]);
            } else {
                return redirect('/vendor-evaluation')->with('success', 'Berhasil Membatalkan Vendor Evaluation');
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal Membatalkan Vendor Evaluation ' . $ex->getMessage()
                ]);
            } else {
                return back()->with('error', 'Gagal Membatalkan Vendor Evaluation ' . $ex->getMessage());
            }
        }
    }
}
