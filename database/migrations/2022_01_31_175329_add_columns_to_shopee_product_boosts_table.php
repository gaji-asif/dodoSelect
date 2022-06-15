<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToShopeeProductBoostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_product_boosts', function (Blueprint $table) {
            if (!Schema::hasColumn($table->getTable(), "boost_expires_at")) {
                $table->dateTime('boost_expires_at')->nullable()->after("boosted_from");
            }
            if (!Schema::hasColumn($table->getTable(), "repeat_boost")) {
                $table->Boolean('repeat_boost')->default(True)->after("boost_expires_at");
            }
        });
    }
}
