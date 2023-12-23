<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Po;
use App\Models\PoManual;
use App\Models\SalesPoint;
use App\Models\EmailReminder;
use Mail;
use App\Mail\ReminderMail;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DB;

class VendorEvaluationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendorevaluation:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder vendor evaluation kepada area yang menggunakan jasa vendor tersebut';

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
        try {
            $current_date = CarbonImmutable::now();
            $datas = [];
            $pos = Po::whereIn('status', [3])
                ->join('ticket_item', 'po.ticket_id', '=', 'ticket_item.ticket_id')
                ->where('ticket_item.name', 'like', '%CIT%')
                ->orWhere('ticket_item.name', 'like', '%Pest Control%')
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->get();

            $po_manuals = PoManual::whereIn('status', [3, 4])
                ->whereIn('category_name', ['CIT', 'PEST CONTROL'])
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->get();

            foreach ($pos as $po) {
                $data = new \stdClass();
                $data->sender_name = $po->sender_name;
                $data->salespoint_name = $po->salespoint_name;
                $data->current_ticketing = $po->current_ticketing();
                $data->end_date = $po->end_date;
                $searchsalespoint = SalesPoint::where('name', $po->salespoint_name)->first();
                if ($searchsalespoint) {
                    $data->salespoint_id     = $searchsalespoint->id;
                } else {
                    $data->salespoint_id     = -1;
                }

                // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
                if ($data->current_ticketing == null && $current_date->addDays(30) >= Carbon::parse($data->end_date)) {
                    array_push($datas, $data);
                }
            }

            foreach ($po_manuals as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->end_date = $po->end_date;
                $data->salespoint_name = $po->salespoint_name;
                $data->vendor_name = $po->vendor_name;
                $searchsalespoint = SalesPoint::where('name', $po->salespoint_name)->first();
                if ($searchsalespoint) {
                    $data->salespoint_id     = $searchsalespoint->id;
                } else {
                    $data->salespoint_id     = -1;
                }
                switch ($po->category_name) {
                    case 'CIT':
                        $data->type = "CIT";
                        break;
                    case 'PEST CONTROL':
                        $data->type = "Pest Control";
                        break;
                    default:
                        $data->type = "-";
                        break;
                }
                $data->current_ticketing = $po->current_ticketing();
                // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
                if ($data->current_ticketing == null && $current_date->addDays(30) > Carbon::parse($data->end_date)) {
                    array_push($datas, $data);
                }
            }

            $selected_reminders = [];
            $pos = collect($datas);
            $vendor_evaluation_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Vendor Evaluation')->first();

            if (now()->day <= $vendor_evaluation_ticketing_block->max_block_day) {
                array_push($selected_reminders, 'vendor_evaluation');
            }

            $emailreminders = EmailReminder::whereIn("type", $selected_reminders)->get();
            foreach ($emailreminders as $emailreminder) {

                if ($emailreminder->salespoint_id == "all") {
                    $salespoints = SalesPoint::all();
                } else {
                    $salespoints = SalesPoint::where("id", $emailreminder->salespoint_id)->get();
                }
                if ($salespoints == null) {
                    continue;
                }

                $type = '-';
                $mindays = 30;
                foreach ($salespoints as $salespoint) {
                    foreach ($emailreminder->detail as $detail) {
                        $days = $detail->days;
                        $isMaxDays = $detail->isMaxDays();
                        $selectedpos = $pos->where('salespoint_id', $salespoint->id);
                        $selectedpos = $selectedpos->filter(function ($item) use ($days, $mindays, $current_date, $isMaxDays) {
                            if ($isMaxDays) {
                                if ($current_date->addDays($mindays)->subDays($days - $mindays)->format('Y-m-d') >= Carbon::parse($item->end_date)->format('Y-m-d')) {
                                    return true;
                                } else {
                                    return false;
                                }
                            } else {
                                if ($current_date->addDays($mindays)->subDays($days - $mindays)->format('Y-m-d') == Carbon::parse($item->end_date)->format('Y-m-d')) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        });

                        if ($selectedpos->count() > 0) {
                            $employee_emails = json_decode($detail->emails);
                            $data = array(
                                'original_emails' => $employee_emails,
                                'type' => $type,
                                'pos_list' => $selectedpos,
                                'salespoint_name' => $salespoint->name,
                            );

                            if (config('app.env') == 'local') {
                                $employee_emails = [config('mail.testing_email')];
                            }

                            $emailflag = true;
                            try {
                                Mail::to($employee_emails)->send(new ReminderMail($data, 'vendorevaluationreminder'));
                            } catch (\Exception $ex) {
                                $emailflag = false;
                                print('failed to sent reminder ' . $ex->getMessage());
                            }
                            if ($emailflag) {
                                print('successfully sent reminder' . "\n");
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            dd($ex);
        }
    }
}
