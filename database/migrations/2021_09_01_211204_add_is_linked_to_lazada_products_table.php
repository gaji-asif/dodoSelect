<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLinkedToLazadaProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lazada_products', function (Blueprint $table) {
            $table->integer('is_linked')->default(0)->comment('0=not linked, 1=linked to dodo product')->after('dodo_product_id');
        });
    }
}
