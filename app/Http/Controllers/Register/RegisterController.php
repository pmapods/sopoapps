<?php

namespace App\Http\Controllers\Register;

use Hash;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorCompany;
use App\Models\VendorLogin;
use App\Models\Province;
use App\Models\Regency;

use Illuminate\Support\Str;
use Illuminate\Http\Request;


class RegisterController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function register()
    {
        $regency = Regency::inRandomOrder()->first()->name;
        $provinces = Province::all();
        $vendors = Vendor::all();
        return view('Register.register', compact('provinces', 'vendors'), ['title' => 'Register']);
    }

    public function addVendorCompany(Request $request)
    {
        try {
            $checkVendorLogin = VendorLogin::where('username', $request->username)->first();
            if ($checkVendorLogin) {
                return back()->with('error', 'Username sudah terdaftar sebelumnya untuk perusahaan dengan nama ' . $checkVendorLogin->name);
            }

            // Periksa apakah email sudah ada
            $existingVendorByEmail = VendorLogin::where('email', $request->pic_email)->first();
            if ($existingVendorByEmail) {
                return back()->with('error', 'Email sudah terdaftar sebelumnya untuk perusahaan dengan nama ' . $existingVendorByEmail->name);
            }

            $checkVendor = null;

            $count_vendor = VendorLogin::withTrashed()->count() + 1;
            $code = "VDR-" . str_repeat("0", 4 - strlen($count_vendor)) . $count_vendor;

            if ($request->vendor_ref) {
                $checkVendor = Vendor::where('code', $request->vendor_ref)->first();
            } else {
                $count_vendor_existing = Vendor::withTrashed()->count() + 1;
                $code_vendor = "V2000" . str_repeat("0", 4 - strlen($count_vendor_existing)) . $count_vendor_existing;

                $newVendor = new Vendor;
                $newVendor->code = $code_vendor;
                $newVendor->type = $request->business_type;
                $newVendor->name = strtoupper($request->company_name);
                $newVendor->alias = strtolower($request->username);
                $newVendor->address = $request->company_address;
                $newVendor->city_id = $request->city_id;
                $newVendor->salesperson = strtolower($request->pic_name);
                $newVendor->phone = $request->pic_phone;
                $emails = explode(',', $request->pic_email);
                foreach ($emails as $key => $email) {
                    // trim setiap email
                    $emails[$key] = strtolower(trim($email));
                }
                $emails = array_filter($emails, function ($email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    } else {
                        return true;
                    }
                });
                $newVendor->email = json_encode($emails);
                $newVendor->status = 1;
                $newVendor->e_log_sync = 1;
                $newVendor->deleted_at = null;
                $newVendor->created_at = now();
                $newVendor->updated_at = now();
                $newVendor->save();
            }

            $newVendorCompany = new VendorCompany;
            $newVendorCompany->id = (string) Str::uuid();
            $newVendorCompany->code = $checkVendor != null ? $request->vendor_ref : $code_vendor;
            $newVendorCompany->company_name = strtoupper($request->company_name);
            $newVendorCompany->ceo_name = $request->company_leader;
            $newVendorCompany->address = $request->company_address;
            $newVendorCompany->company_phone = $request->contact_number;
            $newVendorCompany->company_website = $request->website;
            $newVendorCompany->company_status = $request->company_status;
            $newVendorCompany->legal_form = $request->legal_form;
            $newVendorCompany->ownership_status = $request->ownership;
            $newVendorCompany->company_type = $request->business_type;

            $company_name = str_replace(' ', '_', strtoupper($request->company_name));
            $ext = pathinfo($request->file('company_profile')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "COMPANY_PROFILE_" . $company_name . '.' . $ext;
            $path = "/attachments/vendor/company/" . $newVendorCompany->code . '/' . $name;
            $file = pathinfo($path);
            $path_company_profile = $request->file('company_profile')->storeAs($file['dirname'], $file['basename'], 'public');
            $newVendorCompany->company_profile = $path_company_profile;

            //doi (deed of incorporation)
            $ext = pathinfo($request->file('legal_docs')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "LEGAL_DOC_" . $company_name . '.' . $ext;
            $path = "/attachments/vendor/company/" . $newVendorCompany->code . '/' . $name;
            $file = pathinfo($path);
            $path_legal_doc = $request->file('legal_docs')->storeAs($file['dirname'], $file['basename'], 'public');
            $newVendorCompany->company_doi = $path_legal_doc;

            $ext = pathinfo($request->file('location_permission')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "IZIN_LOKASI_" . $company_name . '.' . $ext;
            $path = "/attachments/vendor/company/" . $newVendorCompany->code . '/' . $name;
            $file = pathinfo($path);
            $path_local_permission = $request->file('location_permission')->storeAs($file['dirname'], $file['basename'], 'public');
            $newVendorCompany->location_permission = $path_local_permission;
            
            $ext = pathinfo($request->file('siup')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "SIUP_" . $company_name . '.' . $ext;
            $path = "/attachments/vendor/company/" . $newVendorCompany->code . '/' . $name;
            $file = pathinfo($path);
            $path_siup = $request->file('siup')->storeAs($file['dirname'], $file['basename'], 'public');
            $newVendorCompany->siup = $path_siup;

            $ext = pathinfo($request->file('tdp_nib')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "TDP_NIB_" . $company_name . '.' . $ext;
            $path = "/attachments/vendor/company/" . $newVendorCompany->code . '/' . $name;
            $file = pathinfo($path);
            $path_tdp_nib = $request->file('tdp_nib')->storeAs($file['dirname'], $file['basename'], 'public');
            $newVendorCompany->tdp_nib = $path_tdp_nib;

            $ext = pathinfo($request->file('company_npwp')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = "NPWP_" . $company_name . '.' . $ext;
            $path = "/attachments/vendor/company/" . $newVendorCompany->code . '/' . $name;
            $file = pathinfo($path);
            $path_npwp = $request->file('company_npwp')->storeAs($file['dirname'], $file['basename'], 'public');
            $newVendorCompany->company_npwp = $path_npwp;

            $newVendorCompany->pic_name = strtolower($request->pic_name);
            $newVendorCompany->pic_phone = $request->pic_phone;
            $newVendorCompany->pic_email = $request->pic_email;
            $newVendorCompany->pic_position = $request->pic_position;
            $newVendorCompany->status = 1;
            $newVendorCompany->deleted_at = null;
            $newVendorCompany->updated_at = now();
            $newVendorCompany->created_at = now();
            $newVendorCompany->save();

            $newVendorLogin = new VendorLogin;
            $newVendorLogin->id = (string) Str::uuid();
            $newVendorLogin->code = $code;
            $newVendorLogin->email = $request->pic_email;
            $newVendorLogin->username = strtolower($request->username);
            $newVendorLogin->password = Hash::make($request->password);
            $newVendorLogin->name = strtoupper($request->company_name);
            $newVendorLogin->type = $request->business_type;
            $newVendorLogin->vendor_code_ref = $checkVendor != null ? $request->vendor_ref : $code_vendor;
            $newVendorLogin->status = 1;
            $newVendorLogin->deleted_at = null;
            $newVendorLogin->updated_at = now();
            $newVendorLogin->created_at = now();
            $newVendorLogin->save();

            return back()->with('success', 'Terima kasih ' . strtoupper($request->company_name) . ' sudah mendaftar sebagai Vendor kami, kami akan mengevaluasi perusahaan anda dan menghubungi anda secepat mungkin');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan perusahaan vendor, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

}
