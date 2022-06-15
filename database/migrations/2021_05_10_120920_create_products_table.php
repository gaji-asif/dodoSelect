<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_name')->nullable();
            $table->string('specifications', 500)->nullable();
            $table->string('pack', 200);
            $table->string('currency', 50)->nullable();
            $table->integer('cost_pc');
            $table->integer('shop_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('image')->nullable();
            $table->string('product_code')->nullable();
            $table->integer('seller_id');
            $table->integer('warehouse_id')->nullable();
            $table->integer('from_where');
            $table->string('price')->nullable();
            $table->string('weight')->nullable();
            $table->string('alert_stock', 100)->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }
}
