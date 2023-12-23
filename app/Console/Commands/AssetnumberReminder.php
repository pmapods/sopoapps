<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Models\SalesPoint;
use App\Models\EmailReminder;
use Mail;
use App\Mail\ReminderMail;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

class AssetnumberReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assetnumber:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim Reminder untuk proses yang butuh dilengkapi untuk nomor assetnya';

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
        // TICKETING
        // cari ticket yang statusnya sedang menunggu nomor asset (5)

        $current_date = CarbonImmutable::now();
        $tickets = Ticket::where('status', 5)->get();
        $emailreminders = EmailReminder::whereIn("type", ["asset_number"])->get();
        foreach ($emailreminders as $emailreminder) {
            if ($emailreminder->salespoint_id == "all") {
                $salespoints = SalesPoint::all();
            } else {
                $salespoints = SalesPoint::where("id", $emailreminder->salespoint_id)->get();
            }
            if ($salespoints == null) {
                continue;
            }
            // $type = $emailreminder->type();
            $mindays = 0;
            foreach ($salespoints as $salespoint) {
                foreach ($emailreminder->detail as $detail) {
                    $days = $detail->days;
                    // $selectedpos = $pos->where('salespoint_name',$salespoint->name)->where('type',$type);
                    $selectedticket = $tickets->where('salespoint_id', $salespoint->id);
                    $selectedticket = $selectedticket->filter(function ($item) use ($days, $mindays, $current_date) {
                        if ($current_date->addDays($mindays)->subDays($days - $mindays)->format('Y-m-d') == Carbon::parse($item->pr->updated_at)->format('Y-m-d')) {
                            return true;
                        } else {
                            return false;
                        }
                    });
                    if ($selectedticket->count() > 0) {
                        $employee_emails = json_decode($detail->emails);
                        $data = array(
                            'original_emails' => $employee_emails,
                            'ticket_list' => $selectedticket,
                            'salespoint_name' => $salespoint->name,
                        );
                        if (config('app.env') == 'local') {
                            $employee_emails = [config('mail.testing_email')];
                        }
                        $emailflag = true;
                        try {
                            Mail::to($employee_emails)->send(new ReminderMail($data, 'assetnumberreminder'));
                        } catch (\Exception $ex) {
                            $emailflag = false;
                        }
                        $emailmessage = "";
                        if (!$emailflag) {
                            $emailmessage = "\n (" . config('customvariable.fail_email_text') . ")";
                        }
                        if ($emailflag) {
                            info('successfully sent reminder');
                        } else {
                            info('failed to sent reminder');
                        }
                    } else {
                    }
                }
            }
        }
    }
}
