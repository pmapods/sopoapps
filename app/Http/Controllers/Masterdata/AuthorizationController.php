<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesPoint;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeeLocationAccess;
use App\Models\Authorization;
use App\Models\AuthorizationDetail;

use DB;
use Auth;

class AuthorizationController extends Controller
{
    public function authorizationView()
    {
        $employee_access = Auth::user()->location_access_list();
        $salespoints = SalesPoint::whereIn('id', $employee_access)->get();
        $regions = $salespoints->groupBy('region');
        // selain superadmin
        $positions = EmployeePosition::where('id', '!=', 1)->get();
        $authorizations = Authorization::whereIn('salespoint_id', $employee_access)->get();
        $employees = Employee::where('id', '!=', 1)->get();
        return view('Masterdata.authorization', compact('regions', 'authorizations', 'positions', 'employees'));
    }

    public function authorizationData(Request $request)
    {
        $search_value = $request->search["value"];

        $region_found_array = [];
        if (str_contains(strtolower("MT CENTRAL 1"), strtolower($search_value))) {
            array_push($region_found_array, 0);
        }
        if (str_contains(strtolower("SUMATERA 1"), strtolower($search_value))) {
            array_push($region_found_array, 1);
        }
        if (str_contains(strtolower("SUMATERA 2"), strtolower($search_value))) {
            array_push($region_found_array, 2);
        }
        if (str_contains(strtolower("SUMATERA 3"), strtolower($search_value))) {
            array_push($region_found_array, 3);
        }
        if (str_contains(strtolower("SUMATERA 4"), strtolower($search_value))) {
            array_push($region_found_array, 4);
        }
        if (str_contains(strtolower("BANTEN"), strtolower($search_value))) {
            array_push($region_found_array, 5);
        }
        if (str_contains(strtolower("DKI"), strtolower($search_value))) {
            array_push($region_found_array, 6);
        }
        if (str_contains(strtolower("JABAR 1"), strtolower($search_value))) {
            array_push($region_found_array, 7);
        }
        if (str_contains(strtolower("JABAR 2"), strtolower($search_value))) {
            array_push($region_found_array, 8);
        }
        if (str_contains(strtolower("JABAR 3"), strtolower($search_value))) {
            array_push($region_found_array, 9);
        }
        if (str_contains(strtolower("JATENG 1"), strtolower($search_value))) {
            array_push($region_found_array, 10);
        }
        if (str_contains(strtolower("JATENG 2"), strtolower($search_value))) {
            array_push($region_found_array, 11);
        }
        if (str_contains(strtolower("JATIM 1"), strtolower($search_value))) {
            array_push($region_found_array, 12);
        }
        if (str_contains(strtolower("JATIM 2"), strtolower($search_value))) {
            array_push($region_found_array, 13);
        }
        if (str_contains(strtolower("BALINUSRA"), strtolower($search_value))) {
            array_push($region_found_array, 14);
        }
        if (str_contains(strtolower("KALIMANTAN"), strtolower($search_value))) {
            array_push($region_found_array, 15);
        }
        if (str_contains(strtolower("SULAWESI"), strtolower($search_value))) {
            array_push($region_found_array, 16);
        }
        if (str_contains(strtolower("HO"), strtolower($search_value))) {
            array_push($region_found_array, 17);
        }
        if (str_contains(strtolower("JATENG 3"), strtolower($search_value))) {
            array_push($region_found_array, 18);
        }
        if (str_contains(strtolower("INDIRECT"), strtolower($search_value))) {
            array_push($region_found_array, 19);
        }


        $formtype_found_array = [];
        if (str_contains(strtolower("form pengadaan barang jasa"), strtolower($search_value))) {
            array_push($formtype_found_array, 0);
        }
        if (str_contains(strtolower("form bidding"), strtolower($search_value))) {
            array_push($formtype_found_array, 1);
        }
        if (str_contains(strtolower("form pr"), strtolower($search_value))) {
            array_push($formtype_found_array, 2);
        }
        if (str_contains(strtolower("form po"), strtolower($search_value))) {
            array_push($formtype_found_array, 3);
        }
        if (str_contains(strtolower("form fasilitas"), strtolower($search_value))) {
            array_push($formtype_found_array, 4);
        }
        if (str_contains(strtolower("form mutasi"), strtolower($search_value))) {
            array_push($formtype_found_array, 5);
        }
        if (str_contains(strtolower("form perpanjangan perhentian"), strtolower($search_value))) {
            array_push($formtype_found_array, 6);
        }
        if (str_contains(strtolower("form pengadaan armada"), strtolower($search_value))) {
            array_push($formtype_found_array, 7);
        }
        if (str_contains(strtolower("form pengadaan security"), strtolower($search_value))) {
            array_push($formtype_found_array, 8);
        }
        if (str_contains(strtolower("form evaluasi"), strtolower($search_value))) {
            array_push($formtype_found_array, 9);
        }
        if (str_contains(strtolower("Upload Budget (baru)"), strtolower($search_value))) {
            array_push($formtype_found_array, 10);
        }
        if (str_contains(strtolower("Upload Budget (revisi)"), strtolower($search_value))) {
            array_push($formtype_found_array, 11);
        }
        if (str_contains(strtolower("form FRI"), strtolower($search_value))) {
            array_push($formtype_found_array, 12);
        }
        if (str_contains(strtolower("form Peremajaan Armada"), strtolower($search_value))) {
            array_push($formtype_found_array, 16);
        }
        if (str_contains(strtolower("Cancel End Kontrak (Pest Control, Armada, Security)"), strtolower($search_value))) {
            array_push($formtype_found_array, 17);
        }

        // dd($search_value,$region_found_array,$formtype_found_array);
        $employee_access = Auth::user()->location_access_list();
        $authorizations = Authorization::whereIn('salespoint_id', $employee_access)->get();
        $authorizations =  Authorization::leftJoin('salespoint', 'salespoint.id', '=', 'authorization.salespoint_id')
            ->join('authorization_detail', 'authorization_detail.authorization_id', '=', 'authorization.id')
            ->join('employee', 'employee.id', '=', 'authorization_detail.employee_id')
            ->where(function ($query) use ($employee_access) {
                // filter apakan punya akses
                $query->whereIn('authorization.salespoint_id', $employee_access);
            })
            ->where(function ($query) use ($search_value, $region_found_array, $formtype_found_array) {
                // filter apakan punya akses
                $query->where(DB::raw('lower(salespoint.name)'), 'like', '%' . strtolower($search_value) . '%')
                    ->orWhereIn('salespoint.region', $region_found_array)
                    ->orWhereIn('authorization.form_type', $formtype_found_array)
                    ->orWhere(DB::raw('lower(employee.name)'), 'like', '%' . strtolower($search_value) . '%')
                    ->orWhere(DB::raw('lower(authorization.notes)'), 'like', '%' . strtolower($search_value) . '%')
                    ->orWhere(DB::raw('lower(authorization.salespoint_id)'), 'like', '%' . strtolower($search_value) . '%');
            })
            ->select('authorization.*')
            ->distinct('authorization.id')
            ->orderBy('authorization.id', 'ASC')
            ->get();

        $authorizations_paginate = $authorizations->skip($request->start)->take($request->length);
        $datas = [];
        foreach ($authorizations_paginate as $authorization) {
            $array = [];
            $salespoint_name = $authorization->salespoint_name;
            $region_name = ($authorization->salespoint) ? $authorization->salespoint->region_name() : "-";
            $employees_name = implode(", ", $authorization->authorization_detail->pluck("employee_name")->toArray());
            $form_type_name = $authorization->form_type_name();
            $notes = $authorization->notes;
            array_push($array, $salespoint_name);
            array_push($array, $region_name);
            array_push($array, $employees_name);
            array_push($array, $form_type_name);
            array_push($array, $notes);
            array_push($array, $authorization);
            array_push($array, $authorization->list());

            array_push($datas, $array);
        }
        return response()->json([
            "data" => $datas,
            "draw" => $request->draw,
            "recordsFiltered" => $authorizations->count(),
            "recordsTotal" => $authorizations->count(),
        ]);
    }

