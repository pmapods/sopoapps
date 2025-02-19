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
            $check = Customer::where('code', $request->kode)->first();
            if ($check != null) {
                return back()->with('error', 'Kode customer tidak bisa sama / harus berbeda -- ' . $request->code . ' ' . $check->name);
            }
            $stafflist = $request->stafflist;
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
            return back()->with('success', 'Berhasil menambahkan customer');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan customer  "' . $ex->getMessage() . '"');
        }
    }

    public function updateCustomer(Request $request)
    {
        try {
            $customer = Customer::where('code', $request->kode)->first();
            $customer->name             = $request->nama;
            $customer->alias            = $request->alias;
            $customer->type             = $request->cust_type;
            $customer->regency_id       = $request->regency_id;
            $customer->address          = $request->address;
            $customer->opening_date     = $request->requirement_date;
            $customer->space            = $request->space;
            $customer->store_staff      = $request->stafflist;
            $customer->save();

            return back()->with('success', 'Berhasil memperbarui customer');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui customer  "' . $ex->getMessage() . '"');
        }
    }

    public function deleteCustomer(Request $request)
    {
        try {
            $customer = Customer::where('code', $request->kode)->first();
            $customer->delete();

            return back()->with('success', 'Berhasil menghapus customer');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus customer  "' . $ex->getMessage() . '"');
        }
    }
}
