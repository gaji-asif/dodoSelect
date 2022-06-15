<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInShopeeOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_order_purchases', function (Blueprint $table) {
            $table->string('status_custom', 20)->nullable()->after('status');
            $table->dateTime('process_start_date')->nullable()->after('order_date');
            $table->dateTime('process_complete_date')->nullable()->after('process_start_date');
            $table->string('process_completion_duration', 60)->nullable()->after('process_complete_date');
            $table->string('package_number', 30)->nullable()->after('tracking_number');
        });
    }
}
