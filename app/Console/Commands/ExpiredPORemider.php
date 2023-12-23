<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArmadaTicket;
use App\Models\SecurityTicket;
use App\Models\Po;
use App\Models\PoManual;
use App\Models\SalesPoint;
use App\Models\EmployeeLocationAccess;
use App\Models\Employee;
use App\Models\EmailReminder;
use Mail;
use App\Mail\ReminderMail;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

use DB;

class ExpiredPORemider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'po:remindexpired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder po yang akan expired dalam jangka waktu yang belum di proses ke masing employee di salespoint';

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
        // ARMADA
        // cari po yang statusnya sedang aktif saat in
        // $current_date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-10-31 00:00:00')->addDays(7);
        // return;
        $current_date = CarbonImmutable::now();
        $datas = [];
        $pos = Po::whereIn('status', [3, 4])
            ->where('armada_ticket_id', '!=', null)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();
        $po_manuals = PoManual::whereIn('status', [3, 4])
            ->where('category_name', 'armada')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->end_date = $po->end_date;
            $niagatext = ($po->armada_ticket->armada_type->isNiaga == true) ? "Niaga" : "Non Niaga";
            $data->type = "Armada " . $niagatext;
            $data->gs_plate = $po->armada_ticket->gs_plate ?? '-';
            $data->gt_plate = $po->armada_ticket->gt_plate ?? '-';
            $data->vehicle_type = $po->armada_ticket->armada_type->name;
            if ($po->armada_ticket->mutation_salespoint_id != null) {
                $data->salespoint_name = $po->armada_ticket->mutation_salespoint->name;
                $data->salespoint_id = $po->armada_ticket->mutation_salespoint->id ?? -1;
            } else {
                $data->salespoint_name = $po->armada_ticket->salespoint->name;
                $data->salespoint_id = $po->armada_ticket->salespoint->id ?? -1;
            }
            $data->current_ticketing = $po->current_ticketing();

            // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
            if ($data->current_ticketing == null && $current_date->addDays(30) >= Carbon::parse($data->end_date)) {
                array_push($datas, $data);
            }
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number         = $po->po_number;
            $data->end_date          = $po->end_date;
            $niagatext = ($po->isNiaga == true) ? "Niaga" : "Non Niaga";
            $data->type = "Armada " . $niagatext;
            $data->gs_plate          = $po->gs_plate;
            $data->gt_plate          = $po->gt_plate;
            $data->vehicle_type      = $po->armada_name;
            $data->salespoint_name   = $po->salespoint_name;
            $searchsalespoint = SalesPoint::where('name', $po->salespoint_name)->first();
            if ($searchsalespoint) {
                $data->salespoint_id     = $searchsalespoint->id;
            } else {
                $data->salespoint_id     = -1;
            }
            $data->current_ticketing = $po->current_ticketing();

            // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
            if ($data->current_ticketing == null && $current_date->addDays(30) >= Carbon::parse($data->end_date)) {
                array_push($datas, $data);
            }
        }

        // SECURITY
        // cari po yang statusnya sedang aktif saat ini
        $pos = Po::whereIn('status', [3, 4])
            ->where('security_ticket_id', '!=', null)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        $po_manuals = PoManual::whereIn('status', [3, 4])
            ->where('category_name', 'SECURITY')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->end_date = $po->end_date;
            $data->type = "Security";
            $data->salespoint_name = $po->security_ticket->salespoint->name;
            $data->salespoint_id = $po->security_ticket->salespoint->id ?? -1;
            $data->current_ticketing = $po->current_ticketing();
            // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
            if ($data->current_ticketing == null && $current_date->addDays(60) >= Carbon::parse($data->end_date)) {
                array_push($datas, $data);
            }
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->end_date = $po->end_date;
            $data->salespoint_name = $po->salespoint_name;
            $searchsalespoint = SalesPoint::where('name', $po->salespoint_name)->first();
            if ($searchsalespoint) {
                $data->salespoint_id     = $searchsalespoint->id;
            } else {
                $data->salespoint_id     = -1;
            }
            $data->type = "Security";
            $data->current_ticketing = $po->current_ticketing();
            // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
            if ($data->current_ticketing == null && $current_date->addDays(60) >= Carbon::parse($data->end_date)) {
                array_push($datas, $data);
            }
        }

        // Barang Jasa
        // cari po yang statusnya sedang aktif saat ini
        $pos = Po::whereIn('status', [3, 4])
            ->where('ticket_id', '!=', null)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        $po_manuals = PoManual::whereIn('status', [3, 4])
            ->whereIn('category_name', ['CIT', 'PEST CONTROL', 'MERCHANDISER'])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();
        foreach ($pos as $po) {
            $data = new \stdClass();
            $data->po_number = $po->no_po_sap;
            $data->end_date = $po->end_date;
            $data->salespoint_name = $po->ticket->salespoint->name;
            $data->salespoint_id = $po->ticket->salespoint->id;
            $data->type = "Barang Jasa";
            $data->current_ticketing = $po->current_ticketing();
            // jika po belum di proses / null. dan sudah dalam masa tenggang maka tambahkan ke dalam array
            if ($data->current_ticketing == null && $current_date->addDays(30) > Carbon::parse($data->end_date)) {
                array_push($datas, $data);
            }
        }
        foreach ($po_manuals as $po) {
            $data = new \stdClass();
            $data->po_number = $po->po_number;
            $data->end_date = $po->end_date;
            $data->salespoint_name = $po->salespoint_name;
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
                case 'MERCHANDISER':
                    $data->type = "Merchandiser";
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
        $pos = collect($datas);
        $selected_reminders = ["po_merchandiser"];
        // ticketing_type_name
        // block_day
        // Security
        // CIT
        // Pest Control
        // Armada
        $armada_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Armada')->first();
        $security_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Security')->first();
        $cit_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'CIT')->first();
        $pest_ticketing_block = DB::table('ticketing_block')->where('ticketing_type_name', 'Pest Control')->first();
        if (now()->day <= $armada_ticketing_block->max_block_day) {
            array_push(
                $selected_reminders,
                "po_armada_niaga",
                "po_armada_non_niaga"
            );
        }
        if (now()->day <= $security_ticketing_block->max_block_day) {
            array_push($selected_reminders, "po_security");
        }
        if (now()->day <= $cit_ticketing_block->max_block_day) {
            array_push($selected_reminders, "po_cit");
        }
        if (now()->day <= $pest_ticketing_block->max_block_day) {
            array_push($selected_reminders, "po_pest_control");
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
            $type = str_replace("PO ", "", $emailreminder->type());
            $mindays = 30;
            if ($type == "Security") {
                $mindays = 60;
            }
            if ($type == "Merchandiser") {
                $mindays = 90;
            }
            foreach ($salespoints as $salespoint) {
                foreach ($emailreminder->detail as $detail) {
                    $days = $detail->days;
                    $isMaxDays = $detail->isMaxDays();
                    $selectedpos = $pos->where('salespoint_name', $salespoint->name)->where('type', $type);
                    $selectedpos = $selectedpos->filter(function ($item) use ($days, $mindays, $current_date, $isMaxDays) {
                        // 2mindays - days
                        // 31 Agustus
                        // H-30 (30)
                        // H-28 (32)
                        // H-26 (34)
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
                            Mail::to($employee_emails)->send(new ReminderMail($data, 'poreminder'));
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
    }
}
