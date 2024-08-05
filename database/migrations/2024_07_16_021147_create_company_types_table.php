<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateCompanyTypesTable extends Migration
{
    public function up()
    {
        Schema::create('company_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('name', 100)->index();
            $table->timestamps(); // Tambahkan kolom created_at dan updated_at
        });

        // Insert data
        DB::table('company_types')->insert([
            ['type' => 'barangjasa', 'name' => 'Barang Jasa'],
            ['type' => 'security', 'name' => 'Security'],
            ['type' => 'armada', 'name' => 'Armada'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('company_types');
    }
}

