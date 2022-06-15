<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoShipmentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_shipment_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('po_shipment_id')->default(0);
            $table->integer('product_id')->default(0);
            $table->integer('ship_quantity')->default(0);
            $table->integer('order_purchase_id')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->integer('seller_id')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
