<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('out_of_stock_reorder', 10)->default('')->after('warehouse_id');
            $table->string('low_stock_reorder', 10)->default('')->after('out_of_stock_reorder');
            $table->decimal('lowest_sell_price')->default(0)->after('cost_pc');
        });
    }
}