    public function addAuthorization(Request $request)
    {
        try {
            DB::beginTransaction();
            // dd($request);
            switch ($request->form_type) {
                case '0':
                    // 0 form pengadaan barang jasa
                    $detail_counts = [3];
                    $errMessage = "Form Pengadaan Barang jasa membutuhkan 3 pilihan torisasi";
                    break;
                case '1':
                    // 1 form bidding
                    $detail_counts = [3];
                    $errMessage = "Form Bidding membutuhkan 3 pilihan otorisasi";
                    break;
                case '2':
                    // 2 form pr
                    $detail_counts = [4, 5];
                    $errMessage = "Form PR membutuhkan 4 atau 5 pilihan otorisasi";
                    break;
                case '3':
                    // 3 form po
                    $detail_counts = [2];
                    $errMessage = "Form PO membutuhkan 2 pilihan otorisasi";
                    break;
                case '4':
                    // 4 form fasilitas
                    $detail_counts = [2];
                    $errMessage = "Form Fasilitas membutuhkan 2 pilihan otorisasi";
                    break;
                case '5':
                    // 5 form mutasi
                    $detail_counts = [5, 7];
                    $errMessage = "Form Mutasi membutuhkan 5 atau 7 pilihan otorisasi";
                    break;
                case '6':
                    // 6 form perpanjangan perhentian
                    $detail_counts = [4, 5];
                    $errMessage = "Form Perpanjangan Perhentian membutuhkan 4 atau 5 pilihan otorisasi";
                    break;
                case '7':
                    // 7 form pengadaan armada
                    $detail_counts = [3];
                    $errMessage = "Form Pengadaan Armada membutuhkan 3 pilihan otorisasi";
                    break;
                case '8':
                    // 8 form pengadaan security
                    $detail_counts = [3, 5];
                    $errMessage = "Form Pengadaan Security membutuhkan 3 atau 5 pilihan otorisasi";
                    break;
                case '9':
                    // 9 form evaluasi security
                    $detail_counts = [4];
                    $errMessage = "Form Evaluasi Security membutuhkan 4 pilihan otorisasi";
                    break;
                case '10':
                    // 10 upload budget (baru)
                    $detail_counts = -1;
                    $errMessage = "Otorisasi upload budget membutuhkan minimal 1 pilihan otorisasi";
                    break;
                case '11':
                    // 11 upload budget (revisi)
                    $detail_counts = -1;
                    $errMessage = "Otorisasi upload budget membutuhkan minimal 1 pilihan otorisasi";
                    break;
                case '12':
                    // 12 FORM FRI
                    $detail_counts = [2];
                    $errMessage = "Form Pengadaan Armada membutuhkan 2 pilihan otorisasi";
                    break;
                case '13':
                    // 13 form evaluasi vendor
                    $detail_counts = [2];
                    $errMessage = "Form Evaluasi Vendor membutuhkan 2 pilihan otorisasi";
                    break;
                case '14':
                    // 14 form over budget area
                    $detail_counts = [3];
                    $errMessage = "Form Over Budget Area membutuhkan 3 pilihan otorisasi";
                    break;
                case '15':
                    // 15 form over budget ho
                    $detail_counts = [2];
                    $errMessage = "Form Over Budget HO membutuhkan 2 pilihan otorisasi";
                    break;
                case '16':
                    // 16 form peremajaan armada
                    $detail_counts = [1];
                    $errMessage = "Form Over Budget HO membutuhkan 1 pilihan otorisasi";
                    break;
                case '17':
                    // 17 cancel end kontrak (pest control, armada, security)
                    $detail_counts = [4];
                    $errMessage = "Form Cancel End Kontrak membutuhkan 4 pilihan otorisasi";
                    break;
            }
            if ($detail_counts != -1) {
                if (!in_array(count($request->authorization), $detail_counts)) {
                    return back()->with('error', $errMessage);
                }
            } else {
                if (count($request->authorization) < 1) {
                    return back()->with('error', $errMessage);
                }
            }

            $newAuthorization                 = new Authorization;
            $newAuthorization->salespoint_id  = $request->salespoint;
            $newAuthorization->form_type      = $request->form_type;
            if ($request->notes != null) {
                $newAuthorization->notes          = $request->notes;
            }
            else {
                $newAuthorization->notes          = $request->notes_select;
            }
            $newAuthorization->save();

            $level_over_budget = 4;

            foreach ($request->authorization as $data) {
                $detail                         = new AuthorizationDetail;
                $detail->authorization_id       = $newAuthorization->id;
                $detail->employee_id            = $data['id'];
                $detail->employee_position_id   = $data['position'];
                $detail->sign_as                = $data['as'];

                if ($request->form_type == 14 || $request->form_type == 15) {
                    $detail->level                  = $level_over_budget++;
                } else {
                    $detail->level                  = $data['level'];
                }

                $detail->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil menambahkan otorisasi untuk salespoint');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal membuat otorisasi "' . $ex->getMessage() . '"');
        }
    }

