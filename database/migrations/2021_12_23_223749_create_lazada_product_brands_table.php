<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaProductBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_product_brands', function (Blueprint $table) {
            $table->id();
            $table->integer('lazada_id');
            $table->integer('brand_id');
            $table->string('global_identifier');
            $table->string('name_en');
            $table->string('name');
            $table->timestamps();
        });
    }
}
