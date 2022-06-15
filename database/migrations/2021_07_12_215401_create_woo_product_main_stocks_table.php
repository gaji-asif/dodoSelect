<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooProductMainStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_product_main_stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->nullable(false);
            $table->integer('quantity')->nullable(false);
            $table->timestamps();
        });
    }
}
