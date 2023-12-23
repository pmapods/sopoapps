<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AuthorizationMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorization', function (Blueprint $table) {
            $table->increments('id');
            // salespoint_id untuk form pengadaan bisa west/east/all/salespoint_id
            $table->string('salespoint_id')->nullable();
            $table->tinyInteger('form_type')->default(0);
            // 0 form pengadaan barang jasa
            // 1 form bidding
            // 2 form pr
            // 3 form po
            // 4 form fasilitas
            // 5 form mutasi
            // 6 form perpanjangan perhentian
            // 7 form pengadaan armada
            // 8 form pengadaan security
            // 9 form evaluasi
            // 10 Upload Budget (baru)
            // 11 Upload Budget (revisi)
            // 12 Form FRI
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('authorization_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('authorization_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->integer('employee_position_id')->unsigned();
            $table->string('sign_as');
            $table->integer('level');
            $table->foreign('authorization_id')->references('id')->on('authorization');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->foreign('employee_position_id')->references('id')->on('employee_position');
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
