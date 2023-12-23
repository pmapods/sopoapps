<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Armada;
use App\Models\Accident;
use App\Models\SalesPoint;
use App\Models\Vendor;
use Auth;

class AccidentController extends Controller
{
    public function accidentView(){
        $armadas = Armada::all();
        $salespoints = SalesPoint::all();
        $security_vendors = Vendor::where('type','security')->get();
        $accidents = Accident::all();
        return view('Reporting.accident',compact('armadas','salespoints','security_vendors','accidents'));
    }

    public function addAccident(Request $request){
        try {
            $newAccident                    = new Accident;
            $newAccident->accident_date     = $request->date;
            $newAccident->description       = $request->description;
            $newAccident->type              = $request->type;
            $newAccident->salespoint_id     = $request->salespoint_id;
            $newAccident->created_by        = Auth::user()->id;
            if ($request->file()) {
                $selected_salespoint = SalesPoint::find($request->salespoint_id);
                $salespointname = str_replace(' ','_',$selected_salespoint->name);
                $newext = pathinfo($request->file('support_file')->getClientOriginalName(), PATHINFO_EXTENSION);
                $name = now()->format('YmdHis').'.'.$newext;
                $path = "/attachments/reporting/accident/".$salespointname.'/'.$name;
                $file = pathinfo($path);
                $path = $request->file('support_file')->storeAs($file['dirname'],$file['basename'],'public');
                $newAccident->filepath = $path;
            }
            switch ($request->type) {
                case 'armada':
                    $newAccident->plate = Armada::find($request->armada_id)->plate;
                    $newAccident->vendor_id = -1;
                    $newAccident->armada_id = $request->armada_id;
                    break;
                case 'security':
                    $newAccident->vendor_id = $request->vendor_id;
                    $newAccident->vendor_name = Vendor::find($request->vendor_id)->name ?? "";
                    break;
                case 'cit':
                    $newAccident->vendor_id = -1;
                    $newAccident->vendor_name = $request->vendor;
                    break;
                case 'pest_control':
                    $newAccident->vendor_id = -1;
                    $newAccident->vendor_name = $request->vendor;
                    break;
                case 'merchandiser':
                    $newAccident->vendor_id = -1;
                    $newAccident->vendor_name = $request->vendor;
                    break;
                default:
                    return back()->with('error','Tipe reporting tidak terdaftar');
                    break;
            }
            $newAccident->save();
            return redirect('/accident/?type='.$request->type)->with('success','Berhasil menambahkan accident '.$newAccident->type());
        } catch (\Exception $ex) {
            dd($ex);
        }
    }
}
