<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToShopeeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_products', function (Blueprint $table) {
            $table->integer('total_cover_images')->default(-1);
            $table->integer('total_size_wise_variation_images')->default(-1);
            $table->integer('total_size_wise_options')->default(-1);
            $table->integer('total_color_wise_variation_images')->default(-1);
            $table->integer('total_color_wise_options')->default(-1);
        });
    }
}
