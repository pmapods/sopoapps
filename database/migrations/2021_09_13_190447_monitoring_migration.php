<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MonitoringMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_monitoring', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->integer('employee_id');
            $table->string('employee_name');
            $table->string('message');
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->timestamps();
        });

        Schema::create('armada_ticket_monitoring', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('armada_ticket_id')->unsigned();
            $table->integer('employee_id');
            $table->string('employee_name');
            $table->string('message');
            $table->foreign('armada_ticket_id')->references('id')->on('armada_ticket');
            $table->timestamps();
        });

        Schema::create('security_ticket_monitoring', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('security_ticket_id')->unsigned();
            $table->integer('employee_id');
            $table->string('employee_name');
            $table->string('message');
            $table->foreign('security_ticket_id')->references('id')->on('security_ticket');
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
