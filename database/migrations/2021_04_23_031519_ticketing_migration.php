<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TicketingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned()->nullable();
            $table->string('po_reference_number')->nullable();
            $table->string('code')->nullable();
            // P01-[inisial area]-[tanggal permintaan][urutan permintaan ke berapa dari area]
            $table->date('requirement_date')->nullable();
            $table->integer('salespoint_id')->unsigned();
            $table->integer('authorization_id')->unsigned()->nullable();
            $table->tinyInteger('item_type')->nullable();
            // 0 barang
            // 1 Jasa
            // 2 Maintenance
            // 3 HO
            $table->tinyInteger('request_type')->nullable();
            // 0 Baru
            // 1 Replace Existing
            // 2 Repeat Order
            // 3 Perpanjangan
            // 4 End Kontrak
            $table->boolean('is_it')->nullable();
            $table->tinyInteger('budget_type')->nullable();
            // 0 Budget
            // 1 NonBudget

            // untuk pengadaan HO
            $table->string('division')->nullable();
            $table->integer('indirect_salespoint_id')->nullable();

            $table->text('reason')->nullable();
            $table->json('custom_settings')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 draft
            // 1 waiting for authorization / authorization started
            // 2 finished authorization / waiting for bidding
            // 3 finished bidding / ready for PR
            // 4 PR Created / Waiting for pr authorization
            // 5 PR authorization finished / menunggu kelengkapan data nomor asset
            // 6 Proses PR selesai / Ready for PO
            // 7 Closed PO / Finished
            // -1 terminated / cancelled
            $table->string('ba_vendor_filename')->nullable();
            $table->string('ba_vendor_filepath')->nullable();
            $table->tinyInteger('ba_status')->default(0);
            // 0 Pending
            // 1 Approved
            // -1 need revision
            $table->string('ba_reject_notes')->nullable();
            $table->integer('ba_rejected_by')->nullable();
            $table->integer('ba_revised_by')->nullable();
            $table->integer('ba_confirmed_by')->nullable();

            $table->date('finished_date')->nullable();
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->foreign('authorization_id')->references('id')->on('authorization');
            $table->foreign('created_by')->references('id')->on('employee');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('ticket_item', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->integer('budget_pricing_id')->nullable();
            $table->integer('maintenance_budget_id')->nullable();
            $table->integer('ho_budget_id')->nullable();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->date('expired_date')->nullable();
            $table->double('price');
            $table->integer('count');
            $table->string('lpb_filepath')->nullable();
            $table->string('invoice_filepath')->nullable();
            $table->boolean('isCancelled')->default(false);
            $table->boolean('isFinished')->default(false);
            $table->integer('cancelled_by')->nullable();
            $table->integer('confirmed_by')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('ticket_item_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_item_id')->unsigned();
            $table->string('name');
            $table->string('path');
            $table->tinyInteger('status')->default(0);
            // 0 Pending
            // 1 Approved
            // -1 need revision
            $table->string('reject_notes')->nullable();
            $table->integer('rejected_by')->nullable();
            $table->integer('revised_by')->nullable();
            $table->integer('confirmed_by')->nullable();
            $table->foreign('ticket_item_id')->references('id')->on('ticket_item');
            $table->timestamps();
        });

        Schema::create('ticket_item_file_requirement', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_item_id')->unsigned();
            $table->integer('file_completement_id');
            $table->string('name');
            $table->string('path');
            $table->tinyInteger('status')->default(0);
            // 0 Pending
            // 1 Approved
            // -1 need revision
            $table->string('reject_notes')->nullable();
            $table->integer('rejected_by')->nullable();
            $table->integer('revised_by')->nullable();
            $table->integer('confirmed_by')->nullable();
            $table->foreign('ticket_item_id')->references('id')->on('ticket_item');
            $table->timestamps();
        });

        Schema::create('ticket_vendor', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->integer('vendor_id')->nullable();
            $table->string('name');
            $table->string('salesperson');
            $table->string('phone');
            $table->tinyInteger('type');
            // 0 Registered Vendor
            // 1 One Time Vendor
            $table->enum('added_on',['ticketing','bidding'])->default('ticketing');
            $table->integer('deleted_by')->nullable();
            $table->text('delete_reason')->nullable();
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('ticket_additional_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->string('name');
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('ticket_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // 2 terminate
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->timestamps();
        });

        Schema::create('fri_form', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->date('date_request')->nullable();
            $table->date('date_use')->nullable();
            $table->string('work_location')->nullable();
            $table->integer('salespoint_id')->nullable();
            $table->string('salespoint_name')->nullable();
            $table->string('username_position')->nullable();
            $table->string('division_department')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            $table->json('hardware_details')->nullable();
            $table->json('disabled_hardware_details')->nullable();
            $table->json('application_details')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 new / waiting for approval
            // -1 terminated
            $table->foreign('ticket_id')->references('id')->on('ticket');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fri_form_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fri_form_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('fri_form_id')->references('id')->on('fri_form');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->timestamps();
        });

        Schema::create('custom_ticketing', function (Blueprint $table) {
            $table->increments('id');
            $table->json('settings');
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('ticket');
        Schema::dropIfExists('ticket_item');
        Schema::dropIfExists('ticket_item_attachment');
        Schema::dropIfExists('ticket_item_file_requirement');
        Schema::dropIfExists('ticket_vendor');
        Schema::dropIfExists('ticket_additional_attachment');
        Schema::dropIfExists('ticket_authorization');
    }
}
