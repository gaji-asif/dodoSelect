<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnsToOrderPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_purchases', function (Blueprint $table) {
            $table->integer('shipping_type_id')->nullable()->after('factory_tracking');
            $table->integer('shipping_mark_id')->nullable()->after('shipping_type_id');
            $table->integer('domestic_shipper_id')->nullable()->after('shipping_mark_id');
        });
    }
}
