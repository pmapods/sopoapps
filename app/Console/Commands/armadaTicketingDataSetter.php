<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArmadaTicket;
use App\Models\Po;
use App\Models\PoManual;
use App\Models\Armada;
use App\Models\ArmadaType;

use DB;

class armadaTicketingDataSetter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'armadaticketing:setarmadaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Armada id dan type id untuk ticketing perpanjangan/replace/renewal/stop sewa/mutasi yang null';

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
        $selected_armada_ticket = ArmadaTicket::whereIn('ticketing_type', [1, 2, 3, 4])->where('armada_id', null)->get();
        foreach ($selected_armada_ticket as $armada_ticket) {
            $po = Po::where('no_po_sap', $armada_ticket->po_reference_number)->first();
            $pomanual = PoManual::where('po_number', $armada_ticket->po_reference_number)->first();
            if ($po) {
                $armada_ticket->armada_type_id      = $po->armada_ticket->armada_type_id;
                $armada_ticket->armada_id           = $po->armada_ticket->armada_id;
            }
            if ($pomanual) {
                $plate = ($pomanual->gt_plate != "") ? $pomanual->gt_plate : $pomanual->gs_plate;
                $armada = Armada::where(DB::raw('trim(lower(plate))'), 'LIKE', '%' . trim(strtolower($plate)))->first();
                $armadatype = ArmadaType::where(DB::raw('trim(lower(name))'), 'LIKE', '%' . trim(strtolower($pomanual->armada_name)))->first();
                if ($armadatype == null) {
                    throw new \Exception('Tipe Armada ' . $pomanual->armada_name . ' tidak ditemukan di master jenis armada');
                }
                if ($armada == null) {
                    // buat Armada baru
                    $newArmada = new Armada;
                    $newArmada->armada_type_id  = $armadatype->id;
                    $newArmada->plate           = str_replace(' ', '', strtoupper($plate));
                    $newArmada->vehicle_year    = now()->format('Y') . '-01-01';
                    $newArmada->status          = 0;
                    $newArmada->save();

                    $armada = $newArmada;
                }

                $armada_ticket->armada_type_id      = $armadatype->id;
                $armada_ticket->armada_id           = $armada->id;
            }
            $armada_ticket->save();
            print_r($armada_ticket->toArray());
            print("\n");
        }
    }
}
