<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipper_id')->nullable(false);
            $table->integer('shop_id')->nullable(false);
            $table->string('tracking_id')->nullable(false);
            $table->string('buyer')->nullable(false);
            $table->enum('input_method', [ 'manual', 'import' ]);
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('phone', 191)->nullable();
            $table->timestamps();
        });
    }
}
