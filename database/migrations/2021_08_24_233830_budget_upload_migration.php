<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BudgetUploadMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_upload', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salespoint_id')->unsigned();
            $table->string('code')->unique();
            $table->string('division')->nullable();
            $table->string('year')->nullable();
            $table->enum('type',['inventory','armada','security','assumption','ho']);
            $table->tinyInteger('status')->default(0);
            // -1 reject
            // 0 pending
            // 1 active
            // 2 inactive
            $table->integer('created_by')->nullable();
            $table->integer('rejected_by')->nullable();
            $table->text('reject_notes')->nullable();
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('budget_upload_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('budget_upload_id')->references('id')->on('budget_upload');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('inventory_budget', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned();
            $table->string('code');
            $table->string('keterangan');
            $table->integer('qty');
            $table->double('value');
            $table->foreign('budget_upload_id')->references('id')->on('budget_upload');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('armada_budget', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned();
            $table->integer('armada_type_id');
            $table->string('armada_type_name');
            $table->string('vendor_code');
            $table->string('vendor_name');
            $table->integer('qty');
            $table->double('value');
            $table->foreign('budget_upload_id')->references('id')->on('budget_upload');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('assumption_budget', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned();
            $table->integer('maintenance_budget_id')->nullable();
            $table->string('code');
            $table->string('group');
            $table->string('name');
            $table->integer('qty');
            $table->double('value');
            $table->foreign('budget_upload_id')->references('id')->on('budget_upload');
            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::create('ho_budget_upload', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned();
            $table->integer('ho_budget_id')->nullable();
            $table->string('code');
            $table->string('category');
            $table->string('name');
            // $table->enum('frequency',['monthly', 'quarterly', 'yearly', 'if any']);
            $table->json('values');
            $table->foreign('budget_upload_id')->references('id')->on('budget_upload');
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('budget_upload');
        Schema::dropIfExists('budget_upload_authorization');
        Schema::dropIfExists('inventory_budget');
        Schema::dropIfExists('armada_budget');
        Schema::dropIfExists('assumption_budget');
        Schema::dropIfExists('ho_budget_upload');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
