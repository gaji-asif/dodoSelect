<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerShippingMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_shipping_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->default(0);
            $table->integer('shipping_cost_id')->default(0);
            $table->decimal('price', 11, 2)->default(0);
            $table->decimal('discount_price', 11, 2)->default(0);
            $table->integer('enable_status')->default(0);
            $table->integer('is_new_status')->default(0);
            $table->timestamps();
        });
    }
}
