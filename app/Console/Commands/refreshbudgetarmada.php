<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArmadaBudget;
use App\Models\ArmadaType;

class refreshbudgetarmada extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'armadabudget:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'refreh tipe armada berdasarkan nama';

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
        $armadabudget = ArmadaBudget::withTrashed()->get();
        foreach ($armadabudget as $budget){
            $armadatype = ArmadaType::where('name',trim($budget->armada_type_name))->first();
            if($armadatype){
                $budget->armada_type_id = $armadatype->id;
                $budget->save();
            }
        }
    }
}
