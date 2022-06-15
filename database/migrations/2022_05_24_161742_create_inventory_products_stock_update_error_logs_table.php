<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryProductsStockUpdateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_products_stock_update_error_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('dodo_product_id')->comment('Refers to "id" is "products" table');
            $table->integer('quantity');
            $table->integer('platform');
            $table->integer('platform_sid')->nullable()->comment('Platform shop_id/website_id');
            $table->text('message');
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
        Schema::dropIfExists('inventory_products_stock_update_error_logs');
    }
}
