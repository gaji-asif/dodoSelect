<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_products', function (Blueprint $table) {
            $table->increments('id');
            $table->text('images')->nullable(false);
            $table->integer('inventory_id')->nullable(false);
            $table->bigInteger('product_id')->default(0);
            $table->bigInteger('parent_id')->default(0);
            $table->integer('dodo_product_id')->default(0);
            $table->string('type', 250)->nullable(false);
            $table->string('product_name')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('website_id')->nullable();
            $table->string('image')->nullable();
            $table->string('product_code')->nullable();
            $table->text('variations')->nullable(false);
            $table->text('meta_data')->nullable(false);
            $table->integer('seller_id')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->integer('from_where')->default(0);
            $table->string('quantity', 10)->nullable();
            $table->string('incoming', 50)->nullable(false);
            $table->string('price')->nullable();
            $table->string('regular_price', 10)->nullable();
            $table->string('sale_price', 10)->nullable();
            $table->text('price_html')->nullable(false);
            $table->string('weight')->nullable();
            $table->string('pack')->nullable();
            $table->string('inventory_link', 10)->nullable(false);
            $table->text('specifications')->nullable();
            $table->string('status', 50)->nullable(false);
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
            $table->unique(['product_id']);
        });
    }
}
