7<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArmadaMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('armada_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('brand_name');
            $table->string('alias')->nullable();
            $table->boolean('isNiaga'); 
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('armada', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salespoint_id')->nullable();
            $table->integer('armada_type_id')->unsigned();
            $table->string('plate')->unique();
            $table->tinyInteger('status')->default('0');
            $table->date('vehicle_year')->nullable();
            // 0 available
            // 1 booked
            $table->string('booked_by')->nullable();
            $table->foreign('armada_type_id')->references('id')->on('armada_type');
            $table->softDeletes();
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
