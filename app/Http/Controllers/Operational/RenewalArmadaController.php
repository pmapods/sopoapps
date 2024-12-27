<?php

namespace App\Http\Controllers\Operational;
use DB;
use Auth;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Armada;
use App\Models\ArmadaTicket;
use App\Models\RenewalArmada;
use App\Models\RenewalArmadaAuthorization;
use App\Models\ArmadaType;
use App\Models\SalesPoint;
use App\Models\Authorization;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\PoManual;
use App\Models\Po;
use App\Models\AuthorizationDetail;

class RenewalArmadaController extends Controller
{
    public function renewalArmadaView(){
        $employee_access = Auth::user()->location_access->pluck('salespoint_id');
        $employee_positions = EmployeePosition::all();
        $employees = Employee::all();
        $armada_types = ArmadaType::all();
        $renewalarmadas = RenewalArmada::all();
        $authorizations = Authorization::all();
        $authorization_details = AuthorizationDetail::all();
        $renewalauthorizations = RenewalArmadaAuthorization::all();
        $user_login = Auth::user()->id;
        $salespoints = SalesPoint::whereIn('id', $employee_access)->get();
        return view('Operational.Armada.renewalarmada',compact('user_login','employees','salespoints','renewalauthorizations','renewalarmadas', 'employee_positions', 'armada_types', 'authorization_details', 'authorizations'));
    }

    public function renewalArmadaData(Request $request) {
        $search_value = $request->search["value"];
        $employee_access = Auth::user()->location_access_list();

        $renewalTable = RenewalArmada::leftJoin('armada as a', 'a.id', '=', 'renewal_armada_detail.armada_id')
                ->leftJoin('salespoint as b', 'b.id', '=', 'renewal_armada_detail.last_salespoint_id')
                ->leftJoin('salespoint as c', 'c.id', '=', 'renewal_armada_detail.new_salespoint_id')
                ->leftJoin('armada_type', 'armada_type.id', '=', 'renewal_armada_detail.armada_type_id')
                ->leftJoin('employee', 'employee.id', '=', 'renewal_armada_detail.created_by')
                ->leftJoin('renewal_armada_authorization', 'renewal_armada_authorization.renewal_armada_id', '=', 'renewal_armada_detail.id')
                ->leftJoin('employee as d', 'd.id', 'renewal_armada_detail.rejected_by')
                ->leftJoin('employee as e', 'e.id', 'renewal_armada_detail.terminated_by')
                ->whereNull('renewal_armada_authorization.deleted_at');

        if ($request->status == -1) {
            $renewalTable = $renewalTable->where('renewal_armada_detail.status', '!=', 0);
        }
        else {
            $renewalTable = $renewalTable->where('renewal_armada_detail.status', '=', 0);
        }
        
        $renewalTable = $renewalTable->select('renewal_armada_detail.*', 'b.name as last_salespoint', 'c.name as new_salespoint', 'armada_type.*', 'renewal_armada_authorization.*', 'employee.name as emp_name', 'renewal_armada_detail.deleted_at as delete_at', 'd.name as reject_by_name', 'e.name as terminate_by_name')
                ->orderBy('renewal_armada_detail.id')
                ->get();
        
        $renewal_paginate = $renewalTable->skip($request->start)->take($request->length);

        $datas = [];
        $count = 1 + $request->start;
        foreach ($renewal_paginate as $renewals) {
            $array = [];

            $idRenewal = "";
            if(isset($renewals->id)) {
                $idRenewal = $renewals->id;
            }

            $codeRenewal = "";
            if(isset($renewals->code)) {
                $codeRenewal = $renewals->code;
            }

            $created_by_employee = "";
            if (isset($renewals->created_by)) {
                $created_by_employee = $renewals->emp_name;
            }

            $last_salespoint = "";
            if (isset($renewals->last_salespoint)) {
                $last_salespoint = $renewals->last_salespoint;
            }
            
            $new_salespoint = "";
            if (isset($renewals->new_salespoint)) {
                $new_salespoint = $renewals->new_salespoint;
            }
            
            $jenis_kendaraan = "";
            if (isset($renewals->name)) {
                $jenis_kendaraan = $renewals->name;
            }

            $old_plate = "";
            if (isset($renewals->old_plate)) {
                $old_plate = $renewals->old_plate . " (" . date('Y', strtotime($renewals->old_vehicle_year)) . ")";
            }
            
            $new_plate = "";
            if (isset($renewals->new_plate)) {
                $new_plate = $renewals->new_plate . " (" . date('Y', strtotime($renewals->new_vehicle_year)) . ")";
            }
            
            $status = "";
            $alasan = "";
            if (isset($renewals->status)) {
                // $status = $renewals->status;
                if ($renewals->status == 0) {
                    $status = "Waiting Approval " . $renewals->employee_name;
                }
                elseif ($renewals->status == -1) {
                    $alasan = $renewals->terminated_reason;
                    $status = "Terminate by " . $renewals->terminate_by_name . ", " . date('d F Y', strtotime($renewals->delete_at)) . ", Alasan : " . $alasan;
                }
                elseif ($renewals->status == 1) {
                    $status = "Approved by " . $renewals->employee_name . ", " . date('d F Y', strtotime($renewals->finished_date));
                }
                elseif ($renewals->status == 2) {
                    $alasan = $renewals->reject_reason;
                    $status = "Rejected by " . $renewals->reject_by_name . ", " . date('d F Y', strtotime($renewals->updated_at)) . ", Alasan : " . $alasan;
                }
            }

            $bastk_path = "";
            if (isset($renewals->bastk_path)) {
                $bastk_path = $renewals->bastk_path;
            }

            $approved_by = "";
            if (isset($renewals->approved_by)) {
                $approved_by = $renewals->approved_by;
            }

            $statusId = "";
            if (isset($renewals->status)) {
                $statusId = $renewals->status;
            }

            $armada_id = "";
            if (isset($renewals->armada_type_id)) {
                $armada_id = $renewals->armada_type_id;
            }

            $last_salespoint_id = "";
            if (isset($renewals->last_salespoint_id)) {
                $last_salespoint_id = $renewals->last_salespoint_id;
            }

            $new_salespoint_id = "";
            if (isset($renewals->new_salespoint_id)) {
                $new_salespoint_id = $renewals->new_salespoint_id;
            }

            $created_by = "";
            if (isset($renewals->created_by)) {
                $created_by = $renewals->created_by;
            }

            array_push($array, $count); 
            array_push($array, $codeRenewal); 
            array_push($array, $last_salespoint);
            array_push($array, $new_salespoint);
            array_push($array, $jenis_kendaraan);
            array_push($array, $old_plate);
            array_push($array, $new_plate);
            array_push($array, $created_by_employee);
            array_push($array, $status);
            array_push($array, $bastk_path);
            array_push($array, $approved_by);
            array_push($array, $statusId);
            array_push($array, $idRenewal); 
            array_push($array, $armada_id); 
            array_push($array, $last_salespoint_id); 
            array_push($array, $new_salespoint_id); 
            array_push($array, $created_by); 
            array_push($datas, $array);
            $count++;
        }

        return response()->json([
            "data" => $datas,
            "draw" => $request->draw,
            "recordsFiltered" => $renewalTable->count(),
            "recordsTotal" => $renewalTable->count(),
        ]);
    }

