<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopeeCategoryIdToShopeeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_products', function (Blueprint $table) {
            $table->integer('shopee_category_id')->after('category_id')->default(0);
        });
    }
}
