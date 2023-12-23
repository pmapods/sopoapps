<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SecurityTicketingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_ticket', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned()->nullable();
            $table->string('code')->unique();
            $table->integer('salespoint_id')->unsigned();
            $table->string('po_reference_number')->nullable();
            // po_reference untuk mutasi replace renew
            $table->string('po_number')->nullable();
            // po_number untuk po yang ke create baru karena tiket ini
            $table->string('vendor_name')->nullable();
            $table->string('vendor_recommendation_name')->nullable();
            $table->tinyInteger('ticketing_type');
            // 0 Pengadaan                
            // 1 Perpanjangan
            // 2 Replace
            // 3 End Kontrak
            // 4 Pengadaan Lembur
            $table->tinyInteger('status')->default(0);
            // -1 Terminated
            // 0 New
            // 1 Pending Authorization
            // 2 Finish Authorization
            // 3 Otorisasi PR Dimulai
            // 4 Dalam Proses PO
            // 5 Menunggu Upload Berkas Penerimaan
            // 6 Selesai / sudah diterima
            $table->text('reason')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();

            $table->date('requirement_date');
            $table->integer('personil_count')->nullable();
            $table->date('finished_date')->nullable();

            $table->string('ba_path')->nullable();
            $table->string('lpb_path')->nullable();
            $table->string('endkontrak_path')->nullable();

            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('security_ticket_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('security_ticket_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            $table->text('reject_notes')->nullable();
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('security_ticket_id')->references('id')->on('security_ticket');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->timestamps();
        });

        Schema::create('evaluasi_form', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('security_ticket_id')->unsigned();
            $table->integer('salespoint_id')->nullable();
            $table->string('vendor_name');
            $table->date('period');
            $table->string('salespoint_name');
            $table->json('personil');
            $table->json('lembaga');
            $table->tinyInteger('kesimpulan');
            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->foreign('security_ticket_id')->references('id')->on('security_ticket');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('evaluasi_form_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('evaluasi_form_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('evaluasi_form_id')->references('id')->on('evaluasi_form');
            $table->foreign('employee_id')->references('id')->on('employee');
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
    }
}
