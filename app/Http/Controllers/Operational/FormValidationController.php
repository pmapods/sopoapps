<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;

use App\Models\ArmadaTicket;
use App\Models\ArmadaTicketMonitoring;
use App\Models\FacilityForm;
use App\Models\PerpanjanganForm;
use App\Models\MutasiForm;

class FormValidationController extends Controller
{
    public function formValidationView(Request $request) {
        // dd($request);
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        // ambil form2 yang sesuai akses salespoint dan status armada ticket nya sudah ready buat PO (4)
        $facility_form = FacilityForm::join('armada_ticket','armada_ticket.id','=','facility_form.armada_ticket_id')
                ->where('facility_form.is_form_validated',false)
                ->whereIn('facility_form.salespoint_id',$salespoint_ids)
                ->where('armada_ticket.status',4)
                ->select('facility_form.*')
                ->get();
        $perpanjangan_form = PerpanjanganForm::join('armada_ticket','armada_ticket.id','=','perpanjangan_form.armada_ticket_id')
                ->where('perpanjangan_form.is_form_validated',false)
                ->whereIn('perpanjangan_form.salespoint_id',$salespoint_ids)
                ->where('armada_ticket.status',4)
                ->select('perpanjangan_form.*', 'armada_ticket.ticketing_type', 'perpanjangan_form.stopsewa_reason', 'perpanjangan_form.is_percepatan')
                ->get();
        $mutasi_form = MutasiForm::join('armada_ticket','armada_ticket.id','=','mutasi_form.armada_ticket_id')
                ->where('mutasi_form.is_form_validated',false)
                ->whereIn('mutasi_form.salespoint_id',$salespoint_ids)
                ->where('armada_ticket.status',4)
                ->select('mutasi_form.*')
                ->get();
        $data = [];
        foreach($facility_form as $form){
            $item = new \stdClass();
            $item->data = $form->toJson();
            $item->type = 'facility_form';
            $item->armada_ticket = $form->armada_ticket;
            $item->salespoint = $form->armada_ticket->salespoint;
            $item->created_at = $form->created_at;
            array_push($data, $item);
        }
        foreach($perpanjangan_form as $form){
            $item = new \stdClass();
            $item->data = $form->toJson();
            if ($form->ticketing_type == 4 && $form->is_percepatan == true && $form->stopsewa_reason == 'replace') {
                $item->type = 'percepatan_replace_form';
            }
            elseif ($form->ticketing_type == 4 && $form->is_percepatan == true && $form->stopsewa_reason == 'renewal') {
                $item->type = 'percepatan_renewal_form';
            }
            elseif ($form->ticketing_type == 4 && $form->is_percepatan == true && $form->stopsewa_reason == 'end') {
                $item->type = 'percepatan_end_kontrak_form';
            }
            else {
                $item->type = 'perpanjangan_form';
            }
            $item->armada_ticket = $form->armada_ticket;
            $item->salespoint = $form->armada_ticket->salespoint;
            $item->created_at = $form->created_at;
            array_push($data, $item);
        }
        foreach($mutasi_form as $form){
            $item = new \stdClass();
            $item->data = $form->toJson();
            $item->type = 'mutasi_form';
            $item->armada_ticket = $form->armada_ticket;
            $item->salespoint = $form->armada_ticket->salespoint;
            $item->created_at = $form->created_at;
            array_push($data, $item);
        }
        
        return view('Operational.formvalidation',compact('data'));
    }

