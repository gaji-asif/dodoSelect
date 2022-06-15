<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockProductDeffectImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_product_deffect_images', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_id');
            $table->integer('product_id');
            $table->string('image');
            $table->integer('staff_id');
            $table->dateTime('date');
            $table->timestamps();
        });
    }
}
