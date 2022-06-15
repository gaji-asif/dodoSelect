<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryProductsStockUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_products_stock_update_log', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('custom_status');
            $table->integer('platform');
            $table->integer('platform_sid')->comment('Platform shop_id/website_id');
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
        Schema::dropIfExists('inventory_products_stock_update_log');
    }
}