    public function updateAuthorization(Request $request)
    {
        try {
            DB::beginTransaction();

            switch ($request->form_type) {
                case '0':
                    // 0 form pengadaan barang jasa
                    $detail_counts = [3];
                    $errMessage = "Form Pengadaan Barang jasa membutuhkan 3 opilihan torisasi";
                    break;
                case '1':
                    // 1 form bidding
                    $detail_counts = [3];
                    $errMessage = "Form Bidding membutuhkan 3 pilihan otorisasi";
                    break;
                case '2':
                    // 2 form pr
                    $detail_counts = [4, 5];
                    $errMessage = "Form PR membutuhkan 4 atau 5 pilihan otorisasi";
                    break;
                case '3':
                    // 3 form po
                    $detail_counts = [2];
                    $errMessage = "Form PO membutuhkan 2 pilihan otorisasi";
                    break;
                case '4':
                    // 4 form fasilitas
                    $detail_counts = [2];
                    $errMessage = "Form Fasilitas membutuhkan 2 pilihan otorisasi";
                    break;
                case '5':
                    // 5 form mutasi
                    $detail_counts = [5, 7];
                    $errMessage = "Form Mutasi membutuhkan 5 atau 7 pilihan otorisasi";
                    break;
                case '6':
                    // 6 form perpanjangan perhentian
                    $detail_counts = [4, 5];
                    $errMessage = "Form Perpanjangan Perhentian membutuhkan 4 atau 5 pilihan otorisasi";
                    break;
                case '7':
                    // 7 form pengadaan armada
                    $detail_counts = [3];
                    $errMessage = "Form Pengadaan Armada membutuhkan 3 pilihan otorisasi";
                    break;
                case '8':
                    // 8 form pengadaan security
                    $detail_counts = [3, 5];
                    $errMessage = "Form Pengadaan Security membutuhkan 3 atau 5 pilihan otorisasi";
                    break;
                case '9':
                    // 9 form evaluasi
                    $detail_counts = [4];
                    $errMessage = "Form Evaluasi membutuhkan 4 pilihan otorisasi";
                    break;
                case '10':
                    // 10 upload budget (baru)
                    $detail_counts = -1;
                    $errMessage = "Otorisasi upload budget membutuhkan minimal 1 pilihan otorisasi";
                    break;
                case '11':
                    // 11 upload budget (revisi)
                    $detail_counts = -1;
                    $errMessage = "Otorisasi upload budget membutuhkan minimal 1 pilihan otorisasi";
                    break;
                case '12':
                    // 12 FORM FRI
                    $detail_counts = [2];
                    $errMessage = "Form Pengadaan Armada membutuhkan 2 pilihan otorisasi";
                    break;
                case '13':
                    // 13 form evaluasi vendor
                    $detail_counts = [2];
                    $errMessage = "Form Evaluasi Vendor membutuhkan 2 pilihan otorisasi";
                    break;
                case '14':
                    // 14 form over budget area
                    $detail_counts = [3];
                    $errMessage = "Form Over Budget Area membutuhkan 3 pilihan otorisasi";
                    break;
                case '15':
                    // 15 form over budget ho
                    $detail_counts = [2];
                    $errMessage = "Form Over Budget HO membutuhkan 2 pilihan otorisasi";
                    break;
                case '16':
                    // 16 form peremajaan armada
                    $detail_counts = [1];
                    $errMessage = "Form Over Budget HO membutuhkan 1 pilihan otorisasi";
                    break;
            }

            if ($detail_counts != -1) {
                if (!in_array(count($request->authorization), $detail_counts)) {
                    return back()->with('error', $errMessage);
                }
            }

            $authorization = Authorization::findOrFail($request->authorization_id);
            $authorization->salespoint_id  = $request->salespoint;
            $authorization->form_type      = $request->form_type;
            $authorization->notes          = $request->notes;
            $authorization->save();

            foreach ($authorization->authorization_detail as $old) {
                $old->delete();
            }

            $level_over_budget = 4;

            foreach ($request->authorization as $data) {
                $detail                         = new AuthorizationDetail;
                $detail->authorization_id       = $authorization->id;
                $detail->employee_id            = $data['id'];
                $detail->employee_position_id   = $data['position'];
                $detail->sign_as                = $data['as'];

                if ($request->form_type == 14 || $request->form_type == 15) {
                    $detail->level                  = $level_over_budget++;
                } else {
                    $detail->level                  = $data['level'];
                }

                $detail->save();
            }
            DB::commit();
            return back()->with('success', 'Berhasil update otorisasi');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal update otorisasi "' . $ex->getMessage() . '"');
        }
    }

