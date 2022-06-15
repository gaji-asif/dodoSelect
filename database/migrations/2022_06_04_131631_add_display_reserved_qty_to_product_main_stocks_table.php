<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayReservedQtyToProductMainStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_main_stocks', function (Blueprint $table) {
            $table->integer('display_reserved_qty')->default(0)->comment("This reserved quantity is for displaying only");
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
            $table->dropColumn('display_reserved_qty');
        });
    }
}
