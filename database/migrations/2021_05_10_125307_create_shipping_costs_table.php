<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipper_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('weight_from')->nullable();
            $table->string('weight_to')->nullable();
            $table->string('price')->nullable();
            $table->integer('seller_id')->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }
}
