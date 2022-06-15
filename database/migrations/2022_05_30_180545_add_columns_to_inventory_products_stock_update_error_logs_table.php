<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToInventoryProductsStockUpdateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_products_stock_update_error_logs', function (Blueprint $table) {
            $table->integer("product_id")->after('dodo_product_id')->default(0);
            $table->integer("variation_id")->after('product_id')->nullable();
            $table->string("type")->after('variation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_products_stock_update_error_logs', function (Blueprint $table) {
            $table->dropColumn("product_id");
            $table->dropColumn("variation_id");
            $table->dropColumn("type");
        });
    }
}