    public function deleteAuthorization(Request $request)
    {
        try {
            DB::beginTransaction();
            $authorization = Authorization::findOrFail($request->authorization_id);
            foreach ($authorization->authorization_detail as $old) {
                $old->delete();
            }
            $authorization->delete();
            DB::commit();
            return back()->with('success', 'Berhasil menghapus otorasi');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus otorisasi "' . $ex->getMessage() . '"');
        }
    }

    public function AuthorizedEmployeeBySalesPoint($salespoint_id)
    {

        $employeeaccess = EmployeeLocationAccess::where('salespoint_id', $salespoint_id)
            ->where('employee_id', '!=', 1)
            ->get();
        $employees = $employeeaccess->pluck('employee_id')->unique();
        if (in_array($salespoint_id, ["all", "west", "east", "indirect"])) {
            $employees = Employee::where('id', '!=', 1)->get()->pluck("id");
        }
        $data = array();
        foreach ($employees as $employee) {
            $selected_employee = Employee::find($employee);
            if ($selected_employee != null) {
                $single_data = (object)[];
                $single_data->id = $selected_employee->id;
                $single_data->name = $selected_employee->name;
                if ($selected_employee->status == 0) {
                    array_push($data, $single_data);
                }
            }
        }
        return response()->json([
            'salespoint_id' => $salespoint_id,
            'data' => $data
        ]);
    }

