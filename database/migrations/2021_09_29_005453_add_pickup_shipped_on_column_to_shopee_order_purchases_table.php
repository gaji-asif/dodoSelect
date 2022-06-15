<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickupShippedOnColumnToShopeeOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_order_purchases', function (Blueprint $table) {
            $table->text("pickup_shipped_on")->nullable();
        });
    }
}
