<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shipment_id');
            $table->integer('product_id');
            $table->integer('quantity')->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
            //$table->timestamps();
        });
    }
}