    public function getAuthorizationDetails(Request $request)
    {
        try {
            $details = AuthorizationDetail::join('authorization', 'authorization.id', '=', 'authorization_detail.authorization_id')
                ->where('authorization_detail.employee_position_id', $request->position_id)
                ->where('authorization.salespoint_id', $request->salespoint_id)
                ->select('authorization_detail.*')
                ->get();
            $formatted_details = [];
            foreach ($details as $detail) {
                $data = new \stdClass();
                $data->authorization_detail_id = $detail->id;
                $data->employee_id = $detail->employee_id;
                $data->employee_name = $detail->employee_name;
                $data->author_list = $detail->authorization->authorization_detail->sortBy('level')->pluck('employee_name')->toArray();
                $data->authorization_notes = $detail->authorization->notes;
                $data->form_type = $detail->authorization->form_type;
                $data->form_type_name = $detail->authorization->form_type_name();
                array_push($formatted_details, $data);
            }
            return response()->json([
                "error" => false,
                "data" => $formatted_details
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "error" => true,
                "data" => $ex->getMessage() . $ex->getLine()
            ]);
        }
    }

    public function multiReplace(Request $request)
    {
        try {
            DB::beginTransaction();
            $salespoint = SalesPoint::findOrFail($request->salespoint_id);
            $employee_position = EmployeePosition::findOrFail($request->position_id);
            $to_employee = Employee::findOrFail($request->to_employee_id);

            $newRequest  = new Request;
            $newRequest->replace([
                'salespoint_id' => $request->salespoint_id,
                'position_id' => $request->position_id
            ]);
            $response = $this->getAuthorizationDetails($newRequest);
            $responseData = $response->getData();
            foreach ($responseData->data as $data) {
                $authorization_detail = AuthorizationDetail::find($data->authorization_detail_id);
                if ($authorization_detail) {
                    $authorization_detail->employee_id = $request->to_employee_id;
                    $authorization_detail->save();
                }
            }
            DB::commit();
            return back()->with('success', "Berhasil melakukan multi replace terkait salespoint \"" . $salespoint->name . "\" dan jabatan \"" . $employee_position->name . "\" menjadi " . $to_employee->name);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', "Gagal melakukan multi replace (" . $ex->getMessage() . $ex->getLine() . ")");
        }
    }
}
