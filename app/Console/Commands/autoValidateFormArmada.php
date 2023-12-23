<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\ArmadaTicket;

class autoValidateFormArmada extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'armadaticket:autovalidateform';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto validasi form perpanjanga,mutasi,fasilitas untuk pengadaan armada yang statusnya 4,5,6';

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
        DB::beginTransaction();
        try {
            $armada_tickets = ArmadaTicket::whereIn('status', [4,5,6])->get();
            foreach ($armada_tickets as $armada_ticket) {
                if (isset($armada_ticket->facility_form)) {
                    if ($armada_ticket->facility_form->is_form_validated == false) {
                        $facility_form = $armada_ticket->facility_form;
                        $facility_form->is_form_validated = true;
                        $facility_form->validated_at = now();
                        $facility_form->validated_by = 1;
                        $facility_form->save();
                    }
                }
                if (isset($armada_ticket->perpanjangan_form)) {
                    if ($armada_ticket->perpanjangan_form->is_form_validated == false) {
                        $perpanjangan_form = $armada_ticket->perpanjangan_form;
                        $perpanjangan_form->is_form_validated = true;
                        $perpanjangan_form->validated_at = now();
                        $perpanjangan_form->validated_by = 1;
                        $perpanjangan_form->save();
                    }
                }
                if (isset($armada_ticket->mutasi_form)) {
                    if ($armada_ticket->mutasi_form->is_form_validated == false) {
                        $mutasi_form = $armada_ticket->mutasi_form;
                        $mutasi_form->is_form_validated = true;
                        $mutasi_form->validated_at = now();
                        $mutasi_form->validated_by = 1;
                        $mutasi_form->save();
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            print('error : '.$ex->getMessage());
            DB::rollback();
        }
    }
}
