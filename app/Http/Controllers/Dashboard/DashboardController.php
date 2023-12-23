<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use Auth;
use Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboardView()
    {
        // check if already change password
        return view('Dashboard.dashboard');
    }

    public function profileView()
    {
        return view('Dashboard.profile');
    }

    public function changePassword(Request $request)
    {
        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $confirm_password = $request->confirm_new_password;
        if ($new_password != $confirm_password) {
            return back()->with('error', 'Password baru tidak sesuai dengan konfirmasi password');
        }
        $employee = Auth::user();
        if (!Hash::check($old_password, $employee->password)) {
            return back()->with('error', 'Password lama salah');
        } else {
            $employee->password = Hash::make($request->new_password);
            $employee->save();
        }
        return redirect('/profile')->with('success', 'Berhasil mengubah password');
    }
}