    public function formValidationDetailView(Request $request){
        try{
            $type = $request->type;
            $formdata = json_decode($request->formdata);
            
            if($type == "perpanjangan_form" || $type == "percepatan_replace_form" || $type == "percepatan_renewal_form" || $type == "percepatan_end_kontrak_form"){
                $armadaticket = ArmadaTicket::findOrFail($formdata->armada_ticket_id);
                if($armadaticket->status != 4){
                    throw new \Exception("Form Perpanjangan belum siap divalidasi");
                }
                return view('Operational.formvalidationdetail',compact('type','armadaticket'));
            }
            if($type == "mutasi_form"){
                $armadaticket = ArmadaTicket::findOrFail($formdata->armada_ticket_id);
                if($armadaticket->status != 4){
                    throw new \Exception("Form Mutasi belum siap divalidasi");
                }
                return view('Operational.formvalidationdetail',compact('type','armadaticket'));
            }
            if($type == "facility_form"){
                $armadaticket = ArmadaTicket::findOrFail($formdata->armada_ticket_id);
                if($armadaticket->status != 4){
                    throw new \Exception("Form Fasilitas belum siap divalidasi");
                }
                return view('Operational.formvalidationdetail',compact('type','armadaticket'));
            }
        }catch(\Exception $ex){
            return redirect('/form-validation')->with('error','Gagal membuka menu validasi form ('.$ex->getMessage().')');
        }
    }

    public function formValidationApprove(Request $request){
        try{
            // dd($request->type);
            DB::beginTransaction();
            switch($request->type){
                case 'perpanjangan_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;

                    $perpanjangan_form->is_form_validated = true;
                    $perpanjangan_form->validated_by = Auth::user()->id;
                    $perpanjangan_form->validated_at = now();
                    $perpanjangan_form->save();

                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Approve Validasi Formulir Perpanjangan.';
                    $monitor->save();

                    if($armadaticket->type() == "End Kontrak"){
                        // jika form end kontrak, setelah tervalidasi oleh GA langsung ubah status "menunggu upload BASTK"
                        $armadaticket->status = 5;
                        $armadaticket->save();
                    }
                    break;
                case 'percepatan_replace_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;

                    $perpanjangan_form->is_form_validated = true;
                    $perpanjangan_form->validated_by = Auth::user()->id;
                    $perpanjangan_form->validated_at = now();
                    $perpanjangan_form->save();

                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Approve Validasi Formulir Percepatan Replace.';
                    $monitor->save();
                    break;
                case 'percepatan_end_kontrak_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;
    
                    $perpanjangan_form->is_form_validated = true;
                    $perpanjangan_form->validated_by = Auth::user()->id;
                    $perpanjangan_form->validated_at = now();
                    $perpanjangan_form->save();
    
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Approve Validasi Formulir Percepatan End Kontrak.';
                    $monitor->save();

                    // jika form percepatan end kontrak, setelah tervalidasi oleh GA langsung ubah status "menunggu upload BASTK"
                    $armadaticket->status = 5;
                    $armadaticket->save();
                    
                    break;
                case 'percepatan_renewal_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;
        
                    $perpanjangan_form->is_form_validated = true;
                    $perpanjangan_form->validated_by = Auth::user()->id;
                    $perpanjangan_form->validated_at = now();
                    $perpanjangan_form->save();
        
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Approve Validasi Formulir Percepatan Renewal.';
                    $monitor->save();
                    break;
                case 'mutasi_form':
                    $mutasi_form = MutasiForm::findOrFail($request->mutasi_form_id);
                    $armadaticket = $mutasi_form->armada_ticket;

                    $mutasi_form->is_form_validated = true;
                    $mutasi_form->validated_by = Auth::user()->id;
                    $mutasi_form->validated_at = now();
                    $mutasi_form->save();

                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Approve Validasi Formulir Mutasi.';
                    $monitor->save();
                    break;
                case 'facility_form':
                    $facility_form = FacilityForm::findOrFail($request->facility_form_id);
                    $armadaticket = $facility_form->armada_ticket;

                    $facility_form->is_form_validated = true;
                    $facility_form->validated_by = Auth::user()->id;
                    $facility_form->validated_at = now();
                    $facility_form->save();

                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Approve Validasi Formulir Fasilitas.';
                    $monitor->save();
                    break;
                default:
                    throw new \Exception('Tipe '.$request->type.' tidak valid');
                    break;
            }
            DB::commit();
            return redirect('/form-validation')->with('success', 'Berhasil melakukan approve validasi form ' . ucwords(str_replace("_"," ",$request->type)));
        }catch(\Exception $ex){
            DB::rollback();
            return back()->with('error','Gagal melakukan approve validasi form ('.$ex->getMessage().')');
        }
    }

