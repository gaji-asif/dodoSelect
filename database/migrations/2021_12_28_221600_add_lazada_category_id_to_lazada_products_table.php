<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLazadaCategoryIdToLazadaProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lazada_products', function (Blueprint $table) {
            $table->integer('lazada_category_id')->after('category_id')->default(0);
        });
    }
}
