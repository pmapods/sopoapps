<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArmadaTicketingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('armada_ticket', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_upload_id')->unsigned()->nullable();
            $table->string('code')->unique();
            $table->integer('salespoint_id')->unsigned();
            $table->integer('mutation_salespoint_id')->nullable();
            $table->integer('armada_type_id')->nullable();
            $table->integer('armada_id')->nullable();
            $table->string('po_reference_number')->nullable();
            // po_reference untuk mutasi replace renew
            $table->string('po_number')->nullable();
            // po_number untuk po yang ke create baru karena tiket ini
            $table->string('vendor_name')->nullable();
            $table->string('vendor_recommendation_name')->nullable();
            $table->boolean('isNiaga');
            
            // hanya untuk pengadaan baru
            $table->boolean('isBudget')->nullable();
            
            $table->tinyInteger('ticketing_type');
            // 0 Pengadaan Baru                
            // 1 Perpanjangan/Replace/Renewal/Stop Sewa
            // 2 Mutasi
            // 3 COP
            $table->tinyInteger('status')->default(0);
            // -1 Terminated
            // 0 New
            // 1 Pending Authorization
            // 2 Finish Authorization
            // 3 Otorisasi PR Dimulai
            // 4 Dalam Proses PO
            // 5 Menunggu Upload Berkas Penerimaan
            // 6 Selesai / sudah diterima
            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->date('requirement_date');

            $table->date('finished_date')->nullable();
            $table->string('gs_plate')->nullable();
            $table->date('gs_received_date')->nullable();
            $table->string('gt_plate')->nullable();
            $table->date('gt_received_date')->nullable();
            $table->string('bastk_path')->nullable();
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('armada_ticket_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('armada_ticket_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->text('reject_notes')->nullable();
            $table->foreign('armada_ticket_id')->references('id')->on('armada_ticket');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->timestamps();
        });

        Schema::create('facility_form', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('armada_ticket_id')->unsigned();
            $table->integer('salespoint_id')->unsigned();
            $table->string('code');
            $table->string('nama');
            $table->string('divisi');
            $table->string('phone');
            $table->string('jabatan');
            $table->date('tanggal_mulai_kerja');
            $table->string('golongan');
            $table->enum('status_karyawan', ['percobaan', 'tetap']);
            $table->json('facilitylist')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->tinyInteger('status')->default(0);
            // -1 terminated
            // 0 new / waiting for approval
            $table->boolean('is_form_validated')->default(false);
            $table->integer('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreign('salespoint_id')->references('id')->on('salespoint');
            $table->foreign('armada_ticket_id')->references('id')->on('armada_ticket');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('facility_form_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('facility_form_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('facility_form_id')->references('id')->on('facility_form');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->timestamps();
        });

        Schema::create('perpanjangan_form',function(Blueprint $table){
            $table->increments('id');
            $table->integer('armada_ticket_id')->unsigned();
            $table->integer('salespoint_id')->nullable();
            $table->integer('armada_id')->nullable();
            $table->string('nama');
            $table->string('nik');
            $table->string('jabatan');
            $table->string('nama_salespoint');
            $table->enum('tipe_armada', ['niaga', 'nonniaga']);
            $table->string('jenis_kendaraan');
            $table->string('nopol');
            $table->enum('unit', ['GS', 'GT']);
            $table->boolean('is_vendor_lokal');
            $table->string('nama_vendor');
            $table->enum('form_type', ['perpanjangan', 'stopsewa']);
            $table->integer('perpanjangan_length')->nullable();
            $table->date('stopsewa_date')->nullable();     
            $table->enum('stopsewa_reason',['replace','renewal','end'])->nullable();  
            $table->boolean('is_percepatan')->default(false);
            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 new / waiting for approval
            // -1 terminated
            $table->boolean('is_form_validated')->default(false);
            $table->integer('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreign('armada_ticket_id')->references('id')->on('armada_ticket');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('perpanjangan_form_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('perpanjangan_form_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('perpanjangan_form_id')->references('id')->on('perpanjangan_form');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->timestamps();
        });
        
        Schema::create('mutasi_form',function(Blueprint $table){
            $table->increments('id');
            $table->integer('armada_ticket_id')->unsigned();
            $table->integer('salespoint_id')->nullable();
            $table->integer('receiver_salespoint_id')->nullable();
            $table->integer('armada_id')->nullable();
            $table->string('code')->unique();
            $table->string('sender_salespoint_name');
            $table->string('receiver_salespoint_name');
            $table->date('mutation_date');
            $table->date('received_date');
            $table->string('nopol');
            $table->string('vendor_name');
            $table->string('brand_name');
            $table->string('jenis_kendaraan');
            $table->string('nomor_rangka')->nullable();
            $table->string('nomor_mesin')->nullable();
            $table->smallInteger('tahun_pembuatan');
            $table->date('stnk_date');

            $table->boolean('p3k');
            $table->boolean('segitiga');
            $table->boolean('dongkrak');
            $table->boolean('toolkit');
            $table->boolean('ban');
            $table->boolean('gembok');
            $table->boolean('bongkar');
            $table->boolean('buku');

            $table->string('nama_tempat');

            $table->integer('created_by')->nullable();
            $table->integer('terminated_by')->nullable();
            $table->string('termination_reason')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 new / waiting for approval
            // -1 terminated
            $table->boolean('is_form_validated')->default(false);
            $table->integer('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreign('armada_ticket_id')->references('id')->on('armada_ticket');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('mutasi_form_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mutasi_form_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('employee_name');
            $table->string('as');
            $table->string('employee_position');
            $table->tinyInteger('level');
            $table->tinyInteger('status')->default(0);
            // 0 pending
            // 1 approved
            // -1 reject
            $table->foreign('mutasi_form_id')->references('id')->on('mutasi_form');
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
    }
}
