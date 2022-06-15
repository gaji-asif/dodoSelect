<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetDataTpksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_data_tpks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sheet_name_id')->default(0);
            $table->date('date')->nullable();
            $table->float('amount')->default(0)->nullable();
            $table->string('type', 20)->nullable();
            $table->string('channel', 20)->nullable();
            $table->char('order_by', 1)->nullable();
            $table->char('shop', 2)->nullable();
            $table->float('charged_shipping_cost')->default(0)->nullable();
            $table->float('actual_shipping_cost')->default(0)->nullable();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->timestamps();
        });
    }
}
