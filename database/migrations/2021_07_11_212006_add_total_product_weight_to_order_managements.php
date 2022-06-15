<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalProductWeightToOrderManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_managements', function (Blueprint $table) {
            $table->integer('total_product_weight')->default(0)->after('in_total');
        });
    }
}
