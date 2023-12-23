<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use App\Models\Po;
use Carbon\Carbon;
use App\Models\PoManual;
use App\Models\SalesPoint;
use App\Models\ArmadaTicket;
use App\Http\Controllers\Controller;
use App\Models\EmployeeLocationAccess;

class DashboardPoWillExpiredController extends Controller
{
    public function dashboardPoWillExpiredView()
    {
        return view('Dashboard.powillexpired');
    }

    public function getPoWillExpired()
    {
        $user_location_access  = EmployeeLocationAccess::where('employee_id', Auth::user()->id)->get()->pluck('salespoint_id');

        // bukan security
        $pos = Po::where("no_po_sap", '!=', "unset")
            ->where("no_po_sap", '!=', null)
            ->where('end_date', '!=', null)
            ->where('security_ticket_id', null)
            ->where('end_date', '<', now()->addDays(30)->format('Y-m-d'))
            ->where('status', 3)
            ->orderBy('created_at')
            ->get();

        // security
        $security_pos = Po::where("no_po_sap", '!=', "unset")
            ->where("no_po_sap", '!=', null)
            ->where('end_date', '!=', null)
            ->where('security_ticket_id', '!=', null)
            ->where('end_date', '<', now()->addDays(60)->format('Y-m-d'))
            ->where('status', 3)
            ->orderBy('created_at')
            ->get();

        $pos = $pos->merge($security_pos);
        $datas = [];
        foreach ($pos as $po) {
            $data = new \stdClass();
            if ($po->ticket) {
                $data->salespoint_name = $po->ticket->salespoint->name;
                $salespoint_id =  $po->ticket->salespoint->id;
                $data->transaction_type = "Barang Jasa";
                $data->link = "/ticketmonitoring?po_number=" . $po->no_po_sap;
            }
            if ($po->armada_ticket) {
                $data->salespoint_name = $po->armada_ticket->salespoint->name;
                $salespoint_id =  $po->armada_ticket->salespoint->id;
                $data->transaction_type = "Armada";
                $data->link = "/armadamonitoring?po_number=" . $po->no_po_sap;
            }
            if ($po->security_ticket) {
                $data->salespoint_name = $po->security_ticket->salespoint->name;
                $salespoint_id =  $po->security_ticket->salespoint->id;
                $data->transaction_type = "Security";
                $data->link = "/securitymonitoring?po_number=" . $po->no_po_sap;
            }
            if (!in_array($salespoint_id, $user_location_access->toArray())) {
                continue;
            }
            // cek apakah po sudah di proses, jika sudah tidak perlu dimunculkan
            $armada_ticket = ArmadaTicket::where('po_reference_number', $po->no_po_sap)->where('status', '!=', -1)->first();
            if ($armada_ticket) {
                continue;
            }
            $data->po_number = $po->no_po_sap;
            $data->vendor_name = $po->sender_name;
            $data->start_date = $po->start_date ?? '-';
            $data->end_date = $po->end_date ?? '-';
            $data->status = $po->status();
            array_push($datas, $data);
        }
        $pos = PoManual::where('end_date', '!=', null)
            ->where('end_date', '<', now()->addDays(30)->format('Y-m-d'))
            ->where('status', 3)
            ->where('barang_jasa_form_bidding_filepath', '=', null)
            ->where('barang_jasa_pr_manual_filepath', '=', null)
            ->where('barang_jasa_po_filepath', '=', null)
            ->where('barang_jasa_lpb_filepath', '=', null)
            ->where('barang_jasa_invoice_filepath', '=', null)
            ->where('armada_pr_manual_filepath', '=', null)
            ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
            ->where('cit_lpb_filepath', '=', null)
            ->where('pestcontrol_lpb_filepath', '=', null)
            ->where('merchandiser_lpb_filepath', '=', null)
            ->orderBy('created_at')
            ->get();

        $security_pos = PoManual::where('end_date', '!=', null)
            ->where('end_date', '<', now()->addDays(60)->format('Y-m-d'))
            ->where('status', 3)
            ->where('barang_jasa_form_bidding_filepath', '=', null)
            ->where('barang_jasa_pr_manual_filepath', '=', null)
            ->where('barang_jasa_po_filepath', '=', null)
            ->where('barang_jasa_lpb_filepath', '=', null)
            ->where('barang_jasa_invoice_filepath', '=', null)
            ->where('armada_pr_manual_filepath', '=', null)
            ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
            ->where('cit_lpb_filepath', '=', null)
            ->where('pestcontrol_lpb_filepath', '=', null)
            ->where('merchandiser_lpb_filepath', '=', null)
            ->orderBy('created_at')
            ->get();

        $pos = $pos->merge($security_pos);
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->salespoint_name = $po->salespoint_name;
            $data->po_number = $po->po_number;
            $data->transaction_type = strtoupper($po->category_name) . " (manual)";
            if (strtoupper($po->category_name) == "ARMADA") {
                $data->link = "/armadamonitoring?po_number=" . $po->po_number;
            }
            if (strtoupper($po->category_name) == "SECURITY") {
                $data->link = "/securitymonitoring?po_number=" . $po->po_number;
            }
            if (strtoupper($po->category_name) == "CIT") {
                $data->link = "/citmonitoring?po_number=" . $po->po_number;
            }
            if (strtoupper($po->category_name) == "PEST CONTROL") {
                $data->link = "/pestmonitoring?po_number=" . $po->po_number;
            }
            $salespoint = SalesPoint::where(DB::raw('trim(lower(name))'), 'LIKE', '%' . trim(strtolower($data->salespoint_name)))->first();
            if (!in_array($salespoint->id ?? null, $user_location_access->toArray())) {
                continue;
            }
            // cek apakah po sudah di proses, jika sudah tidak perlu dimunculkan
            $armada_ticket = ArmadaTicket::where('po_reference_number', $po->po_number)->where('status', '!=', -1)->first();
            if ($armada_ticket) {
                continue;
            }
            $data->vendor_name = $po->vendor_name;
            $data->start_date = $po->start_date ?? '-';
            $data->end_date = $po->end_date ?? '-';
            $data->status = $po->status();
            array_push($datas, $data);
        }

