<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductMainStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_main_stocks', function (Blueprint $table) {
            $table->integer('warehouse_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_main_stocks', function (Blueprint $table) {
            $table->dropColumn('warehouse_quantity');
            $table->dropColumn('reserved_quantity');
            $table->dropColumn('available_quantity');
        });
    }
}
