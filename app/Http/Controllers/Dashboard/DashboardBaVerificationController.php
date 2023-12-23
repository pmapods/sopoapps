<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\PoManual;
use App\Models\SalesPoint;
use App\Models\ArmadaTicket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TicketingBlockOpenRequest;

class DashboardBaVerificationController extends Controller
{
    public function dashboardBaVerificationView()
    {
        return view('Dashboard.baverification');
    }

    public function getBAverification()
    {
        $data = [];
        $ticketing_block_open_request = TicketingBlockOpenRequest::where('status', 0)
            ->orderBy('created_at')
            ->get();

        foreach ($ticketing_block_open_request as $ticketing) {
            try {
                $newAuth = new \stdClass();

                $newAuth->ticket_code = $ticketing->ticket_code;
                $newAuth->po_number = $ticketing->po_number;
                $newAuth->created_at = $ticketing->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticketing->created_by_employee->name;
                $newAuth->transaction_type = 'Verifikasi BA Upload';
                $newAuth->status = $ticketing->status_name();
                $newAuth->link = "/ticketing/BAVerification/";

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

    public function getBAverificationCount()
    {
        $BAverification = TicketingBlockOpenRequest::where('status', 0)->count();

        return $BAverification;
    }
}
