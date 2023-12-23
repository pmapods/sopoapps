<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EmailAdditional;
use App\Models\EmployeeLocationAccess;
use App\Models\SalesPoint;
use App\Models\EmailReminder;
use App\Models\EmailReminderDetail;

use Auth;
use DB;
class MailingController extends Controller
{
    public function additionalEmailView(){
        $emails = EmailAdditional::whereNotIn('category',['purchasing','ga'])->get();
        return view('Masterdata.additionalemail',compact('emails'));
    }

    public function updateAdditionalEmail(Request $request){
        foreach($request->items as $key=>$item){
            $id = $key;
            $emailadditional = EmailAdditional::find($id);
            $text = $item['emails'];
            // pisahkan komanya
            $emails = explode(',',$text);
            foreach($emails as $key => $email){
                // trim setiap email
                $emails[$key] = strtolower(trim($email));
            }
            // jika ada email yang sama makan hapus sisain salah satu
            $emails = array_unique($emails);
            // validate apakah format email sesuai
            $emails = array_filter($emails,function($email){
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }else{
                    return true;
                }
            });
            // array_values fungsinya untuk ignore array keys yang bikin error saat json_encode
            $emailadditional->emails = json_encode(array_values($emails));
            $emailadditional->save();
        }
        return back()->with('success','berhasil Update additional email');
    }

    public function notificationEmailView(){
        $user_location_access  = Auth::user()->location_access->pluck('salespoint_id')->toArray();
        $salespoints = SalesPoint::whereIn('id',$user_location_access)->get();
        $regions = $salespoints->groupBy('region');
        array_push($user_location_access,"all");
        $emailreminders = EmailReminder::whereIn('salespoint_id',$user_location_access)->get();
        $registered_emails = [];
        $emailreminderdetails = EmailReminderDetail::whereIn('email_reminder_id',$emailreminders->pluck('id')->toArray())->get();
        foreach($emailreminderdetails as $emailreminderdetail){
            $emails = json_decode($emailreminderdetail->emails);
            foreach($emails as $email){
                if (!in_array($email, $registered_emails)){
                    array_push($registered_emails,$email);
                }
            }
        }
        return view('Masterdata.notificationemail',compact('regions','salespoints','emailreminders','registered_emails'));
    }

    public function purchasingEmailView(){
        $emails = EmailAdditional::where('category','purchasing')->get();
        return view('Masterdata.purchasingemail',compact('emails'));
    }
    
    public function GAEmailView(){
        $emails = EmailAdditional::where('category','ga')->get();
        return view('Masterdata.gaemail',compact('emails'));
    }

    public function createNotification(Request $request){
        try{
            $emailreminder = EmailReminder::where('type',$request->type)
                ->where('salespoint_id',$request->salespoint_id)
                ->first();

            $salespoint = SalesPoint::find($request->salespoint_id);
            // kalau sudah ada tolak request
            if($emailreminder){
                return back()->with('error','Notifikasi pada salespoint '.$salespoint->name.'terkait '.$request->type.'sudah exists');
            }
            DB::beginTransaction();
            $newEmailReminder                   = new EmailReminder;
            $newEmailReminder->type             = $request->type;
            $newEmailReminder->salespoint_id    = $request->salespoint_id;
            $newEmailReminder->save();

            foreach($request->item as $item){
                $newDetail                     = new EmailReminderDetail;
                $newDetail->email_reminder_id  = $newEmailReminder->id;
                $newDetail->days               = $item['daycount'];
                $emails                        = explode(',',$item['emails']);
                $emails = array_filter($emails,function($email){
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }else{
                        return true;
                    }
                });
                $newDetail->emails             = json_encode(array_values($emails));
                $newDetail->save();
            }

            DB::commit();
            return back()->with('success','Berhasil menambahkan notifikasi baru');
        }catch(\Exception $ex){
            DB::rollback();
        }
    }

    public function updateNotification(Request $request){
        try{
            $reminder = EmailReminder::findOrFail($request->reminder_id);
            DB::beginTransaction();
            foreach($reminder->detail as $detail) {
                $detail->delete();
            }
            foreach($request->item as $item){
                $newDetail                     = new EmailReminderDetail;
                $newDetail->email_reminder_id  = $reminder->id;
                $newDetail->days               = $item['daycount'];
                $emails                        = explode(',',$item['emails']);
                $emails = array_filter($emails,function($email){
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }else{
                        return true;
                    }
                });
                $newDetail->emails             = json_encode(array_values($emails));
                $newDetail->save();
            }

            DB::commit();
            return back()->with('success','Berhasil update notifikasi');
        }catch(\Exception $ex){
            DB::rollback();
            return back()->with('error','Gagal update notifikasi '.$ex->getMessage());
        }
    }

    public function deleteNotification(Request $request){
        try{
            $reminder = EmailReminder::findOrFail($request->reminder_id);
            DB::beginTransaction();
            foreach($reminder->detail as $detail) {
                $detail->delete();
            }
            $reminder->delete();
            DB::commit();
            return back()->with('success','Berhasil menghapus notifikasi');
        }catch(\Exception $ex){
            DB::rollback();
            return back()->with('error','Gagal menghapus notifikasi '.$ex->getMessage());
        }
    }

    public function getNotificationDetails(Request $request){
        try{
            $details = EmailReminderDetail::join('email_reminder','email_reminder.id','=','email_reminder_detail.email_reminder_id')
                ->where(DB::raw('lower(email_reminder_detail.emails)'),'like','%'.strtolower($request->email).'%')
                ->where('email_reminder.salespoint_id',$request->salespoint_id)
                ->select('email_reminder_detail.*')
                ->get();
            $formatted_details = [];
            foreach($details as $detail){
                $data = new \stdClass();
                $data->email_reminder_detail_id = $detail->id;
                $data->emails = json_decode($detail->emails);
                $data->days = $detail->days;
                $data->type_name = $detail->email_reminder->type();
                array_push($formatted_details,$data);
            } 
            return response()->json([
                "error" => false,
                "data" => $formatted_details
            ]);
        }catch(\Exception $ex){
            return response()->json([
                "error" => true,
                "data" => $ex->getMessage().$ex->getLine()
            ]);
        }
    }

    public function multiReplace(Request $request){
        try{
            DB::beginTransaction();
            $salespoint = SalesPoint::findOrFail($request->salespoint_id);

            $newRequest  = new Request;
            $newRequest->replace([
                'salespoint_id' => $request->salespoint_id,
                'email' => $request->email
            ]);
            $response = $this->getNotificationDetails($newRequest);
            $responseData = $response->getData();
            foreach($responseData->data as $data){
                $email_reminder_detail = EmailReminderDetail::find($data->email_reminder_detail_id);
                if($email_reminder_detail){
                    $email_reminder_detail->emails = str_replace($request->email,$request->to_email,$email_reminder_detail->emails);
                    $email_reminder_detail->save();
                }
            }
            DB::commit();
            return back()->with('success', "Berhasil melakukan multi replace terkait salespoint \"".$salespoint->name."\" dari email \"".$request->email."\" menjadi \"".$request->to_email."\"");
        }catch(\Exception $ex){
            DB::rollback();
            return back()->with('error', "Gagal melakukan multi replace (".$ex->getMessage().$ex->getLine().")");
        }

    }
}
