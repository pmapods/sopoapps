<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArmadaAccidentMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('armada_accident', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salespoint_id')->unsigned();
            $table->string('code')->nullable();
            $table->json('accident_causes')->nullable();
            $table->json('urgency')->nullable();
            $table->enum('accident_level',['lite','medium','heavy'])->nullable();
            $table->enum('accident_consecuence',['non tpl','tpl'])->nullable();
            $table->text('description')->nullable();
            $table->date('periode')->nullable();
            $table->date('handling_start_date')->nullable();
            $table->date('handling_end_date')->nullable();
            $table->integer('cost_remarks')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->timestamps();
        });

        Schema::create('vehicle_identity', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->integer('armada_id')->nullable();
            $table->string('nopol')->nullable();
            $table->string('cabang')->nullable();
            $table->enum('stnk_status',['berlaku','expired','tidak ada'])->nullable();
            $table->string('jenis_kendaraan')->nullable();
            $table->boolean('isNiaga')->nullable(); // Jenis Sewa
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
            $table->timestamps();
        });

        Schema::create('driver_identity', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('nik')->nullable();
            $table->string('jabatan')->nullable();
            $table->enum('status',['tetap','kontrak'])->nullable();
            $table->enum('jenis_sim',['C','A','B1','B2'])->nullable();
            $table->enum('sim_status',['berlaku','expired','tidak ada'])->nullable();
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
            $table->timestamps();
        });

        Schema::create('accident_pic_area', function (Blueprint $table){
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->string('nama')->nullable();
            $table->string('nik')->nullable();
            $table->enum('jabatan',['BM','SBH'])->nullable();
            $table->string('phone')->nullable();
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
            $table->timestamps();
        });

        Schema::create('accident_cost', function (Blueprint $table){
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->string('perobatan_korban')->nullable();
            $table->double('nominal_perobatan_korban')->nullable();
            $table->string('santunan')->nullable();
            $table->double('nominal_santunan')->nullable();
            $table->enum('biaya_unit_korban',['perbaikan','penggantian'])->nullable();
            $table->double('nominal_biaya_unit_korban')->nullable();
            $table->string('biaya_perkara')->nullable();
            $table->double('nominal_biaya_perkara')->nullable();
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
            $table->timestamps();
        });

        Schema::create('legal_aspect', function (Blueprint $table){
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->enum('status',['closed','on process','open'])->nullable();
            $table->string('remarks')->nullable();
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
            $table->timestamps();
        });
        
        Schema::create('insurance_aspect', function (Blueprint $table){
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->enum('conclusion',['claimable','unclaimable'])->nullable();
            $table->date('start_date_sla')->nullable();
            $table->date('end_date_sla')->nullable();
            $table->enum('status',['closed','on process','open'])->nullable();
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
            $table->timestamps();
        });
        
        Schema::create('recovery_accident_cost', function (Blueprint $table){
            $table->increments('id');
            $table->integer('armada_accident_id')->unsigned();
            $table->double('insurance_value')->nullable();
            $table->double('employee_value')->nullable();
            $table->foreign('armada_accident_id')->references('id')->on('armada_accident');
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
