<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeOrderParamInitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_order_param_inits', function (Blueprint $table) {
            $table->id();
            $table->string('ordersn');
            $table->text('pickup');
            $table->text('dropoff');
            $table->string('package_number')->nullable();
            $table->timestamps();
        });
    }
}
