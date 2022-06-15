<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooOrderPurchaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_order_purchase_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_purchase_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('seller_id')->nullable();
            $table->string('po_status', 50)->nullable(false);
            $table->timestamps();
        });
    }
}
