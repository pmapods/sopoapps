<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmployeeMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_position', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->SoftDeletes();
            $table->timestamps();
        });
        //daftar karyawan
        Schema::create('employee', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('nik')->unique();
            $table->string('email');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 Active
            // 1 Non Active
            $table->boolean('is_password_changed')->default(0);
            $table->text('signature_filepath')->nullable();
            $table->SoftDeletes();
            $table->timestamps();
        });

        // daftar akses tiap karyawan bisa di salespoint mana  aja
        Schema::create('employee_location_access',function (Blueprint $table){
            $table->increments('id');
            $table->integer('employee_id')->unsigned();
            $table->integer('salespoint_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->timestamps();
        });

        Schema::create('employee_menu_access', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id')->unsigned();
            $table->integer('masterdata')->default(0);
            $table->integer('budget')->default(0);
            $table->integer('operational')->default(0);
            $table->integer('monitoring')->default(0);
            $table->integer('reporting')->default(0);
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
