<?php

namespace App\Console\Commands;

use DB;
use Mail;
use App\Models\Ticket;
use App\Mail\ReminderMail;
use App\Models\EmailReminder;
use App\Models\EmailAdditional;
use Illuminate\Console\Command;

class BarangJasaItReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barangjasait:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder barang jasa jenis IT h-30 sebelum po end date habis';

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

            $barang_jasa_it = Ticket::leftJoin('po', 'po.ticket_id', '=', 'ticket.id')
                ->where('ticket.is_it', 1)
                ->where('po.ticket_id', '!=', null)
                ->where('po.end_date', '!=', null)
                ->where('po.end_date', '>=', now()->subDays(30))
                ->get();

            // dd($barang_jasa_it);

            foreach ($barang_jasa_it as $ticket) {
                try {
                    $data = new \stdClass();
                    $data->salespoint = $ticket->salespoint->name;
                    $data->code = $ticket->code;
                    $data->created_at = $ticket->created_at->translatedFormat('d F Y (H:i)');
                    $data->created_by = $ticket->created_by_employee->name;
                    $date = date_create($ticket->end_date);
                    $end_date_po = date_format($date, "d F Y");
                    $data->end_date = $end_date_po;
                    array_push($datas, $data);
                } catch (\Throwable $e) {
                    continue;
                }
            }

            $employee_emails = EmailAdditional::where('type', 'reminder')->where('category', 'barang_jasa_it')->first()->emails ?? [];
            $employee_emails = json_decode($employee_emails);

            $pos = collect($datas);
            $data = array(
                'original_emails' => $employee_emails,
                'transaction_type' => 'Barang Jasa IT Reminder',
                'from' => 'PODS Barang Jasa IT Reminder',
                'type' => $type,
                'pos_list' => $pos,
                'to' => 'Purchasing & IT Team',
            );

            if (config('app.env') == 'local') {
                $employee_emails = [config('mail.testing_email')];
            }
            $emailflag = true;
            try {
                Mail::to($employee_emails)->send(new ReminderMail($data, 'barangjasaitreminder'));
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
