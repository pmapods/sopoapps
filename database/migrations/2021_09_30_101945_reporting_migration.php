<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accident', function (Blueprint $table) {
            $table->increments('id');
            $table->date('accident_date');
            $table->text('description');
            $table->string('filepath')->nullable();
            $table->enum('type',['armada','security','cit','pest_control','merchandiser']);
            $table->string('plate')->nullable();
            $table->integer('armada_id')->nullable();
            $table->integer('salespoint_id')->unsigned();
            $table->integer('vendor_id')->nullable();
            // vendor_name untuk vendor isian (cit,pest,monitoring)
            $table->string('vendor_name')->nullable();
            $table->integer('created_by');
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
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
        Schema::dropIfExists('accident');
    }
}
