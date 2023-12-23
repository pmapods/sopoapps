<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MailingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_reminder', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            // armada,security,cit,pest_control,merchants
            $table->string('salespoint_id');
            $table->timestamps();
        });
        Schema::create('email_reminder_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('email_reminder_id')->unsigned();
            $table->integer('days');
            $table->json('emails');
            $table->foreign('email_reminder_id')->references('id')->on('email_reminder');
            $table->timestamps();
        });

        Schema::create('email_additional', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category');
            $table->string('type');
            $table->json('emails')->nullable();
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
