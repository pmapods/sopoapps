<?php

namespace App\Http\Controllers\Masterdata;
use DB;
use Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Armada;
use App\Models\ArmadaType;
use App\Models\SalesPoint;
use App\Models\Authorization;

class ArmadaController extends Controller
{
    public function armadaView(){
        $employee_access = Auth::user()->location_access->pluck('salespoint_id');
        $armadas = Armada::all();
        $armada_types = ArmadaType::all();
        $salespoints = SalesPoint::whereIn('id', $employee_access)->get();
        return view('Masterdata.armada',compact('armadas','salespoints','armada_types'));
    }

    public function addArmada(Request $request){
        try {
            $armada_by_plate = Armada::where('plate',str_replace(' ', '', strtoupper($request->plate)))->first();
            if($armada_by_plate){
                throw new \Exception('Nomor Polisi sudah ada ! ('.$armada_by_plate->armada_type->name.' -- '.$armada_by_plate->plate.') di '.$armada_by_plate->salespoint->name);
            }
            DB::beginTransaction();
            $newArmada                  = new Armada;
            $newArmada->salespoint_id   = ($request->salespoint_id ?? null);
            $newArmada->armada_type_id  = $request->armada_type_id;
            $newArmada->plate           = str_replace(' ', '', strtoupper($request->plate));
            $newArmada->vehicle_year    = $request->vehicle_year.'-01-01'; 
            $newArmada->status          = $request->status; 
            $newArmada->booked_by       = $request->booked_by ?? null; 
            $newArmada->save();
            DB::commit();
            return back()->with('success','Berhasil menambahkan armada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menambahkan armada ('.$ex->getMessage().')');
        }

    }

    public function updateArmada(Request $request){
        try {
            $armada_by_plate = Armada::where('plate',str_replace(' ', '', strtoupper($request->plate)))
                                        ->where('id','!=',$request->armada_id)
                                        ->first();
            if($armada_by_plate){
                throw new \Exception('Nopol sudah ada ! '.$armada_by_plate->armada_type->name.' -- '.$armada_by_plate->plate.' di '.$armada_by_plate->salespoint->name);
            }
            DB::beginTransaction();
            $armada                  = Armada::find($request->armada_id);
            $armada->salespoint_id   = ($request->salespoint_id ?? null); 
            $armada->armada_type_id  = $request->armada_type_id; 
            $armada->plate           = str_replace(' ', '', strtoupper($request->plate)); 
            $armada->vehicle_year    = $request->vehicle_year.'-01-01'; 
            $armada->status          = $request->status; 
            $armada->booked_by       = $request->booked_by ?? null; 
            $armada->save();
            DB::commit();
            return back()->with('success','Berhasil update armada');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal update armada ('.$ex->getMessage().')');
        }
    }

    public function deleteArmada(Request $request){
        try {
            $armada = Armada::findOrFail($request->armada_id);
            $armada->delete();
            return back()->with('success','Berhasil Menghapus Armada');
        } catch (\Exception $ex) {
            return back()->with('error','Gagal Menghapus Armada ('.$ex->getMessage().')');
        }
    }

    public function addArmadaType(Request $request){
        try {
            $armadatype = ArmadaType::where('name',$request->name)->first();
            if($armadatype){
                throw new \Exception('Nama Jenis Kendaraan sudah ada');
            }
            $newArmadatype = new ArmadaType;
            $newArmadatype->name = $request->name;
            $newArmadatype->brand_name = $request->brand_name;
            $newArmadatype->alias = $request->alias ?? null;
            $newArmadatype->isNiaga = $request->isNiaga;
            if ($request->isSBH) {
                $newArmadatype->isSBH = $request->isSBH;
            }
            $newArmadatype->save();
            return back()->with('success','Berhasil Menambah Jenis Armada')->with('menu','armadatypelist');
        } catch (\Exception $ex) {
            return back()->with('error','Gagal Menambahkan Jenis Kendaraan ('.$ex->getMessage().')');
        }
    }
    
    public function deleteArmadaType(Request $request){
        try {
            $armadatype = ArmadaType::findOrFail($request->armada_type_id);
            if(count($armadatype->armada)>0){
                $plates = implode(", ",$armadatype->armada->pluck('plate')->toArray());
                throw new \Exception('Harap menghapus armada dengan nomor plat '.$plates.' sebelum melanjutkan pengahapusan jenis armada');
            }
            $armadatype->delete();
            return back()->with('success','Berhasil Menghapus Jenis Armada')->with('menu','armadatypelist');
        } catch (\Exception $ex) {
            return back()->with('error','Gagal Menghapus Jenis Kendaraan ('.$ex->getMessage().')');
        }
    }

    public function getArmadaTypebyNiaga($isNiaga){
        $armadas = ArmadaType::where('isNiaga',$isNiaga)->get();
        return response()->json([
           'data' => $armadas
        ]);
    }

    public function getArmadabySalespoint($salespoint_id){
        try{
            $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
            if(in_array($salespoint_id,$salespoint_ids->toArray())){
                $armadas = Armada::where('salespoint_id',$salespoint_id)->get();
                return response()->json([
                    'data' => $armadas
                ]);
            }else{
                throw new \Exception('access denied');
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function getArmada(Request $request){
        $armadas = Armada::all();
        if($request->isNiaga != null){
            $armadas = $armadas->filter(function($item) use ($request){
                if($item->armada_type->isNiaga == $request->isNiaga){
                    return true;
                }else{
                    return false;
                }
            });
        }
        if($request->salespoint_id != null){
            $armadas = $armadas->filter(function($item) use ($request){
                if($item->salespoint_id ==  $request->salespoint_id){
                    return true;
                }else{
                    return false;
                }
            });
        }
        // butuh di convert jadi array karena hasil filter bentuknya full json
        $data =[];
        foreach($armadas as $armada){
            array_push($data,$armada);
        }
        return response()->json([
            'data' => $data,
        ]);
    }

    public function getArmadaAuthorizationbySalespoint($salespoint_id){
        $salespoint = SalesPoint::find($salespoint_id);
        $armada_authorizations = Authorization::whereIn('salespoint_id',$salespoint->salespoint_id_list())->where('form_type',7)->get();

        foreach($armada_authorizations as $authorizations){
            $authorizations->list = $authorizations->authorization_detail;
            foreach($authorizations->list as $item){
                $item->employee_name = $item->employee->name;
            }
        }
        return response()->json([
            'data' => $armada_authorizations,
        ]);
    }

    public function getSecurityAuthorizationbySalespoint($salespoint_id){
        $salespoint = SalesPoint::find($salespoint_id);
        $security_authorizations = Authorization::whereIn('salespoint_id',$salespoint->salespoint_id_list())->where('form_type',8)->get();

        foreach($security_authorizations as $authorizations){
            $authorizations->list = $authorizations->authorization_detail;
            foreach($authorizations->list as $item){
                $item->employee_name = $item->employee->name;
            }
        }
        return response()->json([
            'data' => $security_authorizations,
        ]);
    }
}
