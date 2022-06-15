<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->index();
            $table->integer('channel')->nullable();
            $table->string('contact_name', 50)->nullable();
            $table->string('customer_name', 50)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('shipping_name', 50)->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_district', 100)->nullable();
            $table->string('shipping_sub_district', 100)->nullable();
            $table->string('shipping_province', 50)->nullable();
            $table->string('shipping_postcode', 10)->nullable();
            $table->string('shipping_methods', 100)->nullable();
            $table->string('payment_url')->nullable();
            $table->integer('payment_status');
            $table->string('order_id');
            $table->string('order_status');
            $table->integer('payment_method')->nullable();
            $table->text('encrepted_order_id')->nullable();
            $table->double('sub_total')->default(0);
            $table->double('shipping_cost')->default(0);
            $table->double('total_discount')->default(0);
            $table->double('in_total')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
