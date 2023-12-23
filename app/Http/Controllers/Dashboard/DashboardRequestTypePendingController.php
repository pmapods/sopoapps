<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Http\Controllers\Controller;
use App\Models\EmployeeLocationAccess;


class DashboardRequestTypePendingController extends Controller
{
    public function dashboardRequestTypePendingView()
    {
        return view('Dashboard.requesttypepending');
    }

    public function getRequestTypePending()
    {
        $data = [];
        $status_security = [0, 2];
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');

        $barangjasa1 = Ticket::leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('ticket.status', 6)
            ->where('ticket.deleted_at', null)
            ->where('ticket_item.deleted_at', null)
            ->where('ticket_item.invoice_filepath', null)
            ->where('ticket_item.lpb_filepath', null)
            ->whereIn('ticket.salespoint_id', $salespoint_ids)
            ->orderBy('ticket.created_at')
            ->get();

        $barangjasa2 = Ticket::leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('ticket.status', 6)
            ->where('ticket.deleted_at', null)
            ->where('ticket_item.deleted_at', null)
            ->where('ticket_item.invoice_filepath', '!=', null)
            ->where('ticket_item.lpb_filepath', null)
            ->whereIn('ticket.salespoint_id', $salespoint_ids)
            ->orderBy('ticket.created_at')
            ->get();

        $barangjasa3 = Ticket::leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('ticket.status', 6)
            ->where('ticket.deleted_at', null)
            ->where('ticket_item.deleted_at', null)
            ->where('ticket_item.invoice_filepath', null)
            ->where('ticket_item.lpb_filepath', '!=', null)
            ->whereIn('ticket.salespoint_id', $salespoint_ids)
            ->orderBy('ticket.created_at')
            ->get();

        $armadaticket =  ArmadaTicket::where('status', 5)
            ->where('bastk_path', '=', null)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->get();

        $securityticket =  SecurityTicket::where('status', 5)
            ->whereIn('ticketing_type', $status_security)
            ->where('lpb_path', '=', null)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->get();

        $securityticketendcontract =  SecurityTicket::where('status', 5)
            ->where('ticketing_type', 3)
            ->where('endkontrak_path', '=', null)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->get();

        foreach ($barangjasa1 as $ticket) {
            try {
                $newAuth = new \stdClass();
                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->type = 'Barang Jasa';
                $newAuth->ticket_item =  $ticket->name;
                $newAuth->status = 'Invoice and LPB';
                $newAuth->link = "/ticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($barangjasa2 as $ticket) {
            try {
                $newAuth = new \stdClass();
                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->type = 'Barang Jasa';
                $newAuth->ticket_item =  $ticket->name;
                $newAuth->status = 'LPB';
                $newAuth->link = "/ticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($barangjasa3 as $ticket) {
            try {
                $newAuth = new \stdClass();
                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->type = 'Barang Jasa';
                $newAuth->ticket_item =  $ticket->name;
                $newAuth->status = 'Invoice';
                $newAuth->link = "/ticketing/" . $newAuth->code;
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
                $newAuth->type = 'Armada';
                $newAuth->ticket_item =  '-';
                $newAuth->status = 'BASTK';
                $newAuth->link = "/armadaticketing/" . $newAuth->code;
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
                $newAuth->type = 'Security Pengadaan and Replace';
                $newAuth->ticket_item =  '-';
                $newAuth->status = 'LPB';
                $newAuth->link = "/securityticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($securityticketendcontract as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;
                $newAuth->type = 'Security End Kontrak';
                $newAuth->ticket_item =  '-';
                $newAuth->status = 'LPB';
                $newAuth->link = "/securityticketing/" . $newAuth->code;
                array_push($data, $newAuth);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $data = array_values(collect($data)->toArray());

        foreach ($data as $key => &$xxx) {
            $xxx->nomor = $key + 1;
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getRequestTypePendingCount()
    {
        $status_security = [0, 2];
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');

        $barangjasa1 = Ticket::leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('ticket.status', 6)
            ->where('ticket.deleted_at', null)
            ->where('ticket_item.deleted_at', null)
            ->where('ticket_item.invoice_filepath', null)
            ->where('ticket_item.lpb_filepath', null)
            ->whereIn('ticket.salespoint_id', $salespoint_ids)
            ->orderBy('ticket.created_at')
            ->count();

        $barangjasa2 = Ticket::leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('ticket.status', 6)
            ->where('ticket.deleted_at', null)
            ->where('ticket_item.deleted_at', null)
            ->where('ticket_item.invoice_filepath', '!=', null)
            ->where('ticket_item.lpb_filepath', null)
            ->whereIn('ticket.salespoint_id', $salespoint_ids)
            ->orderBy('ticket.created_at')
            ->count();

        $barangjasa3 = Ticket::leftJoin('ticket_item', 'ticket_item.ticket_id', '=', 'ticket.id')
            ->where('ticket.status', 6)
            ->where('ticket.deleted_at', null)
            ->where('ticket_item.deleted_at', null)
            ->where('ticket_item.invoice_filepath', null)
            ->where('ticket_item.lpb_filepath', '!=', null)
            ->whereIn('ticket.salespoint_id', $salespoint_ids)
            ->orderBy('ticket.created_at')
            ->count();

        $armadaticket =  ArmadaTicket::where('status', 5)
            ->where('bastk_path', '=', null)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->count();

        $securityticket =  SecurityTicket::where('status', 5)
            ->whereIn('ticketing_type', $status_security)
            ->where('lpb_path', '=', null)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->count();

        $securityticketendcontract =  SecurityTicket::where('status', 5)
            ->where('ticketing_type', 3)
            ->where('endkontrak_path', '=', null)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->count();

        $barangjasa1 = (int)$barangjasa1;
        $barangjasa2 = (int)$barangjasa2;
        $barangjasa3 = (int)$barangjasa3;
        $int_armadaticket = (int)$armadaticket;
        $int_securityticket = (int)$securityticket;
        $int_securityticketendcontract = (int)$securityticketendcontract;

        $request_type_pending = $barangjasa1 + $barangjasa2 + $barangjasa3 + $int_armadaticket + $int_securityticket + $int_securityticketendcontract;

        return $request_type_pending;
    }
}
