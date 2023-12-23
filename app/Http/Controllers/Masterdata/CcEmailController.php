<?php

namespace App\Http\Controllers\Masterdata;

use DB;
use Auth;
use App\Models\EmailCC;
use Illuminate\Http\Request;
use App\Models\EmployeePosition;
use App\Models\AuthorizationDetail;
use App\Http\Controllers\Controller;

class CcEmailController extends Controller
{
    public function detailccEmailView(Request $request)
    {
        $detail_emails =  AuthorizationDetail::join('authorization', 'authorization.id', '=', 'authorization_detail.authorization_id')
            ->join('employee_position', 'employee_position.id', '=', 'authorization_detail.employee_position_id')
            ->join('employee', 'employee.id', '=', 'authorization_detail.employee_id')
            ->where('employee_position.id', $request->employee_position_id)
            ->selectRaw('employee.name, employee.email')
            ->groupBy(DB::raw('employee.name, employee.email'))
            ->get()
            ->makeHidden(['employee_name'])
            ->toArray();

        return ["data" => $detail_emails];
    }

    public function ccEmailView(Request $request)
    {
        $email_ccs = EmailCC::with(['created_by_employee', 'employee_positions'])->get();
        $employee_positions = EmployeePosition::orderBy('name', 'asc')->get();


        return view('Masterdata.emailcc', compact('email_ccs', 'employee_positions'));
    }

    public function ccEmailCreate(Request $request)
    {
        try {
            DB::beginTransaction();
            $email_cc                    = DB::table('email_cc');
            $email_cc                    = new EmailCC;

            $email_cc_exists             = EmailCC::where('employee_position', '=', $request->employee_position)->first();

            if ($email_cc_exists) {
                return back()->with('error', "Nama Jabatan Sudah Ada, Silahkan Pilih Jabatan Yang Lain");
            } else {
                $email_cc->employee_position = $request->employee_position;
                $email_cc->created_by        = Auth::user()->id;
                $email_cc->save();
                DB::commit();

                return back()->with('success', 'Berhasil menambahkan Email CC');
            }
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal menambahkan Email CC (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }

    public function ccEmailDelete(Request $request)
    {
        try {
            DB::beginTransaction();

            $email_cc = EmailCC::where('id', $request->id)->first();
            $email_cc->delete();

            DB::commit();
            return back()->with('success', 'Berhasil Menghapus Jabatan');
        } catch (\Exception $ex) {
            return back()->with('error', "Gagal Menghapus Jabatan (" . $ex->getMessage() . ")[" . $ex->getLine() . "]");
        }
    }
}
