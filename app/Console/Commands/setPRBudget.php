<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pr;
use DB;

class setPRBudget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pr:refreshbudget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh budget yang masih null';

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
        $prs = Pr::where('isBudget',null)->get();
        DB::beginTransaction();
        foreach ($prs as $pr) {
            if($pr->ticket_id != null){
                if($pr->ticket->budget_type == 0){
                    $budget = true;
                }else{
                    $budget = false;
                }
            }
            if($pr->armada_ticket_id != null){
                $budget = true;
            }
            if($pr->security_ticket_id != null){
                $isBudget = true;
                // pengadaan lembur
                if($pr->security_ticket->ticketing_type == 4){
                    $isBudget = false;
                }
                $budget = $isBudget;
            }
            $pr->isBudget = $budget;
            $pr->save();
        }
        DB::commit();
    }
}
