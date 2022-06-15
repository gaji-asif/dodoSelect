<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopeeShippingMethodToShopeeOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_order_purchases', function (Blueprint $table) {
            $table->enum('shopee_shipping_method', ['pickup', 'dropoff'])->nullable();
            $table->json('shopee_shipping_method_params')->nullable();
        });
    }
}
