<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

use App\Models\UploadReport;
use App\Models\UploadReportList;

class UploadReportController extends Controller
{
    public function view(){
        $upload_reports = UploadReport::all();
        return view('Reporting.uploadreport',compact('upload_reports'));
    }

    public function create(){
        try {
            DB::beginTransaction();
            $uploadreport = new UploadReport;
            $uploadreport->name = request()->name;
            $uploadreport->description = request()->description;
            $uploadreport->save();
            DB::commit();
            return back()->with('success','Berhasil menambah report');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menambah report');
        }
    }

    public function createFile(){
        try {
            DB::beginTransaction();
            $uploadreport = UploadReport::find(request()->upload_report_id);
            $uploadfile                     = new UploadReportList;
            $uploadfile->upload_report_id   = request()->upload_report_id;
            $uploadfile->description        = request()->description;
            $uploadfile->path               = "";
            $uploadfile->save();

            $filename = str_replace(' ','_',strtolower(request()->description));
            $ext = pathinfo(request()->file('file')->getClientOriginalName(), PATHINFO_EXTENSION);
            $name = $uploadfile->id."_".$filename.'.'.$ext;
            $path = "/uploadreport/".$name;
            $file = pathinfo($path);
            $path = request()->file('file')->storeAs($file['dirname'],$file['basename'],'public');
            $uploadfile->path               = $path;
            $uploadfile->save();
            DB::commit();
            return back()->with('success','Berhasil menambah report');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error','Gagal menambah report');
        }
    }
}
