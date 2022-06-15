<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooOrderManagementDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_order_management_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_management_id')->nullable(false);
            $table->integer('product_id')->nullable(false);
            $table->integer('quantity')->nullable(false);
            $table->integer('seller_id')->nullable(false);
            $table->timestamps();
        });
    }
}
