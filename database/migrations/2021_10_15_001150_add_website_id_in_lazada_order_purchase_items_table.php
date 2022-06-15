<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebsiteIdInLazadaOrderPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lazada_order_purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('lazada_order_purchase_items', 'website_id')) {
                $table->integer('website_id')->after('order_item_id')->nullable();
            }
        });
    }
}
