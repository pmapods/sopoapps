<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Po;
use App\Models\POUploadRequest;
use App\Models\POMail;
use App\Models\TicketMonitoring;
use App\Models\ArmadaTicketMonitoring;
use App\Models\SecurityTicketMonitoring;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

use DB;
use Auth;
use Mail;
use Illuminate\Support\Str;

class SendEmailPO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // how to run the artisan :
    // php artisan po:sendemail 3011022693,3011022697,3012005022

    protected $signature = 'po:sendemail {no_po}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email po ke vendor';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // get id po dari command prompt
        $no_pos = $this->argument('no_po');

        // jadikan id po array untuk select ke db
        $data_po = explode(',', $no_pos);

        $emailflag = true;
        $emailmessage = "";
        try {
            DB::beginTransaction();
            $pos = Po::whereIn('status', [1])
                ->whereNull('external_signed_filepath')
                ->whereNull('upload_external_signed_by')
                ->whereNull('deleted_at')
                ->whereIn('no_po_sap', $data_po)
                ->get();

            foreach ($pos as $po) {

                $old_po_upload_request = $po->po_upload_request;

                if ($old_po_upload_request) {
                    $old_po_upload_request->isExpired = true;
                    $old_po_upload_request->save();
                    $old_po_upload_request->delete();
                }

                $po_upload_request               = new POUploadRequest;
                $po_upload_request->id           = (string) Str::uuid();
                $po_upload_request->po_id        = $po->id;
                if (strtolower(trim($po->sender_name)) == 'one time vendor') {
                    $po_upload_request->vendor_name  = $po->sender_address;
                } else {
                    $po_upload_request->vendor_name  = $po->sender_name;
                }
                if ($po->ticket_id != null) {
                    $po_upload_request->vendor_pic   = $po->supplier_pic_name ?? "";
                }
                if ($po->armada_ticket_id != null || $po->security_ticket_id != null) {
                    $po_upload_request->vendor_pic   = $po->sender_name;
                }
                $po_upload_request->save();

                $po->po_upload_request_id = $po_upload_request->id;
                $po->save();

                if (count($this->email_text_to_array($request->email)) < 1) {
                    throw new \Exception("Minimal satu email yang dibutuhkan untuk mengirim PO (" . $request->email . ")");
                }
                $mail_to = $this->email_text_to_array($request->email);
                $ccs = $this->email_text_to_array($request->cc);
                $mail_subject = $request->mail_subject;
                $data = array(
                    'original_emails' => $mail_to,
                    'original_ccs' => $ccs,
                    'po' => $po,
                    'mail' => $mail_to,
                    'mail_subject' => $mail_subject,
                    'email_text' => $request->email_text,
                    'po_upload_request' => $po_upload_request,
                    'needVendorConfirmation' => 1,
                    'url' => url('/signpo/' . $po_upload_request->id),
                );
                if (config('app.env') == 'local') {
                    $mail_to = [config('mail.testing_email')];
                    $ccs = [];
                }

                try {
                    Mail::to($mail_to)->cc($ccs)->send(new POMail($data, 'posignedrequest'));
                } catch (\Exception $ex) {
                    throw new \Exception("Terjadi kesalahan dalam pengiriman email. Silahkan coba kembali / hubungi developer - " . $ex->getMessage() . $ex->getLine());
                    $emailflag = false;
                }
                if (!$emailflag) {
                    $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                }

                $po->status = 1;
                $po->last_mail_send_to  = $request->email;
                $po->last_mail_cc_to    = $request->cc;
                $po->last_mail_text     = $request->email_text;
                $po->last_mail_subject  = $request->mail_subject;
                $po->save();
                DB::commit();

                if ($po->ticket_id != null) {
                    $monitor = new TicketMonitoring;
                    $monitor->ticket_id      = $po->ticket->id;
                    $monitor->employee_id    = Auth::user()->id;
                    $monitor->employee_name  = Auth::user()->name;
                    $monitor->message        = 'Mengirim ulang email untuk PO ' . $po->no_po_sap;
                    $monitor->save();
                }
                if ($po->armada_ticket_id != null) {
                    $monitor = new ArmadaTicketMonitoring;
                    $monitor->armada_ticket_id      = $po->armada_ticket->id;
                    $monitor->employee_id           = Auth::user()->id;
                    $monitor->employee_name         = Auth::user()->name;
                    $monitor->message               = 'Mengirim ulang email untuk PO ' . $po->no_po_sap;
                    $monitor->save();
                }
                if ($po->security_ticket_id != null) {
                    $monitor = new SecurityTicketMonitoring;
                    $monitor->security_ticket_id      = $po->security_ticket->id;
                    $monitor->employee_id             = Auth::user()->id;
                    $monitor->employee_name           = Auth::user()->name;
                    $monitor->message                 = 'Mengirim ulang email untuk PO ' . $po->no_po_sap;
                    $monitor->save();
                }
                return back()->with('success', 'berhasil mengirim ulang email untuk po ' . $po->no_po_sap . ' ke email ' . implode(",", $mail_to) . $emailmessage);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Gagal Mengirimkan email (' . $ex->getMessage() . ')');
        }
    }
}
