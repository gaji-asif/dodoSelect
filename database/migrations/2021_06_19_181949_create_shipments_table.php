<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shipment_status', 100)->nullable();
            $table->date('shipment_date')->nullable();
            $table->string('pack_status', 100)->nullable();
            $table->integer('order_id')->default(0);
            $table->timestamps();
        });
    }
}
