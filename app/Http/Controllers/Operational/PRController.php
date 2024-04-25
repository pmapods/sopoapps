<?php

namespace App\Http\Controllers\Operational;

use DB;
use PDF;
use Auth;
use Mail;
use Storage;
use App\Models\Pr;
use Carbon\Carbon;

use App\Models\Ticket;
use App\Models\IssuePO;
use App\Models\Employee;
use App\Models\PrDetail;
use App\Models\SalesPoint;
use App\Models\TicketItem;
use Illuminate\Support\Str;
use App\Models\ArmadaTicket;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Mail\NotificationMail;
use App\Models\SecurityTicket;
use App\Models\PrAuthorization;
use App\Models\EmployeePosition;
use App\Models\TicketMonitoring;
use App\Models\AuthorizationDetail;
use App\Models\TicketAuthorization;
use App\Http\Controllers\Controller;

use App\Models\ArmadaTicketMonitoring;
use App\Models\SecurityTicketMonitoring;
use App\Models\ArmadaTicketAuthorization;
use App\Models\SecurityTicketAuthorization;

class PRController extends Controller
{
    public function prView(Request $request)
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $tickets = array();
        if ($request->input('status') == -1) {
            $pengadaantickets = Ticket::where('status', '>', 5)
                ->whereIn('salespoint_id', $salespoint_ids)
                ->where('item_type', '!=', 4)
                ->get();

            // pr hanya untuk pengadaan armada & niaga, non niaga & pr manual
            $armadatickets = ArmadaTicket::where('status', '>', 4)
                ->whereIn('salespoint_id', $salespoint_ids)
                ->where('ticketing_type', 0)
                ->whereIn('isNiaga', [0, 1])
                ->where('authorization_type', 0)
                ->get();

            // pr hanya untuk pengadaan security dan pengadaan lembur
            $securitytickets = SecurityTicket::where('status', '>', 4)
                ->whereIn('salespoint_id', $salespoint_ids)
                ->whereIn('ticketing_type', [0, 4])
                ->get();
        } else {
            $pengadaantickets = Ticket::whereIn('status', [3, 4, 5])
                ->whereIn('salespoint_id', $salespoint_ids)
                ->get();

            // pr hanya untuk pengadaan armada & niaga
            $armadatickets = ArmadaTicket::whereIn('status', [2, 3, 4])
                ->whereIn('salespoint_id', $salespoint_ids)
                ->where('ticketing_type', 0)
                ->whereIn('isNiaga', [0, 1])
                ->where('authorization_type', 0)
                ->get();

            // pr hanya untuk pengadaan security dan pengadaan lembur
            $securitytickets = SecurityTicket::whereIn('status', [2, 3, 4])
                ->whereIn('salespoint_id', $salespoint_ids)
                ->whereIn('ticketing_type', [0, 4])
                ->get();
        }
        return view('Operational.pr', compact('pengadaantickets', 'armadatickets', 'securitytickets'));
    }

    public function prDetailView($ticket_code)
    {
        try {
            $ticket = Ticket::where('code', $ticket_code)->first();
            $armadaticket = ArmadaTicket::where('code', $ticket_code)->first();
            $securityticket = SecurityTicket::where('code', $ticket_code)->first();
            if (!$ticket && !$armadaticket && !$securityticket) {
                return back()->with('error', "Ticket tidak ditemukan");
            }
            $user_location_access  = Auth::user()->location_access->pluck('salespoint_id');
            if ($ticket != null) {
                // validate budget detail has akses area
                if (!$user_location_access->contains($ticket->salespoint_id)) {
                    return redirect('/pr')->with('error', 'Anda tidak memiliki akses untuk PR berikut. Tidak memiliki akses salespoint "' . $ticket->salespoint->name . '"');
                }
                if ($ticket->status < 3) {
                    return redirect("/pr")->with('error', "Pengadaan dengan kode " . $ticket->code . " belum dapat melakukan proses PR");
                }
                $authorizations = Authorization::whereIn('salespoint_id', $ticket->salespoint->salespoint_id_list())->where('form_type', 2)->get();

                if ($ticket->status < 5) {
                    return view('Operational.prdetail', compact('ticket', 'authorizations'));
                } else {
                    if ($ticket->pr) {
                        // check apakah ada request
                        $newrequest = new Request([
                            "ticket_code" => $ticket->code
                        ]);
                        // TODO DEV FEATURE WEB ASSET
                        // $response = $this->getRequestAssetNumberStatus($newrequest);
                        // $asset_number_request_data  = $response->getData();
                        // END TODO
                        $asset_number_request_data  = null;
                        // if($asset_number_request_data->error == true){
                        //     $asset_number_request_data = null;
                        // }else{

                        // }
                        return view('Operational.prdetailform', compact('ticket', 'authorizations', 'asset_number_request_data'));
                    } else {
                        return back()->with('error', 'PR gagal dibuka');
                    }
                }
            }
            if ($armadaticket != null) {
                // validate budget detail has akses area
                if (!$user_location_access->contains($armadaticket->salespoint_id)) {
                    return redirect('/pr')->with('error', 'Anda tidak memiliki akses untuk PR berikut. Tidak memiliki akses salespoint "' . $armadaticket->salespoint->name . '"');
                }
                $authorizations = Authorization::whereIn('salespoint_id', $armadaticket->salespoint->salespoint_id_list())->where('form_type', 2)->get();
                return view('Operational.Armada.armadaprdetail', compact('armadaticket', 'authorizations'));
            }
            if ($securityticket != null) {
                $authorizations = Authorization::whereIn('salespoint_id', $securityticket->salespoint->salespoint_id_list())->where('form_type', 2)->get();
                // validate budget detail has akses area
                if (!$user_location_access->contains($securityticket->salespoint_id)) {
                    return redirect('/pr')->with('error', 'Anda tidak memiliki akses untuk PR berikut. Tidak memiliki akses salespoint "' . $securityticket->salespoint->name . '"');
                }
                if ($securityticket->type() == "Pengadaan") {
                    return view('Operational.Security.securityprdetail', compact('securityticket', 'authorizations'));
                }
                if ($securityticket->type() == "Pengadaan Lembur") {
                    return view('Operational.Security.securitybaupload', compact('securityticket'));
                }
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'gagal membuka detil PR ' . $ex->getMessage());
        }
    }

    public function addNewPR(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        $messages = [
            'pr_authorization_id.required'  => 'Otorisasi PR wajib dipilih',
            'updated_at.required' => 'Updated at data not received, Please contact admin'
        ];
        $validated = $request->validate([
            'pr_authorization_id' => 'required',
            'updated_at' => 'required'
        ], $messages);
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->ticket_id);
            $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
            $securityticket = SecurityTicket::find($request->security_ticket_id);
            if ($ticket != null) {
                if (new Carbon($ticket->updated_at) != new Carbon($request->updated_at)) {
                    return back()->with('error', 'Terdapat update data pada ticket. Silahkan coba lagi');
                }
                $ticket->status = 4;
                $ticket->save();

                if ($request->pr_id != -1) {
                    $pr = Pr::findOrFail($request->pr_id);
                    $pr->rejected_by = null;
                    $pr->reject_reason = null;
                    $pr->status = 0;
                    $pr->created_at = now()->format('Y-m-d H:i:s');
                } else {
                    $pr = new Pr;
                }
                $pr->isBudget     = ($ticket->budget_type == 0) ? true : false;
                $pr->ticket_id    = $ticket->id;
                $pr->created_by   = Auth::user()->id;
                $pr->save();

                $custom_settings = json_decode($ticket->custom_settings);
                if ($ticket->custom_settings != null && $custom_settings->ticket_name == 'Ekspedisi Unit COP') {
                    $dibuatoleh = TicketAuthorization::find($request->dibuat_oleh_ticketauthorization_id);
                } else {
                    $dibuatoleh = TicketAuthorization::find($request->dibuat_oleh_ticketauthorization_id);
                }

                // if ($dibuatoleh->employee_id != Auth::user()->id) {
                //     throw new \Exception('Matriks approval "Dibuat Oleh" (' . $dibuatoleh->employee->name . ') tidak sesuai dengan akun login saat ini (' . Auth::user()->name . ')');
                // }

                // tambahkan otorisasi dari area
                $authorization                     = new PrAuthorization;
                $authorization->pr_id              = $pr->id;

                $custom_settings = json_decode($ticket->custom_settings);
                if ($ticket->custom_settings != null && $custom_settings->ticket_name == 'Ekspedisi Unit COP') {
                    $authorization->employee_id        = $dibuatoleh->employee->id;
                    $authorization->employee_name      = $dibuatoleh->employee_name;
                    $authorization->as                 = 'Dibuat Oleh (gol 5B)';
                    $authorization->employee_position  = $dibuatoleh->employee_position;
                } else {
                    $authorization->employee_id        = $dibuatoleh->employee->id;
                    $authorization->employee_name      = $dibuatoleh->employee_name;
                    $authorization->as                 = 'Dibuat Oleh (min gol 5A)';
                    $authorization->employee_position  = $dibuatoleh->employee_position;
                }

                $authorization->level              = 1;
                $authorization->save();

                $authorization = Authorization::find($request->pr_authorization_id);
                foreach ($authorization->authorization_detail as $author) {
                    $authorization                     = new PrAuthorization;
                    $authorization->pr_id              = $pr->id;
                    $authorization->employee_id        = $author->employee_id;
                    $authorization->employee_name      = $author->employee->name;
                    $authorization->as                 = $author->sign_as;
                    $authorization->employee_position  = $author->employee_position->name;
                    $authorization->level              = 1 + $author->level;
                    $authorization->save();
                }

                foreach ($request->item as $key => $item) {
                    $ticketitem = TicketItem::find($item["ticket_item_id"]);
                    $detail                     = new PrDetail;
                    $detail->pr_id              = $pr->id;
                    $detail->ticket_item_id     = $item["ticket_item_id"];
                    $detail->name               = $ticketitem->name;
                    $detail->qty                = $item["qty"];
                    $detail->uom                = $item["uom"];

                    // cara menentukan asset atau non asset kalau dia budget pricing / area cek ke master, selainnya (ho dan maintenance) cek apakah diatas atau dibawah sejuta
                    $isAsset = null;
                    $ticket_item = TicketItem::find($item["ticket_item_id"]);
                    if ($ticket_item->budget_pricing_id != null) {
                        if ($ticket_item->budget_pricing_id == -1) {
                            // pest control / merchandiser bukan asset
                            $isAsset = false;
                        } else {
                            $isAsset = $ticket_item->budget_pricing->isAsset;
                        }
                    } else {
                        if (intval($item["price"]) < 1000000) {
                            $isAsset = false;
                        } else {
                            $isAsset = true;
                        }
                    }
                    $detail->isAsset = $isAsset;
                    do {
                        $uuid = Str::uuid()->toString();
                        $flag = true;
                        if (PrDetail::where('asset_number_token', $uuid)->first()) {
                            $flag = false;
                        }
                    } while (!$flag);
                    $detail->asset_number_token = $uuid;
                    $detail->price              = $item["price"] ?? 0;
                    $detail->ongkir_price       = $item["ongkir_price"] ?? 0;
                    $detail->ongpas_price       = $item["ongpas_price"] ?? 0;
                    $detail->setup_date         = $item["setup_date"];
                    $detail->notes              = $item["notes"];
                    $detail->save();
                }

                $monitor = new TicketMonitoring;
                $monitor->ticket_id             = $ticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Menambahkan PR untuk di otorisasi';
                $monitor->save();

                $transactiontype = $ticket->request_type();
                $ticketingtype = 'Barang Jasa';
                $salespointname = $ticket->salespoint->name;
                $code = $ticket->code;
            }
            if ($armadaticket != null) {
                if (new Carbon($armadaticket->updated_at) != new Carbon($request->updated_at)) {
                    return back()->with('error', 'Terdapat update data pada ticket. Silahkan coba lagi');
                }
                $armadaticket->status = 3;
                $armadaticket->save();

                if ($request->pr_id != -1) {
                    $pr = Pr::findOrFail($request->pr_id);
                    $pr->rejected_by = null;
                    $pr->reject_reason = null;
                    $pr->status = 0;
                    $pr->created_at = now()->format('Y-m-d H:i:s');
                } else {
                    $pr = new Pr;
                }
                $pr->isBudget = true;
                $pr->armada_ticket_id    = $armadaticket->id;
                $pr->created_by   = Auth::user()->id;
                $pr->save();

                $dibuatoleh = ArmadaTicketAuthorization::find($request->dibuat_oleh_ticketauthorization_id);
                // if ($dibuatoleh->employee_id != Auth::user()->id) {
                //     throw new \Exception('Matriks approval "Dibuat Oleh" (' . $dibuatoleh->employee->name . ') tidak sesuai dengan akun login saat ini (' . Auth::user()->name . ')');
                // }

                // tambahkan otorisasi dari area
                $authorization                     = new PrAuthorization;
                $authorization->pr_id              = $pr->id;
                $authorization->employee_id        = $dibuatoleh->employee_id;
                $authorization->employee_name      = $dibuatoleh->employee_name;
                $authorization->as                 = 'Dibuat Oleh (min gol 5A)';
                $authorization->employee_position  = $dibuatoleh->employee_position;
                $authorization->level              = 1;
                $authorization->save();

                $authorization = Authorization::find($request->pr_authorization_id);
                foreach ($authorization->authorization_detail as $author) {
                    $authorization                     = new PrAuthorization;
                    $authorization->pr_id              = $pr->id;
                    $authorization->employee_id        = $author->employee_id;
                    $authorization->employee_name      = $author->employee->name;
                    $authorization->as                 = $author->sign_as;
                    $authorization->employee_position  = $author->employee_position->name;
                    $authorization->level              = 1 + $author->level;
                    $authorization->save();
                }

                $detail                     = new PrDetail;
                $detail->pr_id              = $pr->id;
                $detail->name               = $armadaticket->armada_type->name;
                $detail->qty                = 1;
                $detail->uom                = 'Unit';
                do {
                    $uuid = Str::uuid()->toString();
                    $flag = true;
                    if (PrDetail::where('asset_number_token', $uuid)->first()) {
                        $flag = false;
                    }
                } while (!$flag);
                $detail->asset_number_token = $uuid;
                $detail->price              = null;
                $detail->setup_date         = $request->setup_date;
                $detail->notes              = $request->notes;
                $detail->save();

                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Menambahkan PR untuk di otorisasi';
                $monitor->save();

                $transactiontype = $armadaticket->type();
                $ticketingtype = 'Armada';
                $salespointname = $armadaticket->salespoint->name;
                $code = $armadaticket->code;
            }
            if ($securityticket != null) {
                if (new Carbon($securityticket->updated_at) != new Carbon($request->updated_at)) {
                    throw new \Exception('error', 'Terdapat update data pada ticket. Silahkan coba lagi');
                }
                $securityticket->status = 3;
                $securityticket->save();

                if ($request->pr_id != -1) {
                    $pr = Pr::findOrFail($request->pr_id);
                    $pr->rejected_by = null;
                    $pr->reject_reason = null;
                    $pr->status = 0;
                    $pr->created_at = now()->format('Y-m-d H:i:s');
                } else {
                    $pr = new Pr;
                }
                $isBudget = true;
                // pengadaan lembur
                if ($securityticket->ticketing_type == 4) {
                    $isBudget = false;
                }
                $pr->isBudget = $isBudget;
                $pr->security_ticket_id    = $securityticket->id;
                $pr->created_by   = Auth::user()->id;
                $pr->save();

                $dibuatoleh = SecurityTicketAuthorization::find($request->dibuat_oleh_ticketauthorization_id);
                // if ($dibuatoleh->employee_id != Auth::user()->id) {
                //     throw new \Exception('Matriks approval "Dibuat Oleh" (' . $dibuatoleh->employee->name . ') tidak sesuai dengan akun login saat ini (' . Auth::user()->name . ')');
                // }

                // tambahkan otorisasi dari area
                $authorization                     = new PrAuthorization;
                $authorization->pr_id              = $pr->id;
                $authorization->employee_id        = $dibuatoleh->employee_id;
                $authorization->employee_name      = $dibuatoleh->employee_name;
                $authorization->as                 = 'Dibuat Oleh (min gol 5A)';
                $authorization->employee_position  = $dibuatoleh->employee_position;
                $authorization->level              = 1;
                $authorization->save();

                $authorization = Authorization::find($request->pr_authorization_id);
                foreach ($authorization->authorization_detail as $author) {
                    $authorization                     = new PrAuthorization;
                    $authorization->pr_id              = $pr->id;
                    $authorization->employee_id        = $author->employee_id;
                    $authorization->employee_name      = $author->employee->name;
                    $authorization->as                 = $author->sign_as;
                    $authorization->employee_position  = $author->employee_position->name;
                    $authorization->level              = 1 + $author->level;
                    $authorization->save();
                }

                $detail                     = new PrDetail;
                $detail->pr_id              = $pr->id;
                $detail->name               = 'Security';
                $detail->qty                = 1;
                $detail->uom                = '';
                do {
                    $uuid = Str::uuid()->toString();
                    $flag = true;
                    if (PrDetail::where('asset_number_token', $uuid)->first()) {
                        $flag = false;
                    }
                } while (!$flag);
                $detail->asset_number_token = $uuid;
                $detail->price              = null;
                $detail->setup_date         = $request->setup_date;
                $detail->notes              = $request->notes;
                $detail->save();

                $monitor = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $securityticket->id;
                $monitor->employee_id             = Auth::user()->id;
                $monitor->employee_name           = Auth::user()->name;
                $monitor->message                 = 'Menambahkan PR untuk di otorisasi';
                $monitor->save();

                $transactiontype            = $securityticket->type();
                $ticketingtype              = 'Security';
                $salespointname             = $securityticket->salespoint->name;
                $code                       = $securityticket->code;
            }

            $mail_to = $pr->current_authorization()->employee->email;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $transactiontype,
                'ticketing_type' => $ticketingtype,
                'salespoint_name' => $salespointname,
                'from' => Auth::user()->name,
                'to' => $pr->current_authorization()->employee->name,
                'code' => $code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'pr_approval'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            DB::commit();
            return back()->with('success', 'Berhasil menambakan PR, Silahkan melakukan proses otorisasi' . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Menambah PR ' . $ex->getMessage());
        }
    }

    public function approvePR(Request $request, $return_data_type = 'view')
    {
        try {
            DB::beginTransaction();
            $pr = Pr::findOrFail($request->pr_id);

            // check apakah otorisasi sesuai dengan akun login
            if ($pr->current_authorization()->employee->id == Auth::user()->id) {
                $authorization = $pr->current_authorization();
                $authorization->status = 1;
                $authorization->save();
                // jika pr barangjasa
                if ($pr->ticket != null) {
                    // jika otorisasi sudah lengkap  ubah status pr ke submit nomor asset
                    if ($pr->current_authorization() == null) {
                        $pr->status = 1;
                        $pr->save();
                    }
                    return $this->approvePRbarangjasa($pr, $request, $return_data_type);
                }
                // jika pr armada
                if ($pr->armada_ticket != null) {
                    // jika otorisasi sudah lengkap ubah status pr ke selesai
                    if ($pr->current_authorization() == null) {
                        $pr->status = 2;
                        $pr->save();
                    }
                    return $this->approvePRarmada($pr, $request, $return_data_type);
                }
                // jika pr security
                if ($pr->security_ticket != null) {
                    // jika otorisasi sudah lengkap ubah status pr ke selesai
                    if ($pr->current_authorization() == null) {
                        $pr->status = 2;
                        $pr->save();
                    }
                    return $this->approvePRsecurity($pr, $request, $return_data_type);
                }
            } else {
                throw new \Exception("Otorisasi saat ini dan akun login tidak sesuai");
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal approve PR ' . $ex->getMessage() . '(' . $ex->getLine() . ')'
                ]);
            } else {
                return back()->with('error', 'Gagal approve PR ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
            }
        }
    }

    public function approvePRbarangjasa($pr, $request, $return_data_type = 'view')
    {
        // validate apakah kedua bidding masih belum melewati expired date
        foreach ($pr->ticket->bidding as $bidding) {
            // hanya validasi jika memiliki expired date di biddingnya
            if ($bidding->expired_date != null) {
                // hari ini dah lewat dari expired date
                $expired_date = new Carbon($bidding->expired_date);
                if (now() > $expired_date) {
                    DB::rollback();
                    if ($return_data_type == 'api') {
                        return response()->json([
                            "error" => true,
                            "message" => "Bidding untuk produk " . $bidding->product_name . " telah expired pada " . $expired_date->translatedFormat('d F Y')
                        ]);
                    } else {
                        return back()->with('error', "Bidding untuk produk " . $bidding->product_name . " telah expired pada " . $expired_date->translatedFormat('d F Y'));
                    }
                }
            }
        }

        $emailflag = true;
        $emailmessage = "";
        $monitor                 = new TicketMonitoring;
        $monitor->ticket_id      = $pr->ticket->id;
        $monitor->employee_id    = Auth::user()->id;
        $monitor->employee_name  = Auth::user()->name;
        // jika pr selesai
        if ($pr->status == 1) {
            $ticket = $pr->ticket;
            // jika budget cek masing2 item di pr ke budget pricing apakah ada yang jenis assetnya "asset" kalo ada brarti butuh pengisian nomor asset
            // jika pr non budget otomatis butuh pengisian nomor asset / langsung proses PO
            $needassetnumberfill = false;
            if ($pr->isBudget == true) {
                foreach ($pr->pr_detail as $detail) {
                    $isAsset = false;
                    try {
                        $isAsset = $detail->isAsset;
                    } catch (\Throwable $e) {
                    }
                    if ($isAsset == true) {
                        $needassetnumberfill = true;
                    }
                }
            } else {
                $needassetnumberfill = true;
            }
            if ($needassetnumberfill) {
                $ticket->status = 5;
            } else {
                $ticket->status = 6;
                $pr->status = 2;
                $pr->save();
            }
            $ticket->save();

            $monitor->message        = 'Melakukan Otorisasi PR (otorisasi selesai)';
            $monitor->save();

            $mail_to = $pr->authorization_emails();
            $ccs = $ticket->additional_emails();
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->ticket->request_type(),
                'ticketing_type' => 'Barang Jasa',
                'salespoint_name' => $pr->ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => '',
                'code' => $pr->ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_approved'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            // kirim request ke web asset
            $sendrequestmessage = "";
            if ($needassetnumberfill) {
                $request = new Request;
                $request->replace([
                    'ticket_id' => $ticket->id,
                    'pr_id' => $pr->id
                ]);
                $response = $this->sendRequestAssetNumber($request);
                $data = $response->getData();
                if (!$data->error) {
                    $sendrequestmessage = "Request nomor asset berhasil dikirim ke web asset";
                } else {
                    $sendrequestmessage = "Request nomor asset gagal dikirim ke web asset. Anda dapat melakukan  kirim ulang request ke web asset";
                }
            } else {
                $sendrequestmessage = "Silahkan melanjutkan ke proses PO";
            }
            DB::commit();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => false,
                    "message" => 'Berhasil Melakukan Approve PR, Proses otorisasi selesai, ' . $sendrequestmessage . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil Melakukan Approve PR, Proses otorisasi selesai, ' . $sendrequestmessage . $emailmessage);
            }
        } else {
            $monitor->message        = 'Melakukan Otorisasi PR (otorisasi selanjutnya ' . $pr->current_authorization()->employee->name . ')';
            $monitor->save();

            $mail_to = $pr->current_authorization()->employee->email;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $pr->ticket->request_type(),
                'ticketing_type' => 'Barang Jasa',
                'salespoint_name' => $pr->ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $pr->current_authorization()->employee->name,
                'code' => $pr->ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'pr_approval'));
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
                    "message" => 'Berhasil Melakukan Approve PR, Proses otorisasi selanjutnya oleh (' . $pr->current_authorization()->employee_name . ')' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil Melakukan Approve PR, Proses otorisasi selanjutnya oleh (' . $pr->current_authorization()->employee_name . ')' . $emailmessage);
            }
        }

        // otomatis create
    }

    public function approvePRarmada($pr, $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";

        $monitor                        = new ArmadaTicketMonitoring;
        $monitor->armada_ticket_id      = $pr->armada_ticket->id;
        $monitor->employee_id    = Auth::user()->id;
        $monitor->employee_name  = Auth::user()->name;
        // jika pr selesai
        if ($pr->status == 2) {
            $armada_ticket = $pr->armada_ticket;
            $armada_ticket->status = 4;
            $armada_ticket->save();

            $monitor->message        = 'Melakukan Otorisasi PR (otorisasi selesai)';
            $monitor->save();

            $mail_to = $pr->authorization_emails();
            $ccs = $armada_ticket->additional_emails();
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->armada_ticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $pr->armada_ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => '',
                'code' => $pr->armada_ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_approved'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            // email PR manual ke GA
            $mail_to = $pr->armada_ticket->ga_emails();
            $ccs = $armada_ticket->additional_emails();
            $attachments = [
                'pr' => $pr->getPath(),
                'facilityform' => ($pr->armada_ticket->facility_form) ? $pr->armada_ticket->facility_form->getPath() : null
            ];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->armada_ticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $pr->armada_ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => 'Tim GA',
                'code' => $pr->armada_ticket->code,
                'pr_id' => $pr->id,
                'attachments' => $attachments
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_ga'));
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
                    "message" => 'Berhasil Melakukan Approve PR Pengadaan Armada, Proses otorisasi selesai, Silahkan melanjutkan setup PO' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil Melakukan Approve PR Pengadaan Armada, Proses otorisasi selesai, Silahkan melanjutkan setup PO' . $emailmessage);
            }
        } else {
            $monitor->message        = 'Melakukan Otorisasi PR (otorisasi selanjutnya ' . $pr->current_authorization()->employee->name . ')';
            $monitor->save();

            $mail_to = $pr->current_authorization()->employee->email;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $pr->armada_ticket->type(),
                'ticketing_type' => 'Armada',
                'salespoint_name' => $pr->armada_ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $pr->current_authorization()->employee->name,
                'code' => $pr->armada_ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'pr_approval'));
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
                    "message" => 'Berhasil Melakukan Approve PR, Proses otorisasi selanjutnya oleh (' . $pr->current_authorization()->employee_name . ')' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil Melakukan Approve PR, Proses otorisasi selanjutnya oleh (' . $pr->current_authorization()->employee_name . ')' . $emailmessage);
            }
        }
    }

    public function approvePRsecurity($pr, $request, $return_data_type = 'view')
    {
        $emailflag = true;
        $emailmessage = "";

        $monitor = new SecurityTicketMonitoring;
        $monitor->security_ticket_id      = $pr->security_ticket->id;
        $monitor->employee_id    = Auth::user()->id;
        $monitor->employee_name  = Auth::user()->name;
        // jika pr selesai
        if ($pr->status == 2) {
            $security_ticket = $pr->security_ticket;
            $security_ticket->status = 4;
            $security_ticket->save();

            $monitor->message        = 'Melakukan Otorisasi PR (otorisasi selesai)';
            $monitor->save();

            $mail_to = $pr->authorization_emails();
            $ccs = $security_ticket->additional_emails();
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->security_ticket->type(),
                'ticketing_type' => 'Security',
                'salespoint_name' => $pr->security_ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => '',
                'code' => $pr->security_ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_approved'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            $mail_to = $pr->security_ticket->ga_emails();
            $ccs = $security_ticket->additional_emails();
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->security_ticket->type(),
                'ticketing_type' => 'Security',
                'salespoint_name' => $pr->security_ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => 'Tim GA',
                'code' => $pr->security_ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_ga'));
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
                    "message" => 'Berhasil Melakukan Approve PR Pengadaan Security, Proses otorisasi selesai, Silahkan melanjutkan setup PO' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil Melakukan Approve PR Pengadaan Security, Proses otorisasi selesai, Silahkan melanjutkan setup PO' . $emailmessage);
            }
        } else {
            $monitor->message        = 'Melakukan Otorisasi PR (otorisasi selanjutnya ' . $pr->current_authorization()->employee->name . ')';
            $monitor->save();

            $mail_to = $pr->current_authorization()->employee->email;
            $data = array(
                'original_emails' => [$mail_to],
                'transaction_type' => $pr->security_ticket->type(),
                'ticketing_type' => 'Security',
                'salespoint_name' => $pr->security_ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => $pr->current_authorization()->employee_name,
                'code' => $pr->security_ticket->code,
                'pr_id' => $pr->id,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }

            try {
                Mail::to($mail_to)->send(new NotificationMail($data, 'pr_approval'));
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
                    "message" => 'Berhasil Melakukan Approve PR, Proses otorisasi selanjutnya oleh (' . $pr->current_authorization()->employee_name . ')' . $emailmessage
                ]);
            } else {
                return back()->with('success', 'Berhasil Melakukan Approve PR, Proses otorisasi selanjutnya oleh (' . $pr->current_authorization()->employee_name . ')' . $emailmessage);
            }
        }
    }

    public function rejectPR(Request $request, $return_data_type = 'view')
    {
        try {
            // check authorization
            $pr = Pr::findOrFail($request->pr_id);
            if ($pr->current_authorization()->employee->id == Auth::user()->id) {
                DB::beginTransaction();
                $pr->rejected_by = Auth::user()->id;
                $pr->reject_reason = $request->reason;
                $pr->status = -1;
                $pr->save();
                foreach ($pr->pr_detail as $detail) {
                    $detail->delete();
                }
                foreach ($pr->pr_authorizations as $authorization) {
                    $authorization->delete();
                }
                if ($pr->ticket_id != null) {
                    $ticket = Ticket::findOrFail($pr->ticket_id);
                    $ticket->status = 3;
                    $ticket->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $pr->ticket_id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Reject Form PR Barang Jasa';
                    $monitor->save();

                    $transactiontype = $ticket->request_type();
                    $ticketingtype = 'Barang Jasa';
                    $salespointname = $ticket->salespoint->name;
                    $code = $ticket->code;
                }
                if ($pr->armada_ticket_id != null) {
                    $armadaticket = ArmadaTicket::findOrFail($pr->armada_ticket_id);
                    $armadaticket->status = 2;
                    $armadaticket->save();

                    $monitor = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $pr->armada_ticket_id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Reject Form PR Armada';
                    $monitor->save();

                    $transactiontype = $armadaticket->type();
                    $ticketingtype = 'Armada';
                    $salespointname = $armadaticket->salespoint->name;
                    $code = $armadaticket->code;
                }
                if ($pr->security_ticket_id != null) {
                    $securityticket = SecurityTicket::findOrFail($pr->security_ticket_id);
                    $securityticket->status = 2;
                    $securityticket->save();

                    $monitor = new SecurityTicketMonitoring;
                    $monitor->security_ticket_id      = $pr->security_ticket_id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Reject Form PR Security';
                    $monitor->save();

                    $transactiontype            = $securityticket->type();
                    $ticketingtype              = 'Security';
                    $salespointname             = $securityticket->salespoint->name;
                    $code                       = $securityticket->code;
                }

                $employee_ids = $pr->pr_authorizations->pluck('employee_id');
                $employee_emails = Employee::whereIn('id', $employee_ids)->get()->pluck('email');
                $mail_to = $employee_emails->toArray();
                $data = array(
                    'original_emails' => $mail_to,
                    'transaction_type' => $transactiontype,
                    'ticketing_type' => $ticketingtype,
                    'salespoint_name' => $salespointname,
                    'from' => Auth::user()->name,
                    'to' => 'Bapak/Ibu',
                    'code' => $code,
                    'reason' => $request->reason,
                    'pr_id' => $pr->id,
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                }
                $emailflag = true;
                try {
                    Mail::to($mail_to)->send(new NotificationMail($data, 'pr_reject'));
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
                        "message" => 'Berhasil Melakukan Reject PR. Silahkan membuat form PR baru' . $emailmessage
                    ]);
                } else {
                    return redirect('/pr')->with('success', 'Berhasil Melakukan Reject PR. Silahkan membuat form PR baru' . $emailmessage);
                }
            } else {
                throw new \Exception("Otorisasi saat ini dan akun login tidak sesuai");
            }
        } catch (\Exception $ex) {
            DB::rollback();
            if ($return_data_type == 'api') {
                return response()->json([
                    "error" => true,
                    "message" => 'Gagal reject PR ' . $ex->getMessage() . $ex->getLine()
                ]);
            } else {
                return back()->with('error', 'Gagal reject PR ' . $ex->getMessage() . $ex->getLine());
            }
        }
    }

    public function revisePR(Request $request)
    {
        switch ($request->type) {
            case 'barangjasa':
                $pr = Pr::findOrFail($request->pr_id);
                $ticket = $pr->ticket;
                // validate
                // pr harus sudah selesai atau saat setup po, status pengadaan barangjasa harus belum boleh ada po / masih setup PO
                if (!in_array($pr->status, [1, 2])) {
                    return back()->with('error', 'Tidak dapat melakukan revisi PR');
                }
                if (count($ticket->po) > 0) {
                    return back()->with('error', 'Pengadaan ' . $ticket->code . ' telah memiliki PO. harap melakukan reject PO terlebih dahulu');
                }
                try {
                    DB::beginTransaction();
                    $pr->rejected_by = Auth::user()->id;
                    $pr->reject_reason = $request->reason;
                    $pr->save();
                    $pr->delete();

                    foreach ($pr->pr_detail as $detail) {
                        $detail->delete();
                    }

                    $ticket->status = 3;
                    $ticket->save();

                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $pr->ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Revise Form PR (' . $request->reason . ')';
                    $monitor->save();

                    DB::commit();
                    return back()->with('success', 'PR berhasil di Revisi');
                } catch (\Exception $ex) {
                    return back()->with('error', 'Gagal revisi PR');
                }
                break;

            case 'armada':
                $pr = Pr::findOrFail($request->pr_id);
                $armadaticket = $pr->armada_ticket;
                // validate
                // pr harus sudah selesai, status pengadaan barangjasa harus belum boleh ada po / masih setup PO
                if (!in_array($pr->status, [1, 2])) {
                    return back()->with('error', 'Tidak dapat melakukan revisi PR');
                }
                if (count($armadaticket->po) > 0) {
                    return back()->with('error', 'Pengadaan ' . $armadaticket->code . ' telah memiliki PO. harap melakukan reject PO terlebih dahulu');
                }
                try {
                    DB::beginTransaction();
                    $pr->rejected_by = Auth::user()->id;
                    $pr->reject_reason = $request->reason;
                    $pr->save();
                    $pr->delete();

                    foreach ($pr->pr_detail as $detail) {
                        $detail->delete();
                    }

                    $armadaticket->status = 2;
                    $armadaticket->save();

                    $monitor = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Revise Form PR (' . $request->reason . ')';
                    $monitor->save();

                    DB::commit();
                    return back()->with('success', 'PR berhasil di Revisi');
                } catch (\Exception $ex) {
                    return back()->with('error', 'Gagal revisi PR');
                }
                break;

            case 'security':
                $pr = Pr::findOrFail($request->pr_id);
                $securityticket = $pr->security_ticket;
                // validate
                // pr harus sudah selesai, status pengadaan barangjasa harus belum boleh ada po / masih setup PO
                if (!in_array($pr->status, [1, 2])) {
                    return back()->with('error', 'Tidak dapat melakukan revisi PR');
                }
                if (count($securityticket->po) > 0) {
                    return back()->with('error', 'Pengadaan ' . $securityticket->code . ' telah memiliki PO. harap melakukan reject PO terlebih dahulu');
                }
                try {
                    DB::beginTransaction();
                    $pr->rejected_by = Auth::user()->id;
                    $pr->reject_reason = $request->reason;
                    $pr->save();
                    $pr->delete();

                    foreach ($pr->pr_detail as $detail) {
                        $detail->delete();
                    }

                    $securityticket->status = 2;
                    $securityticket->save();

                    $monitor = new SecurityTicketMonitoring;
                    $monitor->security_ticket_id      = $securityticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Revise Form PR (' . $request->reason . ')';
                    $monitor->save();

                    DB::commit();
                    return back()->with('success', 'PR berhasil di Revisi');
                } catch (\Exception $ex) {
                    return back()->with('error', 'Gagal revisi PR');
                }
                break;
            default:
                return back()->with('error', 'Salah tipe revisi');
                break;
        }
    }
    public function resendRequestAssetNumber(Request $request)
    {
        $response = $this->sendRequestAssetNumber($request);
        $data = $response->getData();
        if (!$data->error) {
            return back()->with('success', $data->message);
        } else {
            return back()->with('error', $data->message);
        }
    }
    public function sendRequestAssetNumber(Request $request)
    {
        try {
            $ticket = Ticket::findOrFail($request->ticket_id);
            $pr = Pr::findOrFail($request->pr_id);
            $pr_items = [];
            foreach ($pr->pr_detail as $detail) {
                $item                      = new \stdClass();
                $item->name                = $detail->name;
                $item->qty                 = $detail->qty;
                $item->ongkir_price        = $detail->ongkir_price ?? 0;
                $item->ongpas_price        = $detail->ongpas_price ?? 0;
                $item->price               = $detail->price + $item->ongkir_price + $item->ongpas_price;
                $item->uom                 = $detail->uom;
                $item->isAsset             = $detail->isAsset;
                $item->setup_date          = $detail->setup_date;
                $item->notes               = $detail->notes;
                $item->asset_number_token  = $detail->asset_number_token;
                $item->created_at          = $detail->created_at;
                $item->notes_bidding_harga = $detail->ticket_item->bidding->price_notes;
                $item->notes_keterangan_barang = $detail->ticket_item->bidding->ketersediaan_barang_notes;
                array_push($pr_items, $item);
            }

            $prinfo = new \stdClass();
            $prinfo->salespoint_name = $ticket->salespoint->name;
            $prinfo->salespoint_code = $ticket->salespoint->code;
            $prinfo->isBudget = ($ticket->budget_type == 0) ? true : false;

            $authors = [];
            foreach ($pr->pr_authorizations as $author) {
                $newAuthor = new \stdClass();
                $newAuthor->name              = $author->employee_name;
                $newAuthor->as                = $author->as;
                $newAuthor->position          = $author->employee_position;
                $newAuthor->level             = $author->level;
                $newAuthor->approval_datetime = $author->updated_at;
                array_push($authors, $newAuthor);
            }
            $data = [
                "pr_items" => $pr_items,
                "prinfo" => $prinfo,
                'ticket_code' => $ticket->code,
                'authors' => $authors,
                "attachments" => $ticket->all_attachments(),
                "type" => "request",
            ];
            $curl = curl_init();
            switch (config('app.env')) {
                case 'local':
                    // development
                    $api_url = "http://aset.pinusmerahabadi.co.id:8585/ticket/post";
                    break;
                case 'development':
                    //  QAS
                    $api_url = "http://192.168.35.163:8585/ticket/post";
                    break;
                case 'production':
                    // Production
                    $api_url = "http://aset.pinusmerahabadi.co.id:8585/ticket/post";
                    break;
                default:
                    $api_url = "";
                    break;
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . config('customvariable.asset_bearer_token'),
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
            }
            curl_close($curl);
            if (isset($error_msg)) {
                // api connection error
                return response()->json([
                    'error' => true,
                    'message' => $error_msg
                ], 500);
            } else {
                $response = json_decode($response);
                $data = [];
                return response()->json([
                    'error' => !$response->success,
                    'message' => $response->message,
                ], 200);
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal Mengirimkan request ke web asset'
            ]);
        }
    }

    public function getRequestAssetNumberStatus(Request $request)
    {
        $ticket_code = $request->ticket_code;
        try {
            $curl = curl_init();
            switch (config('app.env')) {
                case 'local':
                    // development
                    $api_url = "http://aset.pinusmerahabadi.co.id:8585/ticket/post";
                    break;
                case 'development':
                    //  QAS
                    $api_url = "http://192.168.35.163:8585/ticket/post";
                    break;
                case 'production':
                    // Production
                    $api_url = "http://192.168.35.163:8585/ticket/post";
                    break;
                default:
                    $api_url = "";
                    break;
            }
            $data = [
                "ticket_code" => $ticket_code,
                "type" => "check",
            ];
            // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . config('customvariable.asset_bearer_token'),
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
            }
            curl_close($curl);
            if (isset($error_msg)) {
                throw new \Exception($error_msg);
            } else {
                $response = json_decode($response);
                return response()->json([
                    'error' => false,
                    'is_found' => $response->success,
                    'ticket_code' => $ticket_code,
                    'message' => $response->message,
                    'detailInfo' => ($response->success == true) ? $response->result->detailInfo : null,
                    'data' => ($response->success == true) ? $response->result->data : null,
                    'auth' => ($response->success == true) ? $response->result->auth : null
                ], 200);
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => "Gagal Koneksi ke Web Asset" . " (" . $ex->getMessage() . ")",
                'ticket_code' => $ticket_code
            ], 500);
        }
    }

    public function setRequestAssetNumber(Request $request)
    {
        $lists = [];
        $ticket = Ticket::where('code', $request->ticket_code)->first();
        try {
            if (!$ticket) {
                throw new \Exception('ticket code ' . $ticket_code . ' tidak ditemukan');
            }
            if ($ticket->status == -1) {
                throw new \Exception('PR untuk ticket code ' . $ticket_code . ' tidak ditemukan');
            }
            $pr = null;
            foreach ($lists as $assetnumber) {
                $new_asset_number = $assetnumber->number;
                $asset_token = $assetnumber->token;
                $pr_detail = PrDetail::where('asset_number_token', $asset_token)->first();
                $pr = $pr_detail->pr;
                if ($pr_detail->pr->ticket->code != $ticket->code) {
                    throw new \Exception($assetnumber->item_name . ' tidak ditemukan pada pr pengadaan');
                }
                $old_asset_number = $pr_detail->asset_number;
                $pr_detail->isAsset = ($new_asset_number) ? true : false;
                $pr_detail->asset_number = $new_asset_number ?? null;
                $pr_detail->save();
            }
            $pr->assetnumber_by = -1;
            $pr->status = 2;
            $pr->save();
            if ($ticket != null) {
                if ($ticket->status < 6) {
                    // tidak memundurkan status jika status tiket sudah berlanjut
                    $ticket->status = 6;
                    $ticket->save();
                }

                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = -1;
                $monitor->employee_name  = "System";
                $monitor->message        = 'Submit Nomor Asset di PR';
                $monitor->save();
            }
            // email PR manual ke GA
            $mail_to = $pr->ticket->ga_emails();
            $ccs = $ticket->additional_emails();
            $attachments = [
                'pr' => $pr->getPath()
            ];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->ticket->request_type(),
                'ticketing_type' => 'Barang Jasa',
                'salespoint_name' => $pr->ticket->salespoint->name,
                'from' => "PODS System",
                'to' => 'Tim GA',
                'code' => $pr->ticket->code,
                'pr_id' => $pr->id,
                'attachments' => $attachments
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_ga'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }
            return response()->json([
                "error" => false,
                "message" => "Update asset number success " . $emailmessage,
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "error" => false,
                "message" => $ex->getMessage(),
            ]);
        }
    }

    public function submitAssetNumber(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->ticket_id);
            // $armadaticket = ArmadaTicket::find($request->armada_ticket_id);
            // if($ticket == null && $armadaticket == null){
            if ($ticket == null) {
                throw new \Exception('Pr tidak valid');
            }
            if ($ticket != null) {
                $updated_at = $ticket->updated_at->format('Y-m-d H:i:s');
            }
            // if($armadaticket != null){
            //    $updated_at = $armadaticket->updated_at->format('Y-m-d H:i:s');
            // }
            if ($request->updated_at != $updated_at) {
                throw new \Exception("Tiket sudah di update sebelumnya. Silahkan coba lagi");
            }

            $pr = Pr::findOrFail($request->pr_id);
            $pr->assetnumber_by = Auth::user()->id;
            if ($ticket != null) {
                foreach ($request->item as $key => $item) {
                    $pr_detail = PrDetail::findOrFail($item['pr_detail_id']);
                    $pr_detail->isAsset = $item['isAsset'];
                    if ($item['isAsset']) {
                        $has_asset_flag = false;
                        // simpan nomor
                        if ($item['asset_numbers']) {
                            $asset_number_list = explode(',', $item['asset_numbers']);
                            $asset_number_list = array_map('trim', $asset_number_list);
                            $asset_number_list = array_filter($asset_number_list, function ($asset_number) {
                                if ($asset_number == "" || $asset_number == null) {
                                    return false;
                                } else {
                                    return true;
                                }
                            });
                            if (count($asset_number_list) > 0) {
                                $has_asset_flag = true;
                                $pr_detail->asset_number = json_encode(array_values($asset_number_list));
                            }
                        }
                        if (isset($request->file('item')[$key]["asset_numbers_file"])) {
                            $ext = pathinfo($request->file('item')[$key]["asset_numbers_file"]->getClientOriginalName(), PATHINFO_EXTENSION);
                            $name = $pr_detail->name . "_" . $pr_detail->id . '_assetnumber.' . $ext;
                            $name = str_replace([" ", "/"], "_", $name);

                            $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/assetnumber/' . $name;
                            $file = pathinfo($path);
                            $path = $request->file('item')[$key]["asset_numbers_file"]->storeAs($file['dirname'], $file['basename'], 'public');

                            $has_asset_flag = true;
                            $pr_detail->asset_number_filepath = $path;
                        }

                        if (!$has_asset_flag) {
                            throw new \Exception('Item asset ' . $pr_detail->name . " harus memiliki minimal satu nomor asset. (pilih salah satu metode manual input / metode upload)");
                        };
                    }
                    $pr_detail->save();
                }
            }

            $pr->status = 2;
            $pr->save();

            if ($ticket != null) {
                $ticket->status = 6;
                $ticket->save();
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Submit Nomor Asset di PR';
                $monitor->save();
            }

            // email PR manual ke GA
            $mail_to = $pr->ticket->ga_emails();
            $ccs = $ticket->additional_emails();
            $attachments = [
                'pr' => $pr->getPath()
            ];
            $data = array(
                'original_emails' => $mail_to,
                'original_ccs' => $ccs,
                'transaction_type' => $pr->ticket->request_type(),
                'ticketing_type' => 'Barang Jasa',
                'salespoint_name' => $pr->ticket->salespoint->name,
                'from' => Auth::user()->name,
                'to' => 'Tim GA',
                'code' => $pr->ticket->code,
                'pr_id' => $pr->id,
                'attachments' => $attachments
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
                $ccs = [];
            }

            $emailflag = true;
            try {
                Mail::to($mail_to)->cc($ccs)->send(new NotificationMail($data, 'pr_ga'));
            } catch (\Exception $ex) {
                $emailflag = false;
            }

            $emailmessage = "";
            if (!$emailflag) {
                $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
            }

            DB::commit();
            return redirect('/pr')->with('success', 'Sukses submit nomor asset. Silahkan melanjutkan ke proses PO' . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal approved PR ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function printPR($ticket_code, $method = 'stream')
    {
        $ticket = Ticket::where('code', $ticket_code)->where('status', '>=', 5)->first();
        $armadaticket = ArmadaTicket::where('code', $ticket_code)->where('status', '>=', 4)->first();
        $securityticket = SecurityTicket::where('code', $ticket_code)->where('status', '>=', 4)->first();
        try {
            if ($ticket != null) {
                $pr = $ticket->pr;
                $code = $ticket->code;
            }
            if ($armadaticket != null) {
                $pr = $armadaticket->pr;
                $code = $armadaticket->code;
            }
            if ($securityticket != null) {
                $pr = $securityticket->pr;
                $code = $securityticket->code;
            }
            if ($pr->status < 1) {
                return 'pr printout unavailable';
            }
            $authorizations = [];
            foreach ($pr->pr_authorizations as $author) {
                $newAuthor = new \stdClass();
                $newAuthor->name = $author->employee_name;
                $newAuthor->position = $author->employee_position;
                $newAuthor->date = $author->updated_at->translatedFormat('d F Y (H:i)');
                array_push($authorizations, $newAuthor);
            }
            if (($ticket->budget_type ?? -1) == 0 && $ticket != null) {
                $authorizations = array_slice($authorizations, 0, 5, true);
            }

            if ($armadaticket != null) {
                $authorizations = array_slice($authorizations, 0, 5, true);
            }

            if ($securityticket != null) {
                $authorizations = array_slice($authorizations, 0, 5, true);
            }

            if ($ticket != null) {
                $pdf = PDF::loadView('pdf.prpdf', compact('pr', 'ticket', 'authorizations'))->setPaper('a4', 'landscape');
            }

            if ($armadaticket != null) {
                $pdf = PDF::loadView('pdf.prpdf', compact('pr', 'armadaticket', 'authorizations'))->setPaper('a4', 'landscape');
            }

            if ($securityticket != null) {
                $pdf = PDF::loadView('pdf.prpdf', compact('pr', 'securityticket', 'authorizations'))->setPaper('a4', 'landscape');
            }
            if ($method == 'stream') {
                return $pdf->stream('PR (' . $code . ').pdf');
            } else if ($method == 'path') {
                $path = "/temporary/pr/PR (" . $code . ").pdf";
                Storage::disk('public')->put($path, $pdf->output());
                return $path;
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal Mencetak PR ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function updateAssetNumberView($ticket_code)
    {
        try {
            $ticket =  Ticket::where('code', $ticket_code)->first();
            if ($ticket) {
                return view('Operational.updateassetnumber', compact('ticket'));
            } else {
                return back()->with('error', 'Tiket tidak ditemukan');
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Tidak dapat melakukan update nomor asset');
        }
    }

    public function updateAssetNumber(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::find($request->ticket_id);
            if ($ticket == null) {
                throw new \Exception('Pr tidak valid');
            }
            if ($ticket != null) {
                $updated_at = $ticket->pr->updated_at->format('Y-m-d H:i:s');
            }
            if ($request->updated_at != $updated_at) {
                throw new \Exception("PR sudah di update sebelumnya. Silahkan coba lagi");
            }

            $pr = Pr::findOrFail($request->pr_id);
            $pr->assetnumber_by = Auth::user()->id;
            if ($ticket != null) {
                foreach ($request->item as $key => $item) {
                    $pr_detail = PrDetail::findOrFail($item['pr_detail_id']);
                    $has_asset_flag = false;
                    // jika asset_numbers ada file diisi maka set iAsAsset menjadi true
                    // simpan nomor
                    if ($item['asset_numbers']) {
                        $asset_number_list = explode(',', $item['asset_numbers']);
                        $asset_number_list = array_map('trim', $asset_number_list);
                        $asset_number_list = array_filter($asset_number_list, function ($asset_number) {
                            if ($asset_number == "" || $asset_number == null) {
                                return false;
                            } else {
                                return true;
                            }
                        });
                        if (count($asset_number_list) > 0) {
                            $has_asset_flag = true;
                            $pr_detail->asset_number = json_encode(array_values($asset_number_list));
                        } else {
                            $pr_detail->asset_number = json_encode([]);
                        }
                    } else {
                        $pr_detail->asset_number = json_encode([]);
                    }
                    if (isset($request->file('item')[$key]["asset_numbers_file"])) {
                        $ext = pathinfo($request->file('item')[$key]["asset_numbers_file"]->getClientOriginalName(), PATHINFO_EXTENSION);
                        $name = $pr_detail->name . "_" . $pr_detail->id . '_assetnumber.' . $ext;
                        $name = str_replace([" ", "/"], "_", $name);

                        $path = "/attachments/ticketing/barangjasa/" . $ticket->code . '/assetnumber/' . $name;
                        $file = pathinfo($path);
                        $path = $request->file('item')[$key]["asset_numbers_file"]->storeAs($file['dirname'], $file['basename'], 'public');

                        $has_asset_flag = true;
                        $pr_detail->asset_number_filepath = $path;
                    }

                    if (isset($pr_detail->asset_number_filepath)) {
                        $has_asset_flag = true;
                    }

                    // jika sebelumnya asset ternyata has asset flagnya menjadi false maka return error
                    if ($pr_detail->isAsset == true && $has_asset_flag == false) {
                        throw new \Exception("Update nomor asset hanya dapat dilakukan untuk revisi nomor asset yang sudah ada / menambahkan nomor asset untuk item yang sebelumnya non asset");
                    }
                    if ($has_asset_flag) {
                        $pr_detail->isAsset = true;
                    };
                    $pr_detail->save();
                }
            }

            if ($ticket != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Update Nomor Asset (Alasan : ' . $request->reason . ')';
                $monitor->save();
            }

            DB::commit();
            return back()->with('success', 'Sukses update nomor asset');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal update nomor asset ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }

    public function updatePRDataView($ticket_code)
    {
        try {
            $ticket =  Ticket::where('code', $ticket_code)->first();
            if ($ticket) {
                return view('Operational.updateprdata', compact('ticket'));
            } else {
                return back()->with('error', 'Tiket tidak ditemukan');
            }
        } catch (\Exception $ex) {
            return back()->with('error', 'Tidak dapat melakukan update nomor asset');
        }
    }

    public function updatePRData(Request $request)
    {
        try {
            DB::beginTransaction();
            $pr = Pr::find($request->pr_id);
            if ($pr == null) {
                throw new \Exception('Pr tidak valid');
            }
            $updated_at = $pr->updated_at->format('Y-m-d H:i:s');
            if ($request->updated_at != $updated_at) {
                throw new \Exception("PR sudah di update sebelumnya. Silahkan coba lagi");
            }

            $pr->revised_by = Auth::user()->id;
            $pr->revised_at = now();
            $pr->revise_reason = $request->reason;
            if (isset($request->selected_issue_po_id)) {
                $issuepo = IssuePO::find($request->selected_issue_po_id);
                $pr->revise_ba_filepath = $issuepo->ba_file;
            }
            $pr->save();

            foreach ($request->item as $key => $item) {
                $pr_detail = PrDetail::findOrFail($item['pr_detail_id']);
                $pr_detail->price = $item['price'];
                $pr_detail->save();
            }

            if ($pr->ticket_id != null) {
                $monitor = new TicketMonitoring;
                $monitor->ticket_id      = $pr->ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi data PR berdasarkan BA dengan alasan : ' . $request->reason;
                $monitor->save();
            }

            if ($pr->armada_ticket_id != null) {
                $monitor = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $pr->armada_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi data PR berdasarkan BA dengan alasan : ' . $request->reason;
                $monitor->save();
            }

            if ($pr->security_ticket_id != null) {
                $monitor = new SecurityTicketMonitoring;
                $monitor->security_ticket_id      = $pr->security_ticket->id;
                $monitor->employee_id    = Auth::user()->id;
                $monitor->employee_name  = Auth::user()->name;
                $monitor->message        = 'Revisi data PR berdasarkan BA dengan alasan : ' . $request->reason;
                $monitor->save();
            }

            DB::commit();
            return redirect('/pr/' . $request->ticket_code)->with('success', 'Sukses update data PR');
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect('/pr/' . $request->ticket_code)->with('error', 'Gagal update data PR ' . $ex->getMessage() . '(' . $ex->getLine() . ')');
        }
    }
}
