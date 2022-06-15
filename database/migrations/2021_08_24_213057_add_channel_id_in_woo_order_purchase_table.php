<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelIdInWooOrderPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('woo_order_purchases', function (Blueprint $table) {
            $table->integer('channel_id')->nullable()->after('product_id');
        });
    }
}
