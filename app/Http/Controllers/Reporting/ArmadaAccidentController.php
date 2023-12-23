<?php

namespace App\Http\Controllers\Reporting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;

use App\Models\SalesPoint;
use App\Models\ArmadaAccident;
use App\Models\VehicleIdentity;
use App\Models\DriverIdentity;
use App\Models\AccidentPicArea;
use App\Models\AccidentCost;
use App\Models\LegalAspect;
use App\Models\InsuranceAspect;
use App\Models\RecoveryAccidentCost;
use App\Models\Armada;

use Carbon\Carbon;

class ArmadaAccidentController extends Controller
{
    public function armadaAccidentView(){
        $access = Auth::user()->location_access->pluck('salespoint_id');
        if(Auth::user()->id == 1){
            $access = SalesPoint::all()->pluck('id');
        }
        $salespoints = SalesPoint::whereIn('id',$access)->get();
        $armada_accidents = ArmadaAccident::whereIn('salespoint_id',$access)->get();
        return view('Reporting.armadaaccident',compact('salespoints','armada_accidents'));
    }

    public function armadaAccidentDetailView($armada_accident_id){
        $access = Auth::user()->location_access->pluck('salespoint_id');
        if(Auth::user()->id == 1){
            $access = SalesPoint::all()->pluck('id');
        }
        $armada_accident = ArmadaAccident::findOrFail($armada_accident_id);

        if(!in_array($armada_accident->id,$access->toArray())){
            return back()->with('error','Access Denied (non access salespoint)');
        }
        $armadas =  Armada::where('salespoint_id',$armada_accident->salespoint_id)->get();
        return view('Reporting.armadaaccidentdetail',compact('armada_accident','armadas'));
    }

    public function armadaAccidentCreate(Request $request){
        try{
            DB::beginTransaction();
            $armada_accident                        = new ArmadaAccident;
            $armada_accident->salespoint_id         = $request->salespoint_id;
            $armada_accident->description           = $request->description;
            $armada_accident->created_by            = Auth::user()->id;
            $armada_accident->save();

            $vehicle_identity = new VehicleIdentity;
            $vehicle_identity->armada_accident_id = $armada_accident->id;
            $vehicle_identity->save();

            $driver_identity = new DriverIdentity;
            $driver_identity->armada_accident_id = $armada_accident->id;
            $driver_identity->save();

            $accident_pic_area = new AccidentPicArea;
            $accident_pic_area->armada_accident_id = $armada_accident->id;
            $accident_pic_area->save();

            $accident_cost = new AccidentCost;
            $accident_cost->armada_accident_id = $armada_accident->id;
            $accident_cost->save();

            $legal_aspect = new LegalAspect;
            $legal_aspect->armada_accident_id = $armada_accident->id;
            $legal_aspect->save();

            $insurance_aspect = new InsuranceAspect;
            $insurance_aspect->armada_accident_id = $armada_accident->id;
            $insurance_aspect->save();

            $recovery_accident_cost = new RecoveryAccidentCost;
            $recovery_accident_cost->armada_accident_id = $armada_accident->id;
            $recovery_accident_cost->save();

            DB::commit();

            return redirect('/armadaaccident/'.$armada_accident->id)->with('success','Berhasil menambahkan accident baru. Harap melengkapi data lainnya');
        }catch(\Exception $ex){
            DB::rollback();
            return back()->with('error','Gagal membuat accident armada '.$ex->getMessage());
        }
    }

