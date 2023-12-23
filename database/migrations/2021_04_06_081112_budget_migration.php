<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BudgetMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_pricing_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('budget_pricing', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_pricing_category_id')->unsigned();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('uom');
            // harga dalam jawa
            $table->double('injs_min_price')->nullable();
            $table->double('injs_max_price')->nullable();
            // harga luar jawa
            $table->double('outjs_min_price')->nullable();
            $table->double('outjs_max_price')->nullable();
            $table->boolean('isAsset');
            $table->boolean('isIT')->default(false);
            $table->string('IT_alias')->nullable();
            $table->foreign('budget_pricing_category_id')->references('id')->on('budget_pricing_category');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('budget_brand', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_pricing_id')->unsigned();
            $table->foreign('budget_pricing_id')->references('id')->on('budget_pricing');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('budget_type', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_pricing_id')->unsigned();
            $table->foreign('budget_pricing_id')->references('id')->on('budget_pricing');
            $table->string('name');
            $table->timestamps();
        });

        //  Armada Maintenance budget
       Schema::create('maintenance_budget_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('maintenance_budget', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('maintenance_budget_category_id')->unsigned();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('uom');
            $table->boolean('isIT')->default(false);
            $table->string('IT_alias')->nullable();
            $table->foreign('maintenance_budget_category_id')->references('id')->on('maintenance_budget_category');
            $table->SoftDeletes();
            $table->timestamps();
        });

         //  HO budget
       Schema::create('ho_budget_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->SoftDeletes();
            $table->timestamps();
        });

        Schema::create('ho_budget', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ho_budget_category_id')->unsigned();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('isIT')->default(false);
            $table->string('IT_alias')->nullable();
            $table->foreign('ho_budget_category_id')->references('id')->on('ho_budget_category');
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
        //
    }
}
