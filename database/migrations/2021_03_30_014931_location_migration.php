<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LocationMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salespoint', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('initial',7);
            $table->enum('region_type',['east','west']);
            $table->tinyInteger('region');
            //  0 MT CENTRAL 1
            //  1 SUMATERA 1
            //  2 SUMATERA 2
            //  3 SUMATERA 3
            //  4 SUMATERA 4
            //  5 BANTEN
            //  6 DKI
            //  7 JABAR 1
            //  8 JABAR 2
            //  9 JABAR 3
            //  10 JATENG 1
            //  11 JATENG 2
            //  12 JATIM 1
            //  13 JATIM 2
            //  14 BALINUSRA
            //  15 KALIMANTAN
            //  16 SULAWESI
            //  17 HO
            //  18 JATENG 3
            //  19 INDIRECT
            $table->tinyInteger('status');
            // 0 depo
            // 1 cabang
            // 2 cellpoint
            // 3 subdist / indirect
            // 4 nasional
            // 5 HO
            // 6 cellpoint+
            $table->tinyInteger('trade_type');
            // 0 MT Modern Trade
            // 1 GT General Trade
            // 2 INDIRECT
            $table->boolean('isJawaSumatra');
            $table->string('address')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
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
