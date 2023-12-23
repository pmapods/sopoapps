    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VendorMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //vendor
        Schema::create('vendor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->enum('type',['barangjasa','armada','security']);
            $table->string('name');
            $table->string('alias');
            $table->string('address');
            $table->char('city_id');
            $table->string('salesperson')->nullable();
            $table->string('phone')->nullable();
            $table->json('email')->nullable();
            $table->tinyInteger('status')->default(0);
            // 0 active
            // 1 non active
            $table->foreign('city_id')->references('id')->on('regencies');
            $table->SoftDeletes();
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
        Schema::dropIfExists('vendor');
    }
}
