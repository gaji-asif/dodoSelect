<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_product_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('shopee_id');
            $table->integer('category_id');
            $table->integer('parent_id')->nullable();
            $table->text('category_name');
            $table->integer('has_children')->default(false);
            $table->integer('max_limit');
            $table->integer('min_limit');
            $table->timestamps();
        });
    }
}
