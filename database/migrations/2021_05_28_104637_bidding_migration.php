<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BiddingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_bidding', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("ticket_id")->unsigned();
            $table->integer("ticket_item_id")->unsigned();
            $table->json("vendors");
            $table->tinyInteger("status")->default(0);
            $table->string("filepath");
            $table->integer("created_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->string("delete_reason")->nullable();
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->foreign('ticket_item_id')->references('id')->on('ticket_item');
            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::create('bidding', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->integer('ticket_item_id')->unsigned();
            $table->string('product_name');
            $table->string('salespoint_name');
            $table->enum('group', ['asset', 'inventory', 'others']);
            $table->string('other_name')->nullable();

            $table->text('price_notes')->nullable();
            $table->string('ketersediaan_barang_notes')->nullable();
            $table->string('ketentuan_bayar_notes')->nullable();
            $table->string('others_notes')->nullable();

            $table->string('optional1_name')->nullable();
            $table->string('optional2_name')->nullable();

            $table->integer('authorization_id');
            $table->tinyInteger('status')->default(0);
            // -1 Rejected
            // 0 Pending
            // 1 Approved
            $table->string('rejected_by')->nullable();
            $table->string('reject_notes')->nullable();

            $table->string('signed_filename')->nullable();
            $table->string('signed_filepath')->nullable();

            // kosong untuk pengadaan HO
            $table->date('expired_date')->nullable();

            $table->text('notes')->nullable();
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->foreign('ticket_item_id')->references('id')->on('ticket_item');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('bidding_detail', function (Blueprint $table){
            $table->increments('id');
            $table->integer('bidding_id')->unsigned();
            $table->integer('ticket_vendor_id')->unsigned();
            $table->string('address');
            $table->double('start_harga');
            $table->double('end_harga');
            $table->double('start_ppn');
            $table->double('end_ppn');
            $table->double('start_ongkir_price');
            $table->double('end_ongkir_price');
            $table->double('start_pasang_price');
            $table->double('end_pasang_price');
            $table->tinyInteger('price_score');

            $table->string("spesifikasi");
            $table->string("ready");
            $table->string("indent");
            $table->string("garansi");
            $table->string("bonus");
            $table->tinyInteger('ketersediaan_barang_score');

            $table->enum('creditcash',['credit','cash']);
            $table->boolean('menerbitkan_faktur_pajak');
            $table->tinyInteger('ketentuan_bayar_score');

            $table->integer('masa_berlaku_penawaran');
            $table->integer('start_lama_pengerjaan');
            $table->integer('end_lama_pengerjaan');
            $table->string('optional1_start')->nullable();
            $table->string('optional1_end')->nullable();
            $table->string('optional2_start')->nullable();
            $table->string('optional2_end')->nullable();
            $table->tinyInteger('others_score');
            $table->foreign('bidding_id')->references('id')->on('bidding');
            $table->foreign('ticket_vendor_id')->references('id')->on('ticket_vendor');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('bidding_authorization', function (Blueprint $table){
            $table->increments('id');
            $table->integer('bidding_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // -1 terminate
            // 0 pending
            // 1 approved
            $table->foreign('bidding_id')->references('id')->on('bidding');
            $table->foreign('employee_id')->references('id')->on('employee');
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
