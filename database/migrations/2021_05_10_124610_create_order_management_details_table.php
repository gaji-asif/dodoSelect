<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderManagementDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_management_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_management_id')->nullable();
            $table->integer('product_id');
            $table->integer('shop_id');
            $table->integer('quantity');
            $table->integer('seller_id');
            $table->timestamps();
        });
    }
}
