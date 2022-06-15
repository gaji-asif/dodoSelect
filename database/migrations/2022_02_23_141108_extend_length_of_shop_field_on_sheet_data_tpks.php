<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendLengthOfShopFieldOnSheetDataTpks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sheet_data_tpks', function (Blueprint $table) {
            $table->string('shop', 10)->nullable()->change();
        });
    }
}
