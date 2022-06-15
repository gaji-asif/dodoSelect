<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryProductsReservedQuantityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_products_reserved_quantity_logs', function (Blueprint $table) {
            $table->id();
            $table->string("order_id");
            $table->integer("dodo_product_id")->comment("Catalog product");
            $table->integer("quantity")->comment("Quantity to be reserved");
            $table->string("shop_name")->nullable();
            $table->integer("website_id");
            $table->string("platform");
            $table->enum("status", ["processing", "processed"])->default("processing");
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
        Schema::dropIfExists('inventory_products_reserved_quantity_logs');
    }
}
