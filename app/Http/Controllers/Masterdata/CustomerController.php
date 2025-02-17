<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Province;
use App\Models\Regency;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeePosition;

class CustomerController extends Controller
{
    public function customerView()
    {
        $regency = Regency::all();
        $customers = Customer::all();
        $customersType = CustomerType::all();
        $customersType2 = CustomerType::all();
        $provinces = Province::all();
        $positions = EmployeePosition::where('id', '!=', 1)->get();
        return view('Masterdata.customer', compact('regency', 'customers', 'customersType', 'positions', 'provinces', 'customersType2'));
    }

    public function addCustomer(Request $request)
    {
        try {
            $check = Customer::where('code', $request->code)->first();
            if ($check != null) {
                return back()->with('error', 'Kode customer tidak bisa sama / harus berbeda -- ' . $request->code . ' ' . $check->name);
            }
            $stafflist = $request->stafflist;
            // dd($stafflist);
            // foreach((array)$stafflist as $key => $item) {
            //     dd($item, $stafflist);                  
            // }
            // dd($request);
            $newCustomer                  = new Customer;
            $newCustomer->code            = $request->kode;
            $newCustomer->name            = $request->nama;
            $newCustomer->alias           = $request->alias;
            $newCustomer->type            = $request->cust_type;
            $newCustomer->regency_id      = $request->regency_id;
            $newCustomer->address         = $request->address;
            $newCustomer->opening_date    = $request->requirement_date;
            $newCustomer->space           = $request->space;
            $newCustomer->store_staff     = $request->stafflist;
            $newCustomer->save();
            return back()->with('success', 'Berhasil menambahkan Customer');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan Customer  "' . $ex->getMessage() . '"');
        }
    }

    public function updateVendor(Request $request)
    {
        try {
            $vendor = Vendor::findOrFail($request->id);
            $vendor->alias       = $request->alias;
            // $vendor->type        = $request->type;
            $vendor->address     = $request->address;
            $vendor->city_id     = $request->city_id;
            $vendor->salesperson = $request->salesperson;
            $vendor->phone       = $request->phone;
            $emails              = explode(',', $request->email);
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
            $vendor->email       = json_encode($emails);
            // $vendor->e_log_sync  = $request->e_log_sync;
            $vendor->save();

            return back()->with('success', 'Berhasil memperbarui vendor');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui Vendor  "' . $ex->getMessage() . '"');
        }
    }

    public function deleteVendor(Request $request)
    {
        try {
            $vendor = Vendor::findOrFail($request->id);
            $vendor->delete();

            return back()->with('success', 'Berhasil menghapus vendor');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus Vendor  "' . $ex->getMessage() . '"');
        }
    }
}
