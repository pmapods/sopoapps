<?php

namespace App\Console\Commands;

use DB;
use stdClass;
use App\Models\PoManualNew;
use App\Models\HOBudgetUpload;
use Illuminate\Console\Command;

class HOBudgetSedder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hobudget:hobudgetsedder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
            DB::beginTransaction();

            $ho_budget = PoManualNew::leftjoin('budget_upload', function ($join) {
                $join->on('po_manual_new.salespoint_id', '=', 'budget_upload.salespoint_id');
                $join->on('po_manual_new.keterangan', '=', 'budget_upload.division');
            })
                ->leftjoin('ho_budget_upload', function ($join) {
                    $join->on('budget_upload.id', '=', 'ho_budget_upload.budget_upload_id');
                    $join->on('po_manual_new.item_code', '=', 'ho_budget_upload.code');
                })
                ->leftjoin('ho_budget', function ($join) {
                    $join->on('ho_budget_upload.ho_budget_id', '=', 'ho_budget.id');
                    $join->on('po_manual_new.item_code', '=', 'ho_budget.code');
                    $join->on('po_manual_new.jenis_it', '=', 'ho_budget.isIT');
                })
                ->where('po_manual_new.budget_or_non_budget', '=', 1)
                ->where('ho_budget_upload.deleted_at', '=', null)
                ->where('budget_upload.year', '=', 2023)
                ->where('budget_upload.status', 1)
                ->get();

            foreach ($ho_budget as $budget) {
                $ho_budget_value = json_decode($budget->values);
                $firstMonth = head($ho_budget_value);
                $anotherMonth = array_slice($ho_budget_value, 1);
                $qty_reduction = $firstMonth->qty - $budget->qty;
                $value_reduction = $firstMonth->value - $budget->harga;
                $stringArray = ['qty', 'value', 'months'];
                $ValueArray = [$qty_reduction, $value_reduction, 1];
                $outputArray = array_combine($stringArray, $ValueArray);

                $object = new stdClass();
                foreach ($outputArray as $key => $value) {
                    $object->$key = $value;
                }

                $myArray[] = $object;
                $merge = array_merge($myArray, $anotherMonth);

                $hbupm = HOBudgetUpload::where('ho_budget_upload.budget_upload_id', '=', $budget->budget_upload_id)
                    ->where('ho_budget_upload.code', '=', $budget->code)->first();
                $hbupm->values = json_encode($merge);
                $hbupm->save();
            }
            DB::commit();
            echo 'success';
        } catch (\Exception $ex) {
            print('error : ' . $ex->getMessage());
            DB::rollback();
        }
    }
}
