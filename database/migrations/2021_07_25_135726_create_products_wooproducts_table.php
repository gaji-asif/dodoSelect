<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsWooproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_wooproducts', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('wooproduct_id');
            $table->primary(['product_id','wooproduct_id']);
        });
    }
}
