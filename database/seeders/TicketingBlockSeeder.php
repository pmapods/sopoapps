<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class TicketingBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ticketing_block')->truncate();
        DB::table('ticketing_block')->insert([
            [
                'ticketing_type_name' => 'Armada',
                'block_day' => 10,
                'max_pr_sap_day' => 15,
                'max_validation_reject_day' => 18
            ],
            [
                'ticketing_type_name' => 'Security',
                'block_day' => 5,
                'max_pr_sap_day' => 10,
                'max_validation_reject_day' => 13
            ],
            [
                'ticketing_type_name' => 'CIT',
                'block_day' => 5,
                'max_pr_sap_day' => 10,
                'max_validation_reject_day' => 13
            ],
            [
                'ticketing_type_name' => 'Pest Control',
                'block_day' => 10,
                'max_pr_sap_day' => 15,
                'max_validation_reject_day' => 18
            ],
        ]);
    }
}
