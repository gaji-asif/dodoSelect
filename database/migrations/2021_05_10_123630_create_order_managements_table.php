<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_managements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->integer('channel');
            $table->string('contact_name')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_name', 100)->nullable();
            $table->string('shipping_phone', 100)->nullable();
            $table->string('shipping_district', 100)->nullable();
            $table->string('shipping_sub_district', 100)->nullable();
            $table->string('shipping_province', 100)->nullable();
            $table->string('shipping_postcode', 100)->nullable();
            $table->string('shipping_methods', 100)->nullable();
            $table->string('payment_url')->nullable();
            $table->string('order_id');
            $table->integer('order_status');
            $table->integer('payment_method')->nullable();
            $table->text('encrepted_order_id')->nullable();
            $table->string('sub_total', 50)->nullable();
            $table->string('shipping_cost', 50)->nullable();
            $table->string('in_total', 50);
            $table->timestamps();
        });
    }
}
