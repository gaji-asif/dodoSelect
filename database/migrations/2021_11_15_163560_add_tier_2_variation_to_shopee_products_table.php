<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTier2VariationToShopeeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_products', function (Blueprint $table) {
            $table->text('tier_2_variations')->nullable()->after('variations');
        });
    }
}
