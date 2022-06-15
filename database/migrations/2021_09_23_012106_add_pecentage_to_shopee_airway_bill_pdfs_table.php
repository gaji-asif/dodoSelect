<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPecentageToShopeeAirwayBillPdfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopee_airway_bill_pdfs', function (Blueprint $table) {
            $table->integer('percentage')->default(0);
        });
    }
}