    public function armadaAccidentUpdate(Request $request){
        // dd($request->armada_accident);
        try {
            DB::beginTransaction();
            // dd($request->armada_accident);
            $armada_accident                        = ArmadaAccident::find($request->armada_accident_id);
            $armada_accident->accident_level        = $request->armada_accident["accident_level"] ?? null;
            $armada_accident->accident_consecuence  = $request->armada_accident["accident_consecuence"] ?? null;
            $armada_accident->handling_start_date   = $request->armada_accident["handling_start_date"] ?? null;
            $armada_accident->handling_end_date     = $request->armada_accident["handling_end_date"] ?? null;
            $armada_accident->cost_remarks          = $request->armada_accident["cost_remarks"] ?? null;
            $armada_accident->accident_causes       = json_encode($request->armada_accident["accident_causes"] ?? null);
            $armada_accident->urgency               = json_encode($request->armada_accident["urgency"] ?? null);
            $armada_accident->description           = $request->armada_accident["description"] ?? null;
            $armada_accident->periode               = \Carbon\Carbon::create()
                                                        ->month($request->armada_accident["periode"]["year"])
                                                        ->year($request->armada_accident["periode"]["year"])
                                                        ->format('Y-m-d');
            $armada_accident->updated_by            = Auth::user()->id;
            $armada_accident->save();
            
            $vehicle_identity                  = $armada_accident->vehicle_identity;
            $vehicle_identity->nopol           = $request->vehicle_identity["nopol"] ?? null;
            $vehicle_identity->cabang          = $request->vehicle_identity["cabang"] ?? null;
            $vehicle_identity->stnk_status     = $request->vehicle_identity["stnk_status"] ?? null;
            $vehicle_identity->jenis_kendaraan = $request->vehicle_identity["jenis_kendaraan"] ?? null;

            $armada = Armada::where('plate',$vehicle_identity->nopol)->first();
            if($armada != null) {
                $vehicle_identity->armada_id       = $armada->id;
                $vehicle_identity->isNiaga         = $armada->armada_type->isNiaga;
            }else{
                $vehicle_identity->armada_id       = null;
                $vehicle_identity->isNiaga         = null;
            }
            $vehicle_identity->save();

            $driver_identity                = $armada_accident->driver_identity;
            $driver_identity->name          = $request->driver_identity['nama'] ?? null;
            $driver_identity->nik           = $request->driver_identity['nik'] ?? null;
            $driver_identity->jabatan       = $request->driver_identity['jabatan'] ?? null;
            $driver_identity->status        = $request->driver_identity['status'] ?? null;
            $driver_identity->jenis_sim     = $request->driver_identity['jenis_sim'] ?? null;
            $driver_identity->sim_status    = $request->driver_identity['sim_status'] ?? null;
            $driver_identity->save();
            
            $pic_area          = $armada_accident->pic_area;
            $pic_area->nama    = $request->pic_area['nama'];
            $pic_area->nik     = $request->pic_area['nik'];
            $pic_area->jabatan = $request->pic_area['jabatan'];
            $pic_area->phone   = $request->pic_area['phone'];
            $pic_area->save();
            
            $accident_cost                            = $armada_accident->accident_cost;
            $accident_cost->perobatan_korban          = $request->accident_cost["perobatan_korban"] ?? null;
            $accident_cost->nominal_perobatan_korban  = $request->accident_cost["nominal_perobatan_korban"] ?? null;
            $accident_cost->santunan                  = $request->accident_cost["santunan"] ?? null;
            $accident_cost->nominal_santunan          = $request->accident_cost["nominal_santunan"] ?? null;
            $accident_cost->biaya_unit_korban         = $request->accident_cost["biaya_unit_korban"] ?? null;
            $accident_cost->nominal_biaya_unit_korban = $request->accident_cost["nominal_biaya_unit_korban"] ?? null;
            $accident_cost->biaya_perkara             = $request->accident_cost["biaya_perkara"] ?? null;
            $accident_cost->nominal_biaya_perkara     = $request->accident_cost["nominal_biaya_perkara"] ?? null;
            $accident_cost->save();
            
            $legal_aspect           = $armada_accident->legal_aspect;
            $legal_aspect->status   = $request->legal_aspect["status"] ?? null;
            $legal_aspect->remarks  = $request->legal_aspect["remarks"] ?? null;
            $legal_aspect->save();
            
            $insurance_aspect                 = $armada_accident->insurance_aspect;
            $insurance_aspect->conclusion     = $request->insurance_aspect["conclusion"] ?? null;
            $insurance_aspect->start_date_sla = $request->insurance_aspect["start_date_sla"] ?? null;
            $insurance_aspect->end_date_sla   = $request->insurance_aspect["end_date_sla"] ?? null;
            $insurance_aspect->status         = $request->insurance_aspect["status"] ?? null;
            $insurance_aspect->save();
            
            $recovery_cost                  = $armada_accident->recovery_cost;
            $recovery_cost->insurance_value = $request->recovery_cost["insurance_value"] ?? null;  
            $recovery_cost->employee_value  = $request->recovery_cost["employee_value"] ?? null;  
            $recovery_cost->save();

            DB::commit();
            return back()->with('success','Berhasil update data');
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
            return back()->with('error','Gagal update data ('.$ex->getMessage().')');
        }
        
    }

    public function openCase(Request $request){
        $armada_accident = ArmadaAccident::find($request->armada_accident_id);
        if(new Carbon($armada_accident->updated_at) != new Carbon($request->updated_at)){
            return back()->with('error','Data telah terupdate silahkan ulangi');
        }
        $armada_accident->status = 0;
        $armada_accident->save();

        return back()->with('success','Berhasil membuka kasus');
    }

    public function closeCase(Request $request){
        $armada_accident = ArmadaAccident::find($request->armada_accident_id);
        if(new Carbon($armada_accident->updated_at) != new Carbon($request->updated_at)){
            return back()->with('error','Data telah terupdate silahkan ulangi');
        }
        $armada_accident->status = 1;
        $armada_accident->save();

        return redirect('/armadaaccident')->with('success', 'Berhasil menutup kasus');
    }
}
