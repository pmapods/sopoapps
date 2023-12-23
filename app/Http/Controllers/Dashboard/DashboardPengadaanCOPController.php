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
use App\Models\EmployeeLocationAccess;
use App\Models\TicketingBlockOpenRequest;

class DashboardPengadaanCOPController extends Controller
{
    public function dashboardPengadaanCOPView()
    {
        return view('Dashboard.pengadaancop');
    }

    public function getPengadaanCOP()
    {
        $data = [];
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');

        $pengadaan_cops = Ticket::where('custom_settings', '!=', null)
            ->where('status', '!=', -1)
            ->where('custom_settings', 'like', '%Pengadaan Fasilitas Karyawan COP%')
            ->where('status', '=', 6)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->orderBy('created_at')
            ->get();

        foreach ($pengadaan_cops as $cop) {
            try {
                $newAuth = new \stdClass();

                $newAuth->salespoint = $cop->salespoint->name;
                $newAuth->ticket_code = $cop->code;
                $newAuth->created_at = $cop->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $cop->created_by_employee->name;
                $newAuth->transaction_type = 'Pengadaan COP';

                if ($cop->po->count() == 0) {
                    $newAuth->status = 'Menunggu Proses PO';
                } elseif (
                    $cop->agreement_filepath == null &&
                    $cop->tor_filepath == null &&
                    $cop->sph_filepath == null && $cop->po->count() > 0
                ) {
                    $newAuth->status = 'Menunggu Upload File Legal';
                } elseif ($cop->agreement_filepath_status == 0) {
                    $newAuth->status = 'Menunggu Approval File Agreement Legal';
                } elseif ($cop->agreement_filepath_status == -1) {
                    $newAuth->status = 'File Agreement Di Tolak, Alasan : ' . $cop->agreement_filepath_reject_notes;
                } elseif ($cop->tor_filepath_status == 0) {
                    $newAuth->status = 'Menunggu Approval File TOR Legal';
                } elseif ($cop->tor_filepath_status == -1) {
                    $newAuth->status = 'File TOR Di Tolak, Alasan : ' . $cop->tor_filepath_reject_notes;
                } elseif ($cop->sph_filepath_status == 0) {
                    $newAuth->status = 'Menunggu Approval File SPH Legal';
                } elseif ($cop->sph_filepath_status == -1) {
                    $newAuth->status = 'File SPH Di Tolak, Alasan : ' . $cop->sph_filepath_reject_notes;
                } elseif (
                    $cop->agreement_filepath_status == 1 &&
                    $cop->tor_filepath_status == 1 &&
                    $cop->sph_filepath_status == 1 &&
                    $cop->user_agreement_filepath_status == null
                ) {
                    $newAuth->status = 'Menunggu Upload File Perjanjian';
                } elseif ($cop->user_agreement_filepath_status == 0) {
                    $newAuth->status = 'Menunggu Approval File Perjanjian';
                } elseif ($cop->user_agreement_filepath_status == -1) {
                    $newAuth->status = 'File Perjanjian Di Tolak, Alasan : ' . $cop->user_agreement_filepath_reject_notes;
                } elseif (
                    $cop->agreement_filepath_status == 1 &&
                    $cop->tor_filepath_status == 1 &&
                    $cop->sph_filepath_status == 1 &&
                    $cop->user_agreement_filepath_status == 1 &&
                    $cop->is_over_plafon == 0 &&
                    $cop->bastk_cop_filepath == null
                ) {
                    $newAuth->status = 'Menunggu Upload BASTK';
                } elseif (
                    $cop->agreement_filepath_status == 1 &&
                    $cop->tor_filepath_status == 1 &&
                    $cop->sph_filepath_status == 1 &&
                    $cop->user_agreement_filepath_status == 1 &&
                    $cop->is_over_plafon == 1 &&
                    $cop->over_plafon_status == null
                ) {
                    $newAuth->status = 'Menunggu Upload Bukti Transfer Over Plafon';
                } elseif ($cop->over_plafon_status == 0) {
                    $newAuth->status = 'Menunggu Approval Bukti Transfer Over Plafon';
                } elseif ($cop->over_plafon_status == -1) {
                    $newAuth->status = 'File Bukti Transfer Over Plafon Di Tolak, Alasan : ' . $cop->over_plafon_reject_notes;
                } elseif (
                    $cop->agreement_filepath_status == 1 &&
                    $cop->tor_filepath_status == 1 &&
                    $cop->sph_filepath_status == 1 &&
                    $cop->user_agreement_filepath_status == 1 &&
                    $cop->is_over_plafon == 1 &&
                    $cop->over_plafon_status == 1 &&
                    $cop->bastk_cop_filepath == null
                ) {
                    $newAuth->status = 'Menunggu Upload BASTK';
                } elseif ($cop->bastk_cop_filepath != null) {
                    $newAuth->status = 'Menunggu Upload LPB';
                } else {
                    $newAuth->status = 'Menunggu Proses PO';
                }

                $newAuth->link = "/ticketing/" . $newAuth->ticket_code;

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

    public function getPengadaanCOPCount()
    {
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        $pengadaan_cops = Ticket::where('custom_settings', '!=', null)
            ->where('status', '!=', -1)
            ->where('custom_settings', 'like', '%Pengadaan Fasilitas Karyawan COP%')
            ->where('status', '=', 6)
            ->whereIn('salespoint_id', $salespoint_ids)
            ->count();

        return $pengadaan_cops;
    }
}
