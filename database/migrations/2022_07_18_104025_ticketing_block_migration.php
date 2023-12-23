<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TicketingBlockMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketing_block', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ticketing_type_name');
            // tanggal block untuk request perpanjangan area
            $table->string('block_day');
            // tanggal max block untuk request perpanjangan area
            $table->string('max_block_day');
            // tanggal block untuk request perpanjangan area
            $table->integer('max_pr_sap_day');
            // tanggal block untuk request perpanjangan area
            $table->integer('max_validation_reject_day');
            $table->timestamps();
        });

        Schema::create('ticketing_block_open_request', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ticket_code');
            $table->string('po_number');
            $table->string('ba_file_path');
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 pending confirmation
            // 1 confirmed
            // -1 rejected
            $table->integer('created_by');
            $table->text('reject_reason')->nullable();
            $table->integer('confirmed_by')->nullable();
            $table->integer('rejected_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