        // hanya munculkan po di bulan ini
        $datas = collect($datas);
        $datas = $datas->filter(function ($po) {
            if (Carbon::parse($po->end_date)->month <= now()->month && Carbon::parse($po->end_date)->year <= now()->year) {
                return true;
            } else {
                return false;
            };
        });

        $data = array_values($datas->toArray());
        foreach ($data as $key => &$xxx) {
            $xxx->nomor = $key + 1;
        }

        return response()->json([
            "data" => $data,
        ]);
    }

    public function getPoWillExpiredCount()
    {
        $user_location_access  = EmployeeLocationAccess::where('employee_id', Auth::user()->id)->get()->pluck('salespoint_id');

        // bukan security
        $pos = Po::where("no_po_sap", '!=', "unset")
            ->where("no_po_sap", '!=', null)
            ->where('end_date', '!=', null)
            ->where('security_ticket_id', null)
            ->where('end_date', '<', now()->addDays(30)->format('Y-m-d'))
            ->where('status', 3)
            ->get();

        // security
        $security_pos = Po::where("no_po_sap", '!=', "unset")
            ->where("no_po_sap", '!=', null)
            ->where('end_date', '!=', null)
            ->where('security_ticket_id', '!=', null)
            ->where('end_date', '<', now()->addDays(60)->format('Y-m-d'))
            ->where('status', 3)
            ->get();

        $pos = $pos->merge($security_pos);
        $datas = [];
        foreach ($pos as $po) {
            $data = new \stdClass();
            if ($po->ticket) {
                $data->salespoint_name = $po->ticket->salespoint->name;
                $salespoint_id =  $po->ticket->salespoint->id;
                $data->transaction_type = "Barang Jasa";
                $data->link = "/ticketmonitoring?po_number=" . $po->no_po_sap;
            }
            if ($po->armada_ticket) {
                $data->salespoint_name = $po->armada_ticket->salespoint->name;
                $salespoint_id =  $po->armada_ticket->salespoint->id;
                $data->transaction_type = "Armada";
                $data->link = "/armadamonitoring?po_number=" . $po->no_po_sap;
            }
            if ($po->security_ticket) {
                $data->salespoint_name = $po->security_ticket->salespoint->name;
                $salespoint_id =  $po->security_ticket->salespoint->id;
                $data->transaction_type = "Security";
                $data->link = "/securitymonitoring?po_number=" . $po->no_po_sap;
            }
            if (!in_array($salespoint_id, $user_location_access->toArray())) {
                continue;
            }
            // cek apakah po sudah di proses, jika sudah tidak perlu dimunculkan
            $armada_ticket = ArmadaTicket::where('po_reference_number', $po->no_po_sap)->where('status', '!=', -1)->first();
            if ($armada_ticket) {
                continue;
            }
            $data->po_number = $po->no_po_sap;
            $data->vendor_name = $po->sender_name;
            $data->start_date = $po->start_date ?? '-';
            $data->end_date = $po->end_date ?? '-';
            $data->status = $po->status();
            array_push($datas, $data);
        }
        $pos = PoManual::where('end_date', '!=', null)
            ->where('end_date', '<', now()->addDays(30)->format('Y-m-d'))
            ->where('status', 3)
            ->where('barang_jasa_form_bidding_filepath', '=', null)
            ->where('barang_jasa_pr_manual_filepath', '=', null)
            ->where('barang_jasa_po_filepath', '=', null)
            ->where('barang_jasa_lpb_filepath', '=', null)
            ->where('barang_jasa_invoice_filepath', '=', null)
            ->where('armada_pr_manual_filepath', '=', null)
            ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
            ->where('cit_lpb_filepath', '=', null)
            ->where('pestcontrol_lpb_filepath', '=', null)
            ->where('merchandiser_lpb_filepath', '=', null)
            ->get();

        $security_pos = PoManual::where('end_date', '!=', null)
            ->where('end_date', '<', now()->addDays(60)->format('Y-m-d'))
            ->where('status', 3)
            ->where('barang_jasa_form_bidding_filepath', '=', null)
            ->where('barang_jasa_pr_manual_filepath', '=', null)
            ->where('barang_jasa_po_filepath', '=', null)
            ->where('barang_jasa_lpb_filepath', '=', null)
            ->where('barang_jasa_invoice_filepath', '=', null)
            ->where('armada_pr_manual_filepath', '=', null)
            ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
            ->where('cit_lpb_filepath', '=', null)
            ->where('pestcontrol_lpb_filepath', '=', null)
            ->where('merchandiser_lpb_filepath', '=', null)
            ->get();

        $pos = $pos->merge($security_pos);
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->salespoint_name = $po->salespoint_name;
            $data->po_number = $po->po_number;
            $data->transaction_type = strtoupper($po->category_name) . " (manual)";
            if (strtoupper($po->category_name) == "ARMADA") {
                $data->link = "/armadamonitoring?po_number=" . $po->po_number;
            }
            if (strtoupper($po->category_name) == "SECURITY") {
                $data->link = "/securitymonitoring?po_number=" . $po->po_number;
            }
            if (strtoupper($po->category_name) == "CIT") {
                $data->link = "/citmonitoring?po_number=" . $po->po_number;
            }
            if (strtoupper($po->category_name) == "PEST CONTROL") {
                $data->link = "/pestmonitoring?po_number=" . $po->po_number;
            }
            $salespoint = SalesPoint::where(DB::raw('trim(lower(name))'), 'LIKE', '%' . trim(strtolower($data->salespoint_name)))->first();
            if (!in_array($salespoint->id ?? null, $user_location_access->toArray())) {
                continue;
            }
            // cek apakah po sudah di proses, jika sudah tidak perlu dimunculkan
            $armada_ticket = ArmadaTicket::where('po_reference_number', $po->po_number)->where('status', '!=', -1)->first();
            if ($armada_ticket) {
                continue;
            }
            $data->vendor_name = $po->vendor_name;
            $data->start_date = $po->start_date ?? '-';
            $data->end_date = $po->end_date ?? '-';
            $data->status = $po->status();
            array_push($datas, $data);
        }

        // hanya munculkan po di bulan ini
        $datas = collect($datas);
        $datas = $datas->filter(function ($po) {
            if (Carbon::parse($po->end_date)->month <= now()->month && Carbon::parse($po->end_date)->year <= now()->year) {
                return true;
            } else {
                return false;
            };
        });

        return response()->json([
            "total" => count($datas->toArray()),
        ]);
    }
}
