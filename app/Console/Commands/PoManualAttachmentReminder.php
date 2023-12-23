<?php

namespace App\Console\Commands;

use DB;
use Mail;
use DateTime;
use Carbon\Carbon;
use App\Models\PoManual;
use App\Mail\ReminderMail;
use App\Models\SalesPoint;
use Carbon\CarbonImmutable;
use App\Models\EmailReminder;
use App\Models\EmailAdditional;
use Illuminate\Console\Command;

class PoManualAttachmentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pomanualattachment:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder PO Manual Attachment Setelah 45 Hari Masuk Ke PODS';

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
            $datas = [];
            $type = '-';

            $po_manual_barang_jasa = PoManual::where('category_name', '=', 'BARANG JASA')
                ->where('created_at', '!=', null)
                ->where(function ($query) {
                    $query->where('barang_jasa_form_bidding_filepath', '=', null)
                        ->orWhere('barang_jasa_pr_manual_filepath', '=', null)
                        ->orWhere('barang_jasa_po_filepath', '=', null)
                        ->orWhere('barang_jasa_lpb_filepath', '=', null)
                        ->orWhere('barang_jasa_invoice_filepath', '=', null);
                })
                ->get();

            foreach ($po_manual_barang_jasa as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->salespoint_name = $po->salespoint_name;
                $data->category_name = $po->category_name;
                $data->item_name = $po->item_name ? $po->item_name : '-';
                $data->vendor_name = $po->vendor_name;
                $data->armada_name = $po->armada_name ? $po->armada_name : '-';
                $data->gs_plate = $po->gs_plate ? $po->gs_plate : '-';
                $data->gt_plate = $po->gt_plate ? $po->gt_plate : '-';

                $datetime1 = Carbon::parse($po->created_at);
                $datetime2 = Carbon::parse(now()->format('Y-m-d'));
                $data->interval = $datetime1->diffInDays($datetime2);

                if ($data->interval > 45) {
                    array_push($datas, $data);
                }
            }

            $po_manual_armada = PoManual::where('category_name', '=', 'ARMADA')
                ->where('created_at', '!=', null)
                ->where('armada_pr_manual_filepath', '=', null)
                ->get();

            foreach ($po_manual_armada as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->salespoint_name = $po->salespoint_name;
                $data->category_name = $po->category_name;
                $data->item_name = $po->item_name ? $po->item_name : '-';
                $data->vendor_name = $po->vendor_name;
                $data->armada_name = $po->armada_name ? $po->armada_name : '-';
                $data->gs_plate = $po->gs_plate ? $po->gs_plate : '-';
                $data->gt_plate = $po->gt_plate ? $po->gt_plate : '-';

                $datetime1 = Carbon::parse($po->created_at);
                $datetime2 = Carbon::parse(now()->format('Y-m-d'));
                $data->interval = $datetime1->diffInDays($datetime2);

                if ($data->interval >  45) {
                    array_push($datas, $data);
                }
            }

            $po_manual_security = PoManual::where('category_name', '=', 'SECURITY')
                ->where('created_at', '!=', null)
                ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
                ->get();

            foreach ($po_manual_security as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->salespoint_name = $po->salespoint_name;
                $data->category_name = $po->category_name;
                $data->item_name = $po->item_name ? $po->item_name : '-';
                $data->vendor_name = $po->vendor_name;
                $data->armada_name = $po->armada_name ? $po->armada_name : '-';
                $data->gs_plate = $po->gs_plate ? $po->gs_plate : '-';
                $data->gt_plate = $po->gt_plate ? $po->gt_plate : '-';

                $datetime1 = Carbon::parse($po->created_at);
                $datetime2 = Carbon::parse(now()->format('Y-m-d'));
                $data->interval = $datetime1->diffInDays($datetime2);

                if ($data->interval >  45) {
                    array_push($datas, $data);
                }
            }

            $po_manual_cit = PoManual::where('category_name', '=', 'CIT')
                ->where('created_at', '!=', null)
                ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
                ->get();

            foreach ($po_manual_cit as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->salespoint_name = $po->salespoint_name;
                $data->category_name = $po->category_name;
                $data->item_name = $po->item_name ? $po->item_name : '-';
                $data->vendor_name = $po->vendor_name;
                $data->armada_name = $po->armada_name ? $po->armada_name : '-';
                $data->gs_plate = $po->gs_plate ? $po->gs_plate : '-';
                $data->gt_plate = $po->gt_plate ? $po->gt_plate : '-';

                $datetime1 = Carbon::parse($po->created_at);
                $datetime2 = Carbon::parse(now()->format('Y-m-d'));
                $data->interval = $datetime1->diffInDays($datetime2);

                if ($data->interval > 45) {
                    array_push($datas, $data);
                }
            }

            $po_manual_pest_control = PoManual::where('category_name', '=', 'PEST CONTROL')
                ->where('created_at', '!=', null)
                ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
                ->get();

            foreach ($po_manual_pest_control as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->salespoint_name = $po->salespoint_name;
                $data->category_name = $po->category_name;
                $data->item_name = $po->item_name ? $po->item_name : '-';
                $data->vendor_name = $po->vendor_name;
                $data->armada_name = $po->armada_name ? $po->armada_name : '-';
                $data->gs_plate = $po->gs_plate ? $po->gs_plate : '-';
                $data->gt_plate = $po->gt_plate ? $po->gt_plate : '-';

                $datetime1 = Carbon::parse($po->created_at);
                $datetime2 = Carbon::parse(now()->format('Y-m-d'));
                $data->interval = $datetime1->diffInDays($datetime2);

                if ($data->interval > 45) {
                    array_push($datas, $data);
                }
            }

            $po_manual_merchandiser = PoManual::where('category_name', '=', 'MERCHANDISER')
                ->where('created_at', '!=', null)
                ->where('security_cit_pestcontrol_merchandiser_pr_manual_filepath', '=', null)
                ->get();

            foreach ($po_manual_merchandiser as $po) {
                $data = new \stdClass();
                $data->po_number = $po->po_number;
                $data->salespoint_name = $po->salespoint_name;
                $data->category_name = $po->category_name;
                $data->item_name = $po->item_name ? $po->item_name : '-';
                $data->vendor_name = $po->vendor_name;
                $data->armada_name = $po->armada_name ? $po->armada_name : '-';
                $data->gs_plate = $po->gs_plate ? $po->gs_plate : '-';
                $data->gt_plate = $po->gt_plate ? $po->gt_plate : '-';

                $datetime1 = Carbon::parse($po->created_at);
                $datetime2 = Carbon::parse(now()->format('Y-m-d'));
                $data->interval = $datetime1->diffInDays($datetime2);

                if ($data->interval > 45) {
                    array_push($datas, $data);
                }
            }

            $employee_emails = EmailAdditional::where('type', 'reminder')->where('category', 'po_manual')->first()->emails ?? [];
            $employee_emails = json_decode($employee_emails);

            $pos = collect($datas);
            $data = array(
                'original_emails' => $employee_emails,
                'transaction_type' => 'PO Manual File Attachment',
                'from' => 'PODS PO Manual File Attachment',
                'type' => $type,
                'pos_list' => $pos,
                // 'ticketing_type' => $data->type,
                'to' => 'Purchasing Team',
            );

            if (config('app.env') == 'local') {
                $employee_emails = [config('mail.testing_email')];
            }
            $emailflag = true;
            try {
                Mail::to($employee_emails)->send(new ReminderMail($data, 'pomanualattachmentreminder'));
            } catch (\Exception $ex) {
                $emailflag = false;
                print('failed to sent reminder ' . $ex->getMessage());
            }
            if ($emailflag) {
                print('successfully sent reminder' . "\n");
            }
        } catch (\Exception $ex) {
            dd($ex);
        }
    }
}
