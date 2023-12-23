<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Http\Controllers\Controller;

class DashboardItBarangJasaReminderController extends Controller
{
    public function dashboardItBarangJasaReminderView()
    {
        return view('Dashboard.itbarangjasareminder');
    }

    public function getItBarangJasaReminder()
    {
        $data = [];
        $barang_jasa_it = Ticket::leftJoin('po', 'po.ticket_id', '=', 'ticket.id')
            ->where('ticket.is_it', 1)
            ->where('po.ticket_id', '!=', null)
            ->where('po.end_date', '!=', null)
            ->where('po.end_date', '>=', now()->subDays(30))
            ->orderBy('ticket.created_at')
            ->get();

        foreach ($barang_jasa_it as $ticket) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->created_by_employee->name;

                $date = date_create($ticket->end_date);
                $end_date_po = date_format($date, "d F Y");
                $newAuth->end_date = $end_date_po;

                $newAuth->status = 'Reminder Barang Jasa Jenis IT';
                $newAuth->link = "/ticketmonitoring";
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


    public function getItBarangJasaReminderCount()
    {
        $barang_jasa_it = Ticket::leftJoin('po', 'po.ticket_id', '=', 'ticket.id')
            ->where('ticket.is_it', 1)
            ->where('po.ticket_id', '!=', null)
            ->where('po.end_date', '!=', null)
            ->where('po.end_date', '>=', now()->subDays(30))
            ->count();

        return $barang_jasa_it;
    }
}
