<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooProductPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_product_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id')->nullable(false);
            $table->string('price')->nullable(false);
            $table->integer('seller_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->timestamps();
        });
    }
}
