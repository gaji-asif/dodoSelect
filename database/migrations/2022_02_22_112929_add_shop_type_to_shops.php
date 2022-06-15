<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopTypeToShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('shops', 'shop_type')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->json('shop_type')->after('logo')->nullable();
            });
        }
    }
}
