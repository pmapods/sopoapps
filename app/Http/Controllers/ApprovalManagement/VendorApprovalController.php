<?php

namespace App\Http\Controllers\ApprovalManagement;

use Hash;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorCompany;
use App\Models\VendorLogin;
use App\Models\Province;
use App\Models\Regency;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\FacilityForm;
use App\Models\PerpanjanganForm;
use App\Models\MutasiForm;


class VendorApprovalController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function vendorApprovalView(Request $request)
    {
        // dd($request);
        $salespoint_ids = Auth::user()->location_access->pluck('salespoint_id');
        // ambil form2 yang sesuai akses salespoint dan status armada ticket nya sudah ready buat PO (4)

        $data = Vendor::select('vendor.id', 'company_types.name as type', 'vendor.name', 'regencies.name as nama_city', 'vendor_company.ownership_status', 'vendor_company.legal_form', 'vendor_company.company_status')
            ->leftjoin('vendor_company', 'vendor_company.code', '=', 'vendor.code')
            ->join('regencies', 'regencies.id', '=', 'vendor.city_id')
            ->join('company_types', 'company_types.type', '=', 'vendor.type')
            ->where('vendor_company.status', '=', 1)
            ->whereNull('vendor_company.deleted_at')
            ->get();
        // dd($data);
        return view('ApprovalManagement.vendorregister', compact('data'));
    }

    public function vendorApprovalDetail(Request $request)
    {
        // dd(base64_decode($code));

        $vendor = Vendor::select('vendor.*', 'regencies.name as nama_city')
            ->where('vendor.id', $request->id)
            ->join('regencies', 'regencies.id', '=', 'vendor.city_id')
            ->first();
        $vendorCompany = VendorCompany::select('vendor_company.*', 'company_types.name as type_name')
            ->join('company_types', 'company_types.type', '=', 'vendor_company.company_type')
            ->where('vendor_company.code', $vendor->code)
            ->first();
        return view('ApprovalManagement.vendorregisterdetail', compact('vendor', 'vendorCompany'));
    }

    public function vendorApprovalApprove(Request $request)
    {
        try {
            
            DB::beginTransaction();
            $request->validate([
                'id' => 'required|exists:vendor,id',
            ]);

            $vendor = Vendor::find($request->id);
            if ($vendor) {
                $vendor->status = 0;
                $vendor->save();
                
                $vendorCompany = VendorCompany::where('code', $vendor->code)->firstOrFail();
                $vendorCompany->approved_by = Auth::user()->id;
                $vendorCompany->status = 0;
                $vendorCompany->rejected_by = NULL;
                $vendorCompany->reject_note = NULL;
                $vendorCompany->save();
                $vendorUser = VendorLogin::where('vendor_code_ref', $vendor->code)->firstOrFail();
                
                $vendorUser->status = 0;
                $vendorUser->save();
                DB::commit();
                return redirect('/vendor-approve-register')->with('success', 'Berhasil melakukan Approve ' . $vendor->name);
        
            } else {
                return back()->with('error', 'data vendor tidak ditemukan)');
            }
            // dd($request->type);
            
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan Approve (' . $ex->getMessage() . ')');
        }
    }

    public function vendorApprovalReject(Request $request)
    {
        
        try {
            
            DB::beginTransaction();
            $request->validate([
                'id' => 'required|exists:vendor,id',
                'reason' => 'required|string',
            ]);
            $vendor = Vendor::find($request->id);
            if ($vendor) {
                $vendor->status = -1;
                $vendor->save();

                $vendorCompany = VendorCompany::where('code', $vendor->code)->firstOrFail();
                $vendorCompany->approved_by = NULL;
                $vendorCompany->rejected_by = Auth::user()->id;
                $vendorCompany->reject_note = $request->reason;
                $vendorCompany->status = -1;
                $vendorCompany->save();
                DB::commit();
                return redirect('/vendor-approve-register')->with('success', 'Berhasil melakukan Reject ' . $vendor->name);
        
            } else {
                return back()->with('error', 'data vendor tidak ditemukan)');
            }
            // dd($request->type);
            
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan Approve (' . $ex->getMessage() . ')');
        }
    }

    public function formValidationReject(Request $request)
    {
        try {
            DB::beginTransaction();
            switch ($request->type) {
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
                    throw new \Exception('Tipe ' . $request->type . ' tidak valid');
                    break;
            }
            DB::commit();
            return redirect('/form-validation')->with('success', 'Berhasil melakukan reject validasi form '  . ucwords(str_replace("_", " ", $request->type)));
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan reject validasi form (' . $ex->getMessage() . ')');
        }
    }
}
