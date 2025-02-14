<?php

namespace App\Http\Controllers\Masterdata;

use DB;
use Auth;
use Hash;
use Mail;
use Crypt;
use Redirect;
use Validator;
use Carbon\Carbon;
use App\Mail\GlobalMail;
use App\Models\Employee;

use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Models\PrAuthorization;
use App\Models\EmployeePosition;
use App\Models\AuthorizationDetail;
use App\Models\TicketAuthorization;
use App\Http\Controllers\Controller;
use App\Models\BiddingAuthorization;
use App\Models\EmployeeLocationAccess;
use App\Models\MutasiFormAuthorization;
use Illuminate\Support\Facades\Session;
use App\Models\ArmadaTicketAuthorization;
use App\Models\EvaluasiFormAuthorization;
use App\Models\FacilityFormAuthorization;

use App\Models\SecurityTicketAuthorization;
use App\Models\PerpanjanganFormAuthorization;
use App\Models\VendorEvaluationAuthorization;

class EmployeeController extends Controller
{
    // EMPLOYEE POSITION
    public function employeepostitionView()
    {
        $positions = EmployeePosition::whereNotIn('id', [1])->get();
        return view('Masterdata.employeeposition', compact('positions'));
    }
    public function addEmployeePosition(Request $request)
    {
        $newPosition            = new EmployeePosition;
        $newPosition->name      = $request->name;
        $newPosition->save();
        return back()->with('success', 'Berhasil menambahkan jabatan ' . $request->name);
    }
    public function updateEmployeePosition(Request $request)
    {
        try {
            $position           = EmployeePosition::findOrFail($request->position_id);
            $old_name           = $position->name;
            $position->name     = $request->name;
            $position->save();
            return back()->with('success', 'Berhasil menguban jabatan ' . $old_name . ' menjadi ' . $position->name);
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal mengubah jabatan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }
    public function deleteEmployeePosition(Request $request)
    {
        try {
            $position           = EmployeePosition::findOrFail($request->position_id);
            $position->delete();
            return back()->with('success', 'Berhasil menghapus jabatan ' . $position->name);
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus jabatan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

    // EMPLOYEE
    public function employeeView()
    {
        $employees = Employee::whereNotIn('id', [1])->get();
        $employee_pst = Employee::leftJoin('authorization_detail', 'employee.id', '=', 'authorization_detail.employee_id')
        ->leftJoin('employee_position', 'employee_position.id', '=', 'employee.position_id')
        ->where('employee.id', '!=', 1)
        ->whereNull('employee.deleted_at')
        ->select('employee.id', 'employee.name', 'employee_position.name as emp_position', 'employee.code', 'employee.username', 'employee.email', 'employee.status', 'employee.phone', 'employee.position_id')
        ->groupBy('employee.id')
        ->get();
        $employee_positions = EmployeePosition::where('employee_position.id', '!=', 1)->get();
        return view('Masterdata.employee', compact('employees', 'employee_pst', 'employee_positions'));
    }

    public function getEmployeePosition(Request $request)
    {
        $employees_pos = Employee::leftJoin('authorization_detail', 'employee.id', '=', 'authorization_detail.employee_id')
        ->leftJoin('employee_position', 'employee_position.id', '=', 'authorization_detail.employee_position_id')
        ->where('employee.id', '=', $request->employee_id)
        ->whereNull('employee.deleted_at')
        ->whereNotNull('authorization_detail.employee_id')
        ->select('employee.id', 'employee.name', 'authorization_detail.employee_position_id', 'employee_position.name as emp_position')
        ->groupBy('employee.id')
        ->get();

        $employees_pos = $employees_pos->toArray();
        
        return response()->json([
            "data" => array_values($employees_pos),
        ]);
    }

    public function jobtitleEmployeeConfirmation(Request $request) {
        try {
            $auth_position = AuthorizationDetail::where('employee_id', '=', $request->employee_id)
                            ->get();
            foreach ($auth_position as $auth_position) {
                try {
                    $old_position                           = $auth_position->employee_position_id;
                    $auth_position->employee_position_id    = $request->job_title_id;
                    $auth_position->save();

                } catch (\Throwable $th) {
                    continue;
                }
            }
            DB::commit();
            return redirect('/employee')->with('success', 'Berhasil ubah job position.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Gagal mengubah jabatan, silahkan coba kembali atau hubungi developer "' . $th->getMessage() . '"');
        }
    }   

    public function addEmployee(Request $request)
    {
        try {
            $count_employee = Employee::withTrashed()->count() + 1;
            $code = "EMP-" . str_repeat("0", 4 - strlen($count_employee)) . $count_employee;

            $checkEmployee = Employee::where('username', $request->username)->first();
            if ($checkEmployee) {
                return back()->with('error', 'Username sudah terdaftar sebelum untuk karyawan dengan nama ' . $checkEmployee->name);
            }
            $checkEmployee = Employee::where('email',$request->email)->first();
            if($checkEmployee){
                return back()->with('error','Email sudah terdaftar sebelum untuk karyawan dengan nama '.$checkEmployee->name);
            }

            $newEmployee                         = new Employee;
            $newEmployee->code                   = $code;
            $newEmployee->name                   = $request->name;
            $newEmployee->username               = $request->username;
            $newEmployee->position_id            = $request->job_title_id;
            $newEmployee->email                  = $request->email;
            $newEmployee->password               = Hash::make($request->password);
            $newEmployee->phone                  = $request->phone;
            $newEmployee->save();
            return back()->with('success', 'Berhasil menambahkan karyawan ' . $request->name);
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menambahkan karyawan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

    public function updateEmployee(Request $request)
    {
        try {
            $employee             = Employee::findOrFail($request->employee_id);
            // jika email berbeda lakukan validasi
            if ($employee->email != $request->email) {
                $check_is_email_exist = Employee::where('email', $request->email)->first();
                if ($check_is_email_exist) {
                    return back()->with('error', 'Email ' . $request->email . ' telah terdaftar sebelumnya dengan nama pengguna ' . $check_is_email_exist->name);
                }
                // check email validity
                if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    return back()->with('error', 'Email ' . $request->email . ' tidak valid / format email salah ');
                }
            }

            // jika username berbeda lakukan validasi
            if ($employee->username != $request->username) {
                $check_is_username_exist = Employee::where('username', $request->username)->first();
                if ($check_is_username_exist) {
                    return back()->with('error', 'Username ' . $request->username . ' telah terdaftar sebelumnya dengan nama pengguna ' . $check_is_username_exist->name);
                }
            }

            $employee->phone                = $request->phone;
            $employee->name                 = $request->name;
            // $employee->username             = $request->username;
            $employee->email                = $request->email;
            $employee->save();
            return back()->with('success', 'Berhasil memperbarui data karyawan ' . $request->name);
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal memperbarui data karyawan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

    public function activeEmployee(Request $request)
    {
        try {
            $employee =  Employee::findOrFail($request->employee_id);
            if (new Carbon($employee->updated_at) != new Carbon($request->updated_at)) {
                return back()->with('error', 'Employee sudah di update sebelumnya. Silahkan coba kembali.');
            }
            $employee->status = 0;
            $employee->save();
            return back()->with('success', 'berhasil diaktifkan');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal mengaktifkan karyawan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

    public function nonactiveEmployee(Request $request)
    {
        try {
            $employee           = Employee::findOrFail($request->employee_id);
            if (new Carbon($employee->updated_at) != new Carbon($request->updated_at)) {
                return back()->with('error', 'Employee sudah di update sebelumnya. Silahkan coba kembali.');
            }
            $employee->status   = 1;
            $employee->save();
            return back()->with('success', 'berhasil di non aktifkan');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal mengaktifkan karyawan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

    public function deleteEmployee(Request $request)
    {
        try {
            $employee           = Employee::findOrFail($request->employee_id);
            if (new Carbon($employee->updated_at) != new Carbon($request->updated_at)) {
                return back()->with('error', 'Employee sudah di update sebelumnya. Silahkan coba kembali.');
            }
            $employee->delete();
            return back()->with('success', 'Karyawan berhasil dihapus');
        } catch (\Exception $ex) {
            return back()->with('error', 'Gagal menghapus karyawan, silahkan coba kembali atau hubungi developer "' . $ex->getMessage() . '"');
        }
    }

    public function migrateEmployeeConfirmationView(Request $request)
    {
        $current_authorizations = $this->getCurrentAuthorization($request->source_employee_id);
        $master_location_access = EmployeeLocationAccess::where('employee_id', $request->source_employee_id)->get();
        $authorization_ids = AuthorizationDetail::where('employee_id', $request->source_employee_id)->get()->pluck('authorization_id')->unique();
        $master_authorizations = Authorization::whereIn('id', $authorization_ids)->get();
        $source_employee = Employee::find($request->source_employee_id);
        $target_employee = Employee::find($request->target_employee_id);
        return view('Masterdata.migrateemployeeconfirmation', compact('current_authorizations', 'master_location_access', 'master_authorizations', 'source_employee', 'target_employee'));
    }

    public function doMigrateEmployee(Request $request)
    {
        try {
            DB::beginTransaction();
            $source_employee = Employee::findOrFail($request->source_employee_id);
            $target_employee = Employee::findOrFail($request->target_employee_id);
            //ticketing
            $data = [];
            $ticketauthorization = TicketAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();
            $armadaticketauthorization = ArmadaTicketAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();
            $securityticketauthorization = SecurityTicketAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();
            $vendorevaluationauthorization = VendorEvaluationAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();
            foreach ($ticketauthorization as $author) {
                try {
                    $ticket = $author->ticket;
                    if (!in_array($ticket->status ?? -1, [1])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }
            foreach ($armadaticketauthorization as $author) {
                try {
                    $armada_ticket = $author->armada_ticket;
                    if (!in_array($armada_ticket->status ?? -1, [1])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }
            foreach ($securityticketauthorization as $author) {
                try {
                    $security_ticket = $author->security_ticket;
                    if (!in_array($security_ticket->status ?? -1, [1])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }
            foreach ($vendorevaluationauthorization as $author) {
                try {
                    $vendor_evaluation = $author->vendorEvaluation;
                    if (!in_array($vendor_evaluation->status ?? -1, [2])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // barangjasa bidding
            $biddingauthorization = BiddingAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();

            foreach ($biddingauthorization as $author) {
                try {
                    $ticket = $author->bidding->ticket;
                    if (!in_array($ticket->status ?? -1, [2])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // pr
            $prauthorization = PrAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();

            foreach ($prauthorization as $author) {
                try {
                    $ticket = $author->pr->ticket;
                    $armadaticket = $author->pr->armada_ticket;
                    $securityticket = $author->pr->security_ticket;
                    if ($ticket) {
                        if (!in_array($ticket->status ?? -1, [4])) {
                            continue;
                        }
                        $author->employee_id = $target_employee->id;
                        $author->employee_name = $target_employee->name;
                        $author->save();
                    }
                    if ($armadaticket) {
                        if (!in_array($armadaticket->status ?? -1, [3])) {
                            continue;
                        }
                        $author->employee_id = $target_employee->id;
                        $author->employee_name = $target_employee->name;
                        $author->save();
                    }
                    if ($securityticket) {
                        if (!in_array($securityticket->status ?? -1, [3])) {
                            continue;
                        }
                        $author->employee_id = $target_employee->id;
                        $author->employee_name = $target_employee->name;
                        $author->save();
                    }
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // form evaluasi
            $evaluasiauthorization = EvaluasiFormAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();

            foreach ($evaluasiauthorization as $author) {
                try {
                    $security_ticket = $author->evaluasi_form->security_ticket;
                    if (!in_array($security_ticket->status ?? -1, [0])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // form fasilitas
            $facilityauthorization = FacilityFormAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();

            foreach ($facilityauthorization as $author) {
                try {
                    $armada_ticket = $author->facility_form->armada_ticket;
                    if (!in_array($armada_ticket->status ?? -1, [0])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // form perpanjangan
            $perpanjanganauthorization = PerpanjanganFormAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();

            foreach ($perpanjanganauthorization as $author) {
                try {
                    $armada_ticket = $author->perpanjangan_form->armada_ticket;
                    if (!in_array($armada_ticket->status ?? -1, [0])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }

            $mutasiauthorization = MutasiFormAuthorization::where('employee_id', $source_employee->id)
                ->where('status', 0)
                ->get();

            foreach ($mutasiauthorization as $author) {
                try {
                    $armada_ticket = $author->mutasi_form->armada_ticket;
                    if (!in_array($armada_ticket->status ?? -1, [0])) {
                        continue;
                    }
                    $author->employee_id = $target_employee->id;
                    $author->employee_name = $target_employee->name;
                    $author->save();
                } catch (\Exception $ex) {
                    continue;
                }
            }

            $master_location_access = EmployeeLocationAccess::where('employee_id', $request->source_employee_id)->get();
            foreach ($master_location_access as $access) {
                $access->employee_id = $request->target_employee_id;
                $access->save();
            }
            $authorization_detail = AuthorizationDetail::where('employee_id', $request->source_employee_id)->get();
            foreach ($authorization_detail as $detail) {
                $detail->employee_id = $request->target_employee_id;
                $detail->save();
            }

            DB::commit();
            return redirect('/employee')->with('success', 'Berhasil migrasi akses otorisasi.');
        } catch (\Exception $th) {
            return back()->with('error', 'Gagal migrasi akses otorisasi. (' . $ex->getMessage() . $ex->getLine() . ')');
            DB::rollback();
        }
    }

    public function getCurrentAuthorization($user_id)
    {
        //ticketing
        $data = [];
        $ticketauthorization = TicketAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();
        $armadaticketauthorization = ArmadaTicketAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();
        $securityticketauthorization = SecurityTicketAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();
        $vendorevaluationauthorization = VendorEvaluationAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();
        
        foreach ($ticketauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $ticket = $author->ticket;

                if (!in_array($ticket->status ?? -1, [1])) {
                    continue;
                }
                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->ticket_authorization->where('as', 'Pengaju')->first()->employee_name ?? '';
                // dd($ticket);
                $newAuth->transaction_type = 'Barang Jasa (ticketing)';
                $newAuth->status = $ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }
        foreach ($armadaticketauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $armada_ticket = $author->armada_ticket;
                if (!in_array($armada_ticket->status ?? -1, [1])) {
                    continue;
                }
                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $armada_ticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Armada (ticketing)';
                $newAuth->status = $armada_ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }
        foreach ($securityticketauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $security_ticket = $author->security_ticket;
                if (!in_array($security_ticket->status ?? -1, [1])) {
                    continue;
                }
                $newAuth->salespoint = $security_ticket->salespoint->name;
                $newAuth->code = $security_ticket->code;
                $newAuth->created_at = $security_ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $security_ticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Security (ticketing)';
                $newAuth->status = $security_ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }
        foreach ($vendorevaluationauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $vendor_evaluation = $author->vendorEvaluation;
                if (!in_array($vendor_evaluation->status ?? -1, [2])) {
                    continue;
                }
                $newAuth->salespoint = $vendor_evaluation->salespoint->name;
                $newAuth->code = $vendor_evaluation->code;
                $newAuth->created_at = $vendor_evaluation->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $vendor_evaluation->authorizations->where('as', 'Menilai')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Vendor Evaluation';
                $newAuth->status = $vendor_evaluation->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }

        // barangjasa bidding
        $biddingauthorization = BiddingAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();

        foreach ($biddingauthorization as $author) {
            try {
                $ticket = $author->bidding->ticket;
                $ticket_item = $author->bidding->ticket_item;
                if (!in_array($ticket->status ?? -1, [2])) {
                    continue;
                }
                $newAuth = new \stdClass();
                $newAuth->salespoint = $ticket->salespoint->name;
                $newAuth->code = $ticket->code;
                $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $ticket->ticket_authorization->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Barang Jasa (bidding)';
                $newAuth->status = $ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }

        // pr
        $prauthorization = PrAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();

        foreach ($prauthorization as $author) {
            try {
                $ticket = $author->pr->ticket;
                $armadaticket = $author->pr->armada_ticket;
                $securityticket = $author->pr->security_ticket;
                if ($ticket) {
                    if (!in_array($ticket->status ?? -1, [4])) {
                        continue;
                    }
                    $newAuth = new \stdClass();
                    $newAuth->salespoint = $ticket->salespoint->name;
                    $newAuth->code = $ticket->code;
                    $newAuth->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                    $newAuth->created_by = $ticket->ticket_authorization->where('as', 'Pengaju')->first()->employee_name ?? '';

                    $newAuth->transaction_type = 'Barang Jasa (PR)';
                    $newAuth->status = $ticket->status();
                    array_push($data, $newAuth);
                }
                if ($armadaticket) {
                    if (!in_array($armadaticket->status ?? -1, [3])) {
                        continue;
                    }
                    $newAuth = new \stdClass();
                    $newAuth->salespoint = $armadaticket->salespoint->name;
                    $newAuth->code = $armadaticket->code;
                    $newAuth->created_at = $armadaticket->created_at->translatedFormat('d F Y (H:i)');
                    $newAuth->created_by = $armadaticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';

                    $newAuth->transaction_type = 'Armada (PR)';
                    $newAuth->status = $armadaticket->status();
                    array_push($data, $newAuth);
                }
                if ($securityticket) {
                    if (!in_array($securityticket->status ?? -1, [3])) {
                        continue;
                    }
                    $newAuth = new \stdClass();
                    $newAuth->salespoint = $securityticket->salespoint->name;
                    $newAuth->code = $securityticket->code;
                    $newAuth->created_at = $securityticket->created_at->translatedFormat('d F Y (H:i)');
                    $newAuth->created_by = $securityticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';

                    $newAuth->transaction_type = 'Security (PR)';
                    $newAuth->status = $securityticket->status();
                    array_push($data, $newAuth);
                }
            } catch (\Exception $ex) {
                continue;
            }
        }

        // form evaluasi
        $evaluasiauthorization = EvaluasiFormAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();

        foreach ($evaluasiauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $security_ticket = $author->evaluasi_form->security_ticket;
                if (!in_array($security_ticket->status ?? -1, [0])) {
                    continue;
                }
                $newAuth->salespoint = $security_ticket->salespoint->name;
                $newAuth->code = $security_ticket->code;
                $newAuth->created_at = $security_ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $security_ticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Security (form evaluasi)';
                $newAuth->status = $security_ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }

        // form fasilitas
        $facilityauthorization = FacilityFormAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();

        foreach ($facilityauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $armada_ticket = $author->facility_form->armada_ticket;
                if (!in_array($armada_ticket->status ?? -1, [0])) {
                    continue;
                }
                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $armada_ticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Armada (form fasilitas)';
                $newAuth->status = $armada_ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }

        // form perpanjang
        $perpanjanganauthorization = PerpanjanganFormAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();

        foreach ($perpanjanganauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $armada_ticket = $author->perpanjangan_form->armada_ticket;
                if (!in_array($armada_ticket->status ?? -1, [0])) {
                    continue;
                }
                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $armada_ticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Armada (form perpanjangan)';
                $newAuth->status = $armada_ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }

        $mutasiauthorization = MutasiFormAuthorization::where('employee_id', $user_id)
            ->where('status', 0)
            ->get();

        foreach ($mutasiauthorization as $author) {
            try {
                $newAuth = new \stdClass();
                $armada_ticket = $author->mutasi_form->armada_ticket;
                if (!in_array($armada_ticket->status ?? -1, [0])) {
                    continue;
                }
                $newAuth->salespoint = $armada_ticket->salespoint->name;
                $newAuth->code = $armada_ticket->code;
                $newAuth->created_at = $armada_ticket->created_at->translatedFormat('d F Y (H:i)');
                $newAuth->created_by = $armada_ticket->authorizations->where('as', 'Pengaju')->first()->employee_name ?? '';
                $newAuth->transaction_type = 'Armada (form mutasi)';
                $newAuth->status = $armada_ticket->status();
                array_push($data, $newAuth);
            } catch (\Exception $ex) {
                continue;
            }
        }

        return $data;
    }

    public function resetEmployeePassword(Request $request)
    {
        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $employee = Employee::findOrFail($request->employee_id);
            $employee->password = Hash::make("pma123");
            $employee->is_password_changed = 0;
            $employee->save();

            // Mail budget upload
            $mail_to = $employee->email;
            $name_to = $employee->name;
            $data = array(
                'employee' => $employee,
                'original_emails' => [$mail_to],
                'from' => Auth::user()->name,
                'to' => $name_to,
            );
            if (config('app.env') == 'local') {
                $mail_to = [config('mail.testing_email')];
            }
            try {
                Mail::to($mail_to)->send(new GlobalMail($data, 'reset_password'));
            } catch (\Exception $ex) {
                dd($ex);
                $emailflag = false;
            }
            if (!$emailflag) {
                $emailmessage = "\n (Email pemberitahuan gagal dirikim. Harap menginfokan langsung ke user bersangkutan)";
            }
            DB::commit();
            return back()->with('success', 'Berhasil melakukan reset password' . $emailmessage);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal melakukan reset password (' . $ex->getMessage() . ')');
        }
    }

    // Organization Chart
    public function orgChartView()
    {
        $rbm_data = Employee::join('authorization_detail as b', 'employee.id', '=', 'b.employee_id')
                            ->join('employee_position as c', 'b.employee_position_id', '=', 'c.id')
                            ->join('authorization as d', 'b.authorization_id', '=', 'd.id')
                            ->join('salespoint as e', 'd.salespoint_id', '=', 'e.id')
                            ->join('region as f', 'e.region', '=', 'f.region')
                            ->where('c.id', '=', 49)
                            ->where('employee.status', '=', 0)
                            ->where('f.region', '!=', 17)
                            ->select('employee.id', 'employee.code', 'employee.nik', DB::raw('UCASE(employee.name) AS emp_name'), 'c.name AS job_title', DB::raw("GROUP_CONCAT(DISTINCT f.region_name SEPARATOR ', ') AS reg_name"))
                            ->groupBy('employee.id')
                            ->orderBy('employee.name', 'ASC')
                            ->orderBy('f.region', 'ASC')
                            ->get();

        return view('Masterdata.orgcharts', compact('rbm_data'));
    }

    public function orgChartDetailView($nik) {

        $orgDataDetail = DB::select
                ("SELECT rbm.rbm_code, rbm.region, rbm.region_name, bm.slp_id, bm.slp_name, rbm.emp_name AS rbm_name, 
                        rbm.rbm_job_title, rbm.rbm_email,
		                bm.bm_code, bm.emp_name AS bm_name, bm.bm_job_title, bm.bm_email,
		                rom.rom_code, rom.emp_name AS rom_name, rom.rom_job_title, rom.rom_email,
		                aos.aos_code, aos.emp_name AS aos_name, aos.aos_job_title, aos.aos_email
	                FROM (
			                SELECT a.nik AS rbm_code, UCASE(a.name) AS emp_name, c.name AS rbm_job_title, e.id AS slp_id, 
                                e.name AS slp_name, f.region, f.region_name, a.email AS rbm_email
			                FROM employee a
			                INNER JOIN authorization_detail b ON a.id = b.employee_id
			                INNER JOIN employee_position c ON b.employee_position_id = c.id
			                INNER JOIN authorization d ON b.authorization_id = d.id
			                INNER JOIN salespoint e ON d.salespoint_id = e.id
			                INNER JOIN region f ON e.region = f.region
			                WHERE c.id = 49
			                AND a.`status` = 0
			                GROUP BY f.region, a.id, e.id
			                ORDER BY f.region
	                    ) rbm
	                LEFT JOIN (
		                    SELECT a.nik AS bm_code, UCASE(a.name) AS emp_name, c.name AS bm_job_title, e.id AS slp_id, 
                                e.name AS slp_name, f.region, f.region_name, a.email AS bm_email
		                    FROM employee a
		                    INNER JOIN authorization_detail b ON a.id = b.employee_id
		                    INNER JOIN employee_position c ON b.employee_position_id = c.id
		                    INNER JOIN authorization d ON b.authorization_id = d.id
		                    INNER JOIN salespoint e ON d.salespoint_id = e.id
		                    INNER JOIN region f ON e.region = f.region
		                    WHERE c.id = 47
		                        AND a.`status` = 0
		                    GROUP BY f.region, e.id, a.id
		                    ORDER BY f.region
	                    ) bm ON rbm.region = bm.region AND rbm.slp_id = bm.slp_id
	                LEFT JOIN (
		                    SELECT a.nik AS rom_code, UCASE(a.name) AS emp_name, c.name AS rom_job_title, e.id AS slp_id, 
                                e.name AS slp_name, f.region, f.region_name, a.email AS rom_email
		                    FROM employee a
		                    INNER JOIN authorization_detail b ON a.id = b.employee_id
		                    INNER JOIN employee_position c ON b.employee_position_id = c.id
		                    INNER JOIN authorization d ON b.authorization_id = d.id
		                    INNER JOIN salespoint e ON d.salespoint_id = e.id
		                    INNER JOIN region f ON e.region = f.region
		                    WHERE c.id IN (42, 77)
		                        AND a.`status` = 0
		                    GROUP BY f.region, a.id, e.id
		                    ORDER BY f.region
	                    ) rom ON rbm.region = rom.region AND rbm.slp_id = rom.slp_id
	                LEFT JOIN (
		                    SELECT a.nik AS aos_code, UCASE(a.name) AS emp_name, c.name AS aos_job_title, e.id AS slp_id, 
                                e.name AS slp_name, f.region, f.region_name, a.email AS aos_email
		                    FROM employee a
		                    INNER JOIN authorization_detail b ON a.id = b.employee_id
		                    INNER JOIN employee_position c ON b.employee_position_id = c.id
		                    INNER JOIN authorization d ON b.authorization_id = d.id
		                    INNER JOIN salespoint e ON d.salespoint_id = e.id
		                    INNER JOIN region f ON e.region = f.region
		                    WHERE c.id = 35
		                        AND a.`status` = 0
		                    GROUP BY f.region, e.id, a.id
		                    ORDER BY f.region
	                    ) aos ON rom.region = aos.region AND rom.slp_id = aos.slp_id
                    WHERE rbm.region != 17
                        AND rbm.rbm_code = $nik
                    GROUP BY bm.slp_id, rbm.emp_name
                    ORDER BY rbm.region, bm.slp_id
                ");

            return view('Masterdata.orgchartsdetail', compact('orgDataDetail', 'nik'));
    }   
}
