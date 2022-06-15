<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductXToOrderPurchaseDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_purchase_details', function (Blueprint $table) {
            $table->decimal('product_price', 11, 2)->default(0)->after('product_id');
            $table->bigInteger('exchange_rate_id')->default(0)->after('po_status');
            $table->decimal('exchange_rate_value', 11, 2)->default(0)->after('exchange_rate_id');
        });
    }
}
