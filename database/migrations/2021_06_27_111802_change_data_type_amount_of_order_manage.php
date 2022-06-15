<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataTypeAmountOfOrderManage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_managements', function (Blueprint $table) {
            $table->decimal('sub_total', 11, 2)->default(0)->change();
            $table->decimal('shipping_cost', 11, 2)->default(0)->change();
            $table->decimal('in_total', 11, 2)->default(0)->change();
        });
    }
}
