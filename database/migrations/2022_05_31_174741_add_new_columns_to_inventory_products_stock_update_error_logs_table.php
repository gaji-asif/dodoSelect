<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToInventoryProductsStockUpdateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_products_stock_update_error_logs', function (Blueprint $table) {
            $table->string("product_name")->after('id')->nullable();
            $table->string("product_code")->after('product_name')->nullable();
            $table->string("platform_name")->after('platform')->nullable();
            $table->string("shop_name")->after('platform_sid')->nullable();
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
            $table->dropColumn("product_name");
            $table->dropColumn("product_code");
            $table->dropColumn("platform_name");
            $table->dropColumn("shop_name");
        });
    }
}
