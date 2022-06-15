<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_product_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('lazada_id');
            $table->integer('category_id');
            $table->integer('parent_id')->nullable();
            $table->text('category_name');
            $table->boolean('var')->default(false);
            $table->boolean('leaf')->default(false);
            $table->timestamps();
        });
    }
}
