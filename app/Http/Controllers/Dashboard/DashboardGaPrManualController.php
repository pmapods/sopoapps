<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;

use App\Http\Controllers\Controller;


class DashboardGaPrManualController extends Controller
{
    public function dashboardGaPrManualView()
    {
        return view('Dashboard.prsapga');
    }

    public function getGaPrManual()
    {
        $data = [];
        $show_status_pr = [1, 2];
        $status_po = [-1, 1, 2, 3, 4, 0];

        $armadaticket =  ArmadaTicket::leftJoin('pr', 'pr.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('armada_ticket.status', 4)
            ->where('pr.status', 2)
            ->where('armada_ticket.id', '!=', 5733)
            ->orderBy('armada_ticket.created_at')
            ->get();

        $securityticket =  SecurityTicket::leftJoin('pr', 'pr.security_ticket_id', '=', 'security_ticket.id')
            ->where('security_ticket.status', 4)
            ->where('pr.status', 2)
            ->orderBy('security_ticket.created_at')
            ->get();

        $barangjasaticketdua = Ticket::leftJoin('pr', 'pr.ticket_id', '=', 'ticket.id')
            ->whereIn('pr.status', $show_status_pr)
            ->where('ticket.status', 5)
            ->where('pr.deleted_at', null)
            ->orderBy('ticket.created_at')
            ->get();

        $barangjasaticket = Ticket::leftJoin('pr', 'pr.ticket_id', '=', 'ticket.id')
            ->leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->whereIn('pr.status', $show_status_pr)
            ->where('ticket.status', 6)
            ->where('ticket.id', '!=', 1166)
            ->where('ticket.id', '!=', 490)
            ->whereNull('pr.deleted_at')
            ->whereNull('ticket_item.lpb_filepath')
            ->whereNull('ticket_item.invoice_filepath')
            ->whereNull('ticket_item.deleted_at')
            ->orderBy('ticket.created_at')
            ->get();

        $barangjasatickethehe = [];
        $barangjasatickethehedua = [];

        foreach ($barangjasaticket as $jasa) {
            $check_pr = DB::table('pr_sap')->where("data", "like", '%' . '"textnote":"' . $jasa->code . '"' . '%')->first();
            if ($check_pr) {
                $barangjasatickethehe[] = $jasa;
            }
        }

        foreach ($barangjasatickethehe as $hehehe) {
            $check_po = DB::table('po')->where("ticket_id", $hehehe->id)->whereIn('status', $status_po)->first();
            if ($check_po) {
                $barangjasatickethehedua[] = $hehehe;
            }
        }

        foreach ($barangjasaticketdua as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Barang Jasa';
                $newAuth->status = 'Menunggu Kelengkapan Nomor Asset';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($barangjasatickethehedua as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Barang Jasa';
                $newAuth->status = 'PR Manual Telah Full Approved';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($armadaticket as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Armada';
                $newAuth->status = 'PR Manual Telah Full Approved';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($securityticket as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Security';
                $newAuth->status = 'PR Manual Telah Full Approved';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $data = array_values(collect($data)->toArray());
        $qqq = [];

        foreach ($data as $key => $xxx) {
            $qqq[$xxx->code] = $xxx;
        }

        $qqq = array_values($qqq);
        foreach ($qqq as $key => &$xxx) {
            $xxx->nomor = $key + 1;
        }

        return response()->json([
            'data' => $qqq,
        ]);
    }

    public function getGaPrManualCount()
    {
        $data = [];
        $show_status_pr = [1, 2];
        $status_po = [-1, 1, 2, 3, 4, 0];

        $armadaticket =  ArmadaTicket::leftJoin('pr', 'pr.armada_ticket_id', '=', 'armada_ticket.id')
            ->where('armada_ticket.status', 4)
            ->where('pr.status', 2)
            ->where('armada_ticket.id', '!=', 5733)
            ->get();

        $securityticket =  SecurityTicket::leftJoin('pr', 'pr.security_ticket_id', '=', 'security_ticket.id')
            ->where('security_ticket.status', 4)
            ->where('pr.status', 2)
            ->get();

        $barangjasaticketdua = Ticket::leftJoin('pr', 'pr.ticket_id', '=', 'ticket.id')
            ->whereIn('pr.status', $show_status_pr)
            ->where('ticket.status', 5)
            ->where('pr.deleted_at', null)
            ->get();

        $barangjasaticket = Ticket::leftJoin('pr', 'pr.ticket_id', '=', 'ticket.id')
            ->whereIn('pr.status', $show_status_pr)
            ->where('ticket.status', 6)
            ->where('ticket.id', '!=', 1166)
            ->where('ticket.id', '!=', 490)
            ->where('pr.deleted_at', null)
            ->get();

        $barangjasatickethehe = [];

        $barangjasatickethehedua = [];

        foreach ($barangjasaticket as $jasa) {
            $check_pr = DB::table('pr_sap')->where("data", "like", '%' . '"textnote":"' . $jasa->code . '"' . '%')->first();
            if (!$check_pr) {
                $barangjasatickethehe[] = $jasa;
            }
        }

        foreach ($barangjasatickethehe as $hehehe) {
            $check_po = DB::table('po')->where("ticket_id", $hehehe->id)->whereIn('status', $status_po)->first();
            if (!$check_po) {
                $barangjasatickethehedua[] = $hehehe;
            }
        }

        foreach ($barangjasaticketdua as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Barang Jasa';
                $newAuth->status = 'Menunggu Kelengkapan Nomor Asset';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($barangjasatickethehedua as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Barang Jasa';
                $newAuth->status = 'PR Manual Telah Full Approved';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($armadaticket as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Armada';
                $newAuth->status = 'PR Manual Telah Full Approved';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($securityticket as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->transaction_type = 'Security';
                $newAuth->status = 'PR Manual Telah Full Approved';
                $newAuth->link = "/pr/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $data = array_values(collect($data)->toArray());

        return response()->json([
            "total" => count($data),
        ]);
    }
}
