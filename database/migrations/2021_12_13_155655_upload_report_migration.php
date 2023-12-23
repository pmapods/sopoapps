<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UploadReportMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('upload_report_list', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('upload_report_id')->unsigned();
            $table->string('description');
            $table->string('path');
            $table->foreign('upload_report_id')->references('id')->on('upload_report');
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
        Schema::dropIfExists('upload_report_list');
        Schema::dropIfExists('upload_report');
    }
}
