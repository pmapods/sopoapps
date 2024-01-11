<?php

namespace App\Http\Controllers\Operational;
use DB;
use Auth;

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

class RenewalArmadaController extends Controller
{
    public function renewalArmadaView(){
        $employee_access = Auth::user()->location_access->pluck('salespoint_id');
        $employee_positions = EmployeePosition::all();
        $employees = Employee::all();
        $armada_types = ArmadaType::all();
        $renewalarmadas = RenewalArmada::all();
        $renewalauthorizations = RenewalArmadaAuthorization::all();
        $salespoint = SalesPoint::find($employee_access[0]);
        $salespoints = SalesPoint::whereIn('id', $employee_access)->get();
        return view('Operational.Armada.renewalarmada',compact('employees','salespoints','renewalauthorizations','renewalarmadas', 'employee_positions', 'armada_types'));
    }

    public function addRenewalArmada(Request $request){
        try {
            DB::beginTransaction();
            $finished_date = null;
            $terminated_by = null;
            $reason = null;
            $deleted_at = null;
            $status = '0';

            if ($request->old_plate == "" || $request->old_plate == null || $request->new_plate == "" || $request->new_plate == null) {
                throw new \Exception('Nomor Plat tidak boleh ada yang kosong.');
            }

            $getArmada = $this->getArmadaByPlate($request->old_plate);
            $armadaData = json_decode($getArmada->getContent());

            $get_salespoint = SalesPoint::find($request->new_salespoint_id);

            $salespointname = str_replace(' ', '_', $get_salespoint->name);

            $ext = pathinfo($request->file('bastk_file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "BASTK_" . $salespointname . '.' . $ext;
            $path = "/attachments/renewal/armada/" . $armadaData->data[0]->id . '/' . $name;
            $file = pathinfo($path);
            $path = $request->file('bastk_file')->storeAs($file['dirname'], $file['basename'], 'public');

            $newRenewalArmada                      = new RenewalArmada;
            $newRenewalArmada->armada_id           = $armadaData->data[0]->id;
            $newRenewalArmada->last_salespoint_id  = ($request->last_salespoint_id ?? null);
            $newRenewalArmada->new_salespoint_id   = ($request->new_salespoint_id ?? null);
            $newRenewalArmada->armada_type_id      = $armadaData->data[0]->armada_type_id;
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
        $armadas = Armada::where('salespoint_id',$salespoint_id)->get();
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
        $armadas = Armada::where('plate', $plate)
                        ->get();

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
            dd($request);
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
            Auth::user()->id != 116 &&
            Auth::user()->id != 117 &&
            Auth::user()->id != 197 &&
            Auth::user()->id != 118 &&
            Auth::user()->id != 717 &&
            Auth::user()->id != 153
        ) {
            return back()->with('error', 'Hanya Admin dan Purchasing yang dapat membatalkan tiket');
        }

        $open_request = null;
        $msgPo = null;
        try {
            DB::beginTransaction();
            $open_request                = RenewalArmada::findOrFail($request->id);
            $open_request->approved_by   = Auth::user()->id;
            $open_request->status        = 1;
            $open_request->finished_date = now()->format('Y-m-d');
            $open_request->updated_at    = now()->format('Y-m-d');
            $open_request->save();

            // cek Po Normal
            $po_armada_tickets = ArmadaTicket::whereIn('po_number', function ($query) use ($open_request) {
                $query->select('no_po_sap')
                    ->distinct()
                    ->from('po')
                    ->whereIn('no_po_sap', function ($subQuery) use ($open_request) {
                        $subQuery->select('po_number')
                                    ->distinct()
                                    ->from('armada_ticket')
                                    ->where('salespoint_id', $open_request->last_salespoint_id)
                                    ->where('armada_type_id', $open_request->armada_type_id)
                                    ->where('armada_id', $open_request->armada_id)
                                    ->where('status', '6')
                                    ->whereIn('ticketing_type', [0, 1, 2])
                                    ->whereNull('deleted_at');
                    })
                    ->where('status', 3)
                    ->whereNull('deleted_at');
            })->get();


            // cek Po Manual dengan transaksi aktif
            $searchPlate = $open_request->old_plate;
            $po_manuals = PoManual::where('salespoint_id', $open_request->last_salespoint_id)
                ->where('armada_type_id', $open_request->armada_type_id)
                ->where('status', 3)
                ->where(function ($query) use ($searchPlate) {
                    $query->where('gs_plate', $searchPlate)
                        ->orWhere('gt_plate', $searchPlate);
                })->get();

            if($po_armada_tickets){
                foreach($po_armada_tickets as $po_armada){
                    $update_po            = Po::find($po_armada['no_po_sap']);
                    $update_po->gt_plate  = $open_request->new_plate;
                    $update_po->gs_plate  = $open_request->new_plate;
                    $update_po->save();
                }

                $msg = 'dan PO';

            } elseif($po_manuals) {
                foreach($po_manuals as $po_manual){
                    $update_po_manual            = PoManual::find($po_manual['po_number']);
                    $update_po_manual->gt_plate  = $open_request->new_plate;
                    $update_po_manual->gs_plate  = $open_request->new_plate;
                    $update_po_manual->save();
                }

                $msg = 'dan PO Manual';
            }

            $msg = 'dan tidak ada PO';

            // update master armada
            $get_armada                = Armada::find($open_request->armada_id);
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
            Auth::user()->id != 116 &&
            Auth::user()->id != 117 &&
            Auth::user()->id != 197 &&
            Auth::user()->id != 118 &&
            Auth::user()->id != 717 &&
            Auth::user()->id != 153
        ) {
            return back()->with('error', 'Hanya Admin dan Purchasing yang dapat membatalkan tiket');
        }
        try {
            DB::beginTransaction();
            $open_request                    = RenewalArmada::findOrFail($request->id);
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
