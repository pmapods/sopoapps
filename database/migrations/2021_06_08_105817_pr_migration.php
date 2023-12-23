<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PrMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->nullable();
            $table->integer('armada_ticket_id')->nullable();
            $table->integer('security_ticket_id')->nullable();
            $table->integer('created_by');
            $table->boolean('isBudget')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 Menunggu proses otorisasi
            // 1 proses otorisasi selesai / menunggu nomor asset
            // 2 nomor asset sudah di update / ready for po
            // -1 rejected
            $table->integer('assetnumber_by')->nullable();
            // -1 system / web asset
            $table->integer('rejected_by')->nullable();
            $table->string('reject_reason')->nullable();
            
            // informasi revisi yang tanpa approval ulang
            $table->integer('revised_by')->nullable();
            $table->timestamp('revised_at')->nullable();
            $table->string('revise_ba_filepath')->nullable();
            $table->text('revise_reason')->nullable();
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('pr_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pr_id')->unsigned();
            $table->integer('ticket_item_id')->nullable();
            $table->string('name');
            $table->integer('qty');
            $table->double('price')->nullable();
            $table->double('ongkir_price')->nullable();
            $table->double('ongpas_price')->nullable();
            $table->string('uom')->nullable();
            $table->date('setup_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('isAsset')->nullable();
            
            $table->json('asset_number')->nullable();
            $table->string('asset_number_filepath')->nullable();
            $table->uuid('asset_number_token')->unique();
            $table->string('asset_number_by')->nullable();

            $table->foreign('pr_id')->references('id')->on('pr');
            $table->SoftDeletes();
            $table->timestamps();
        });

        schema::create('pr_log', function (Blueprint $table){
            $table->increments('id');
            $table->string('message');
            $table->integer('pr_id')->unsigned();
            $table->foreign('pr_id')->references('id')->on('pr');
            $table->timestamps();
        });

        Schema::create('pr_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pr_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // -1 terminate
            // 0 pending
            // 1 approved
            $table->foreign('pr_id')->references('id')->on('pr');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->SoftDeletes();
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