    public function formValidationReject(Request $request){
        try{
            DB::beginTransaction();
            switch($request->type){
                case 'perpanjangan_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;

                    $perpanjangan_form->status              = -1;
                    $perpanjangan_form->terminated_by       = Auth::user()->id;
                    $perpanjangan_form->termination_reason  = $request->reason;
                    $perpanjangan_form->save();
                    $perpanjangan_form->delete();

                    $armadaticket->status              = 0;
                    $armadaticket->save();
                    
                    $armadaticket->refresh();
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Reject Validasi Formulir Perpanjangan.';
                    $monitor->save();
                    break;
                case 'percepatan_replace_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;

                    $perpanjangan_form->status              = -1;
                    $perpanjangan_form->terminated_by       = Auth::user()->id;
                    $perpanjangan_form->termination_reason  = $request->reason;
                    $perpanjangan_form->save();
                    $perpanjangan_form->delete();

                    $armadaticket->status              = 0;
                    $armadaticket->save();
                    
                    $armadaticket->refresh();
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Reject Validasi Formulir Percepatan Replace.';
                    $monitor->save();
                    break;
                case 'percepatan_renewal_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;

                    $perpanjangan_form->status              = -1;
                    $perpanjangan_form->terminated_by       = Auth::user()->id;
                    $perpanjangan_form->termination_reason  = $request->reason;
                    $perpanjangan_form->save();
                    $perpanjangan_form->delete();

                    $armadaticket->status              = 0;
                    $armadaticket->save();
                    
                    $armadaticket->refresh();
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Reject Validasi Formulir Percepatan Renewal.';
                    break;
                case 'percepatan_end_kontrak_form':
                    $perpanjangan_form = PerpanjanganForm::findOrFail($request->perpanjangan_form_id);
                    $armadaticket = $perpanjangan_form->armada_ticket;

                    $perpanjangan_form->status              = -1;
                    $perpanjangan_form->terminated_by       = Auth::user()->id;
                    $perpanjangan_form->termination_reason  = $request->reason;
                    $perpanjangan_form->save();
                    $perpanjangan_form->delete();

                    $armadaticket->status              = 0;
                    $armadaticket->save();
                    
                    $armadaticket->refresh();
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Reject Validasi Formulir Percepatan End Kontrak.';
                    break;
                case 'mutasi_form':
                    $mutasi_form = MutasiForm::findOrFail($request->mutasi_form_id);
                    $armadaticket = $mutasi_form->armada_ticket;

                    $mutasi_form->status              = -1;
                    $mutasi_form->terminated_by       = Auth::user()->id;
                    $mutasi_form->termination_reason  = $request->reason;
                    $mutasi_form->save();
                    $mutasi_form->delete();

                    $armadaticket->status              = 0;
                    $armadaticket->save();
                    
                    $armadaticket->refresh();
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Reject Validasi Formulir Mutasi.';
                    $monitor->save();
                    break;
                case 'facility_form':
                    $facility_form = FacilityForm::findOrFail($request->facility_form_id);
                    $armadaticket = $facility_form->armada_ticket;

                    $facility_form->status              = -1;
                    $facility_form->terminated_by       = Auth::user()->id;
                    $facility_form->termination_reason  = $request->reason;
                    $facility_form->save();
                    $facility_form->delete();

                    $armadaticket->status              = 0;
                    $armadaticket->save();
                    
                    $armadaticket->refresh();
                    $monitor                        = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $armadaticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Reject Validasi Formulir Fasilitas.';
                    $monitor->save();
                    break;
                default:
                    throw new \Exception('Tipe '.$request->type.' tidak valid');
                    break;
                }
            DB::commit();
            return redirect('/form-validation')->with('success', 'Berhasil melakukan reject validasi form '  . ucwords(str_replace("_"," ",$request->type)));
        }catch(\Exception $ex){
            DB::rollback();
            return back()->with('error','Gagal melakukan reject validasi form ('.$ex->getMessage().')');
        }
    }
}
