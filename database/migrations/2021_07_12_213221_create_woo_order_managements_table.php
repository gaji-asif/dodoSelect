<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooOrderManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_order_managements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->nullable(false);
            $table->integer('channel')->nullable(false);
            $table->string('contact_name')->nullable();
            $table->text('shipping_address')->nullable(false);
            $table->timestamps();
        });
    }
}
