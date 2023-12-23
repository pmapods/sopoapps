<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketingBlockOpenRequest;
use App\Models\ArmadaTicket;
use App\Models\ArmadaTicketMonitoring;
use App\Models\SecurityTicket;
use App\Models\SecurityTicketMonitoring;
use DB;
use Auth;

class TicketingBlockController extends Controller
{
    public function checkTicketingAvailable(Request $request)
    {
        // ticketing_block (table)
        // ticketing_type_name
        // Armada
        // Security
        // CIT
        // Pest Control
        // block_day
        // max_pr_sap_day
        // max_validation_reject_day
        // Vendor Evaluation
        try {
            if ($request->type == 'armada') {
                $ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Armada')->first();
                if ($request->block_type == 'block_day') {
                    if (now()->day > $ticketing_block->max_block_day) {
                        throw new \Exception('Perpanjangan Armada maksimal tanggal ' . $ticketing_block->max_block_day . ' di bulan berjalan');
                    }
                    // check apakah tanggal sebelum atau setelah tanggal block day
                    if (now()->day > $ticketing_block->block_day) {
                        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 1)->where('ticket_code', $request->ticket_code)->first();
                        if ($ticketing_block_open_request == null) {
                            throw new \Exception('Perpanjangan Armada hanya dapat dilakukan pada tanggal 1-' . $ticketing_block->block_day . ' setiap bulannya');
                        }
                    }
                    return response()->json([
                        'error' => false,
                        'message' => 'Perpanjangan diijinkan'
                    ]);
                }
            } else if ($request->type == 'security') {
                $ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Security')->first();
                if ($request->block_type == 'block_day') {
                    if (now()->day > $ticketing_block->max_block_day) {
                        throw new \Exception('Perpanjangan / Replace / End Contract Security maksimal tanggal ' . $ticketing_block->max_block_day . ' di bulan berjalan');
                    }
                    // check apakah tanggal sebelum atau setelah tanggal block day
                    if (now()->day > $ticketing_block->block_day) {
                        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 1)->where('ticket_code', $request->ticket_code)->first();
                        if ($ticketing_block_open_request == null) {
                            throw new \Exception('Perpanjangan / Replace / End Contract Security hanya dapat dilakukan pada tanggal 1-' . $ticketing_block->block_day . ' setiap bulannya');
                        }
                    }
                    return response()->json([
                        'error' => false,
                        'message' => 'Perpanjangan diijinkan'
                    ]);
                }
            } else if ($request->type == 'cit') {
                $ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'CIT')->first();
                if ($request->block_type == 'block_day') {
                    if (now()->day > $ticketing_block->max_block_day) {
                        throw new \Exception('Perpanjangan / Replace / End Contract CIT maksimal tanggal ' . $ticketing_block->max_block_day . ' di bulan berjalan');
                    }
                    // check apakah tanggal sebelum atau setelah tanggal block day
                    if (now()->day > $ticketing_block->block_day) {
                        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 1)->where('ticket_code', $request->ticket_code)->first();
                        if ($ticketing_block_open_request == null) {
                            throw new \Exception('Perpanjangan / Replace / End Contract CIT hanya dapat dilakukan pada tanggal 1-' . $ticketing_block->block_day . ' setiap bulannya');
                        }
                    }
                    return response()->json([
                        'error' => false,
                        'message' => 'Perpanjangan diijinkan'
                    ]);
                }
            } else if ($request->type == 'pest_control') {
                $ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Pest Control')->first();
                if ($request->block_type == 'block_day') {
                    if (now()->day > $ticketing_block->max_block_day) {
                        throw new \Exception('Perpanjangan / Replace / End Contract Pest Control maksimal tanggal ' . $ticketing_block->max_block_day . ' di bulan berjalan');
                    }
                    // check apakah tanggal sebelum atau setelah tanggal block day
                    if (now()->day > $ticketing_block->block_day) {
                        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 1)->where('ticket_code', $request->ticket_code)->first();
                        if ($ticketing_block_open_request == null) {
                            throw new \Exception('Perpanjangan / Replace / End Contract Pest Control hanya dapat dilakukan pada tanggal 1-' . $ticketing_block->block_day . ' setiap bulannya');
                        }
                    }
                    return response()->json([
                        'error' => false,
                        'message' => 'Perpanjangan diijinkan'
                    ]);
                }
            } else if ($request->type == 'merchendiser') {
                $ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Merchendiser')->first();
                if ($request->block_type == 'block_day') {
                    if (now()->day > $ticketing_block->max_block_day) {
                        throw new \Exception('Perpanjangan / Replace / End Contract Merchendiser maksimal tanggal ' . $ticketing_block->max_block_day . ' di bulan berjalan');
                    }
                    // check apakah tanggal sebelum atau setelah tanggal block day
                    if (now()->day > $ticketing_block->block_day) {
                        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 1)->where('ticket_code', $request->ticket_code)->first();
                        if ($ticketing_block_open_request == null) {
                            throw new \Exception('Perpanjangan / Replace / End Contract Merchendiser hanya dapat dilakukan pada tanggal 1-' . $ticketing_block->block_day . ' setiap bulannya');
                        }
                    }
                    return response()->json([
                        'error' => false,
                        'message' => 'Perpanjangan diijinkan'
                    ]);
                }
            } else if ($request->type == 'vendor_evaluation') {
                $ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Vendor Evaluation')->first();
                if ($request->block_type == 'block_day') {
                    if (now()->day > $ticketing_block->max_block_day) {
                        throw new \Exception('Pembuatan Vendor Evaluation maksimal tanggal ' . $ticketing_block->max_block_day . ' di bulan berjalan');
                    }
                    // check apakah tanggal sebelum atau setelah tanggal block day
                    if (now()->day > $ticketing_block->block_day) {
                        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 1)->where('ticket_code', $request->ticket_code)->first();
                        if ($ticketing_block_open_request == null) {
                            throw new \Exception('Pembuatan Vendor Evaluation hanya dapat dilakukan pada tanggal 1-' . $ticketing_block->block_day . ' setiap bulannya');
                        }
                    }
                    return response()->json([
                        'error' => false,
                        'message' => 'Pembuatan diijinkan'
                    ]);
                }
            } else {
                throw new \Exception('Check Ticketing type ' . $request->type . ' is not available');
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function BAVerification(Request $request)
    {
        $status_sort = [0, 1, -1];
        $ticketing_block_open_request = TicketingBlockOpenRequest::get()->sortBy(function ($item, $key) use ($status_sort) {
            return array_search($item->status, $status_sort);
        });
        return view('Operational.BAVerification', compact('ticketing_block_open_request'));
    }

    public function BAVerificationConfirm(Request $request)
    {
        try {
            DB::beginTransaction();
            $open_request = TicketingBlockOpenRequest::findOrFail($request->id);
            $open_request->confirmed_by = Auth::user()->id;
            $open_request->status = 1;
            $open_request->save();

            $armadaticket = ArmadaTicket::where('code', $open_request->ticket_code)->first();
            if ($armadaticket) {
                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Konfirmasi BA Perpanjangan';
                $monitor->save();
            }
            $securityticket = SecurityTicket::where('code', $open_request->ticket_code)->first();
            if ($securityticket) {
                $monitor                        = new SecurityTicketMonitoring;
                $monitor->security_ticket_id    = $securityticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Konfirmasi BA Perpanjangan';
                $monitor->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil Konfirmasi BA');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Confirm BA (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }

    public function BAVerificationReject(Request $request)
    {
        try {
            DB::beginTransaction();
            $open_request = TicketingBlockOpenRequest::findOrFail($request->id);
            $open_request->rejected_by = Auth::user()->id;
            $open_request->reject_reason = $request->reason;
            $open_request->status = -1;
            $open_request->save();

            $armadaticket = ArmadaTicket::where('code', $open_request->ticket_code)->first();
            if ($armadaticket) {
                $monitor                        = new ArmadaTicketMonitoring;
                $monitor->armada_ticket_id      = $armadaticket->id;
                $monitor->employee_id           = Auth::user()->id;
                $monitor->employee_name         = Auth::user()->name;
                $monitor->message               = 'Reject BA Perpanjangan dengan alasan ' . $request->reason;
                $monitor->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil Reject BA');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Reject BA (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }
}