    public function addRenewalArmada(Request $request){
        try {
            DB::beginTransaction();
            $finished_date = null;
            $terminated_by = null;
            $reason = null;
            $deleted_at = null;
            $status = '0';

            $code_type = 'RNW01';

            if ($request->old_plate == "" || $request->old_plate == null || $request->new_plate == "" || $request->new_plate == null) {
                throw new \Exception('Nomor Plat tidak boleh ada yang kosong.');
            }

            $getArmada = $this->getArmadaByPlate($request->old_plate);
            $armadaData = json_decode($getArmada->getContent());

            $get_salespoint = SalesPoint::find($request->new_salespoint_id);
            
            $salespointname = str_replace(' ', '_', $get_salespoint->name);
            $initialsalespoint = strtoupper($get_salespoint->initial);

            $countRenewal = RenewalArmada::whereBetween('created_at', [
                                Carbon::now()->startOfMonth(),
                                Carbon::now()->endOfMonth(),
                            ])
                            ->withTrashed()
                            ->count();

            do {
                $codeRenewal = $code_type . "-" . $initialsalespoint . "-" . now()->translatedFormat('dmy') . str_repeat("0", 4 - strlen($countRenewal + 1)) . ($countRenewal + 1);
                $countRenewal++;
                $checkRenewal = RenewalArmada::where('code', $codeRenewal)->first();
                ($checkRenewal != null) ? $flag = false : $flag = true;
            } while (!$flag);
            
            $ext = pathinfo($request->file('bastk_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BASTK_" . $salespointname . '.' . $ext;
            $path = "/attachments/renewal/armada/" . $armadaData->data[0]->plate . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('bastk_file')->storeAs($file['dirname'], $file['basename'], 'public');

            $getArmadaId                           = Armada::where('plate', $armadaData->data[0]->plate)->first();

            if (!$getArmadaId) {
                $armadaOld                             = new Armada;
                $armadaOld->salespoint_id              = $request->last_salespoint_id;
                $armadaOld->armada_type_id             = $armadaData->data[0]->armada_type_id;
                $armadaOld->plate                      = $armadaData->data[0]->plate;
                $armadaOld->status                     = 0;
                $armadaOld->vehicle_year               = $request->old_vehicle_year.'-01-01';
                $armadaOld->save();
            }
            
            $newRenewalArmada                      = new RenewalArmada;
            $newRenewalArmada->code                = $codeRenewal;
            $newRenewalArmada->armada_id           = $getArmadaId->id;
            $newRenewalArmada->last_salespoint_id  = ($request->last_salespoint_id ?? null);
            $newRenewalArmada->new_salespoint_id   = ($request->new_salespoint_id ?? null);
            $newRenewalArmada->armada_type_id      = $getArmadaId->armada_type_id;
            $newRenewalArmada->old_plate           = str_replace(' ', '', strtoupper($request->old_plate));
            $newRenewalArmada->new_plate           = str_replace(' ', '', strtoupper($request->new_plate));
            $newRenewalArmada->new_vehicle_year    = $request->new_vehicle_year.'-01-01';
            $newRenewalArmada->old_vehicle_year    = $request->old_vehicle_year.'-01-01';
            $newRenewalArmada->type_unit           = ($request->type_unit ?? null);
            $newRenewalArmada->created_by          = Auth::user()->id;
            $newRenewalArmada->requirement_date    = now()->format('Y-m-d');
            $newRenewalArmada->approved_by         = $request->approved_by;
            $newRenewalArmada->finished_date       = $finished_date;
            $newRenewalArmada->terminated_by       = $terminated_by;
            $newRenewalArmada->terminated_reason   = $reason;
            $newRenewalArmada->status              = $status;
            $newRenewalArmada->deleted_at          = $deleted_at;
            $newRenewalArmada->bastk_path          = $path;
            $newRenewalArmada->created_at          = now()->format('Y-m-d');
            $newRenewalArmada->updated_at          = now()->format('Y-m-d');
            $newRenewalArmada->save();


            // add authorization renewal armada
            $employee = Employee::Find($newRenewalArmada->approved_by);
            $newRenewalArmadaAuthorization                        = new RenewalArmadaAuthorization;
            $newRenewalArmadaAuthorization->renewal_armada_id     = $newRenewalArmada->id;
            $newRenewalArmadaAuthorization->employee_id           = $newRenewalArmada->approved_by;
            $newRenewalArmadaAuthorization->employee_name         = $employee->name;
            $newRenewalArmadaAuthorization->employee_position_id  = $request->position;
            $newRenewalArmadaAuthorization->level                 = 1;
            $newRenewalArmadaAuthorization->created_at            = now()->format('Y-m-d');
            $newRenewalArmadaAuthorization->updated_at            = now()->format('Y-m-d');
            $newRenewalArmadaAuthorization->save();


            DB::commit();
            return back()->with('success','Berhasil menambahkan Renewal Armada')->with('menu','renewalarmada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menambahkan Renewal Armada ('.$ex->getMessage().')');
        }

    }

    public function getArmadabySalespoint($salespoint_id){
        $armadas = DB::select
            ("SELECT z.plate, z.armada_type_id, z.salespoint_id
                FROM
                (
                    SELECT 
                        a.plate, a.armada_type_id, a.salespoint_id
                    FROM armada a
                    LEFT JOIN
	                  (  
							  SELECT x.plate, x.armada_type_id, x.salespoint_id
	                        FROM
	                            (
	                                SELECT 
	                                		CASE
	                                			WHEN b.gt_plate IS NOT NULL THEN b.gt_plate
	                                			WHEN b.gt_plate IS NULL THEN b.gs_plate
	                                			ELSE ''
	                                		END AS plate,
													b.armada_type_id, b.salespoint_id 
	                                FROM po_manual b
	                                WHERE b.salespoint_id = $salespoint_id
	                            ) x
	                    WHERE x.plate IS NOT NULL AND x.plate != ''
	                  ) m
	               ON a.plate = m.plate AND a.salespoint_id = m.salespoint_id
	               WHERE a.salespoint_id = $salespoint_id AND a.deleted_at IS NULL
                ) z
            GROUP BY z.plate, z.armada_type_id, z.salespoint_id");
        
        return response()->json([
            'data' => $armadas
        ]);
    }

    public function getArmadaTypebyID($armada_type_id){
        $armadas = ArmadaType::where('id', $armada_type_id)
                    ->where('isNiaga', '!=', 2)
                    ->get();

        return response()->json([
           'data' => $armadas
        ]);
    }

    public function getArmadaID(Request $request){
        $salespoint_id = $request->input('last_salespoint_id');
        $armada_type_id = $request->input('armada_type_id');
        $plate_number = $request->input('old_plate');

        $armadas = Armada::where('salespoint_id', $salespoint_id)
                        ->where('armada_type_id', $armada_type_id)
                        ->where('plate', $plate_number)
                        ->get();

        return response()->json([
            'data' => $armadas
        ]);
    }

    public function getArmadaByPlate($plate){
        $armadas = DB::select
            ("SELECT z.plate, z.armada_type_id, z.salespoint_id, z.vehicle_year
                FROM
                (
                    SELECT 
                        a.plate, a.armada_type_id, a.salespoint_id, LEFT(a.vehicle_year, 4) AS vehicle_year
                    FROM armada a
                    -- WHERE a.salespoint_id = 383
                    UNION ALL
                    SELECT x.plate, x.armada_type_id, x.salespoint_id, x.vehicle_year
                        FROM
                            (
                                SELECT 
                                    b.gt_plate AS plate, b.armada_type_id, b.salespoint_id, 2024 AS vehicle_year
                                FROM po_manual b
                                -- WHERE b.salespoint_id = 383
                                    UNION ALL
                                SELECT 
                                    b.gs_plate AS plate, b.armada_type_id, b.salespoint_id, 2024 AS vehicle_year
                                FROM po_manual b
                                -- WHERE b.salespoint_id = 383
                            ) x
                    WHERE x.plate IS NOT NULL AND x.plate != ''
                ) z
            WHERE z.plate = '$plate'
            GROUP BY z.plate, z.armada_type_id, z.salespoint_id, z.vehicle_year");

        return response()->json([
            'data' => $armadas
        ]);
    }

    public function updateRenewalArmada(Request $request){
        try {
            DB::beginTransaction();
            $terminated_by = null;
            $reason = null;
            $deleted_at = null;
            if ($request->terminated_by){
                $status = '2';
                $deleted_at = now()->format('Y-m-d');
            }else{
                $status = '1';
            }
            $getRenewalArmada                   = RenewalArmada::findOrFail($request->id);
            $RenewalArmada->approved_by         = Auth::user()->id;
            $RenewalArmada->finished_date       = now()->format('Y-m-d');
            if ($request->terminated_by){
                $RenewalArmada->terminated_by       = $request->terminated_by;
                $RenewalArmada->terminated_reason   = $request->reason;
            }else{
                $RenewalArmada->terminated_by       = $terminated_by;
                $RenewalArmada->terminated_reason   = $reason;
            }
            $RenewalArmada->status              = $status;
            $RenewalArmada->deleted_at          = $deleted_at;
            $RenewalArmada->created_at          = $getRenewalArmada->created_at;
            $RenewalArmada->updated_at          = now()->format('Y-m-d');

            $RenewalArmada->save();
            DB::commit();

            $this->updateArmada();

            return back()->with('success','Berhasil update renewal armada');

        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal update renewal armada ('.$ex->getMessage().')');
        }

    }

    public function confirmRenewal(Request $request)
    {
        // Superadmin Only
        if (
            Auth::user()->id != 1 &&
            Auth::user()->id != 115 &&
            Auth::user()->id != 120 &&
            Auth::user()->id != 117 &&
            Auth::user()->id != 118 &&
            Auth::user()->id != 809
        ) {
            return back()->with('error', 'Hanya Admin dan Purchasing yang dapat confim tiket');
        }

        $open_request = null;
        $po_normals = null;
        $po_manuals = null;
        $po_armada_tickets = null;
        $searchPlate = null;
        try {
            DB::beginTransaction();
            $open_request                = RenewalArmada::where('code', $request->id)->first();
            $open_request->approved_by   = Auth::user()->id;
            $open_request->status        = 1;
            $open_request->finished_date = now()->format('Y-m-d');
            $open_request->updated_at    = now()->format('Y-m-d');
            $open_request->save();
            
            // cek armada tiket PO status 4 / belum cls PO
            $po_armada_tickets = ArmadaTicket::whereNotIn('status', [4])
            ->where('salespoint_id', '==', $open_request->last_salespoint_id)
            ->where('armada_type_id', '==', $open_request->armada_type_id)
            ->where('armada_id', '==', $open_request->armada_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

            if ($po_armada_tickets != null) {
                $po_normals = Po::where('no_po_sap', $po_armada_tickets->po_reference_number)->first();
            } else {
                // cek armada tiket PO dengan status 6
                $po_armada_tickets = ArmadaTicket::where('status', 6)
                    ->where('salespoint_id', $open_request->last_salespoint_id)
                    ->where('armada_type_id', $open_request->armada_type_id)
                    ->where('armada_id', $open_request->armada_id)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->first();            

                if ($po_armada_tickets != null) {
                    $po_normals = Po::where('no_po_sap', $po_armada_tickets->po_number)
                                ->whereNotIn('status', [-1, 4])
                                ->first();
                }
            }

            $searchPlate = $open_request->old_plate;

            if ($po_normals != null) {
                // Cek di Tiket Armada
                $update_armada_ticket = ArmadaTicket::where('code', $po_armada_tickets->code)->first();
                $update_armada_ticket->gt_plate  = $open_request->new_plate;
                $update_armada_ticket->save();
                $msg = 'PO ' . $po_normals->no_po_sap;
            } else {
                // cek Po Manual dengan transaksi aktif
                $po_manuals = PoManual::where('salespoint_id', $open_request->last_salespoint_id)
                ->where('armada_type_id', $open_request->armada_type_id)
                ->whereNotIn('status', [-1, 4])
                ->where(function ($query) use ($searchPlate) {
                    $query->where('gs_plate', $searchPlate)
                        ->orWhere('gt_plate', $searchPlate);
                })->first();

                

                if($po_manuals != null) {                    
                    $po_manuals->gt_plate  = $open_request->new_plate;
                    $po_manuals->gs_plate  = null;
                    $po_manuals->save();
                    
                    $msg = 'PO Manual ' . $po_manuals->po_number;
                }
            }

            if (is_null($po_normals) && is_null($po_manuals)){
                DB::rollback();
                return back()->with('error', 'Gagal Konfirmasi Renewal Armada data PO tidak ditemukan');
            }

            // update master armada
            $get_armada                = Armada::find($open_request->armada_id);
            if ($get_armada == null) {
                $get_armada            = new Armada;

                if ($po_manuals != null) {
                    $get_armada->armada_type_id = $po_manuals->armada_type_id;
                }
            }
            $get_armada->salespoint_id = $open_request->new_salespoint_id;
            $get_armada->plate         = $open_request->new_plate;
            $get_armada->vehicle_year  = $open_request->new_vehicle_year;
            $get_armada->updated_at    = now()->format('Y-m-d');
            $get_armada->save();

            DB::commit();
            return back()->with('success', 'Berhasil Konfirmasi Renewal Armada (' . $msg . ')');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Konfirmasi Renewal Armada (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }

    public function terminateRenewal(Request $request)
    {
        // Superadmin Only
        if (
            Auth::user()->id != 1 &&
            Auth::user()->id != 115 &&
            Auth::user()->id != 120 &&
            Auth::user()->id != 117 &&
            Auth::user()->id != 118 &&
            Auth::user()->id != 809
        ) {
            return back()->with('error', 'Hanya Admin dan Purchasing yang dapat membatalkan tiket');
        }
        try {
            DB::beginTransaction();
            $open_request                      = RenewalArmada::findOrFail($request->id);
            $open_request->terminated_by       = Auth::user()->id;
            $open_request->status              = -1;
            $open_request->terminated_reason   = $request->reason;
            $open_request->deleted_at          = now()->format('Y-m-d');
            $open_request->updated_at          = now()->format('Y-m-d');
            $open_request->save();

            DB::commit();
            return back()->with('success', 'Berhasil membatalkan renewal armada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membatalkan renewal armada (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }

    public function rejectRenewal(Request $request)
    {
        // Superadmin Only
        if (
            Auth::user()->id != 1 &&
            Auth::user()->id != 115 &&
            Auth::user()->id != 120 &&
            Auth::user()->id != 117 &&
            Auth::user()->id != 118 &&
            Auth::user()->id != 809
        ) {
            return back()->with('error', 'Hanya Admin dan Purchasing yang dapat mereject tiket');
        }
        try {
            DB::beginTransaction();
            $open_request                    = RenewalArmada::where($request->id);
            $open_request->rejected_by       = Auth::user()->id;
            $open_request->status            = 2;
            $open_request->reject_reason     = $request->reason;
            $open_request->updated_at        = now()->format('Y-m-d');
            $open_request->save();

            DB::commit();
            return back()->with('success', 'Berhasil menolak renewal armada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal menolak renewal armada (' . $ex->getMessage() . ' [' . $ex->getLine() . '])');
        }
    }

}
