<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToShopeeOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_order_purchases', function (Blueprint $table) {
            $table->dateTime('awb_printed_at')->nullable()->after('process_completion_duration');
            $table->text('awb_url')->nullable()->after('awb_printed_at');
        });
    }
}
