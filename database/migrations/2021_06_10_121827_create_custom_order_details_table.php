<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_order_id')->index();
            $table->foreignId('shop_id')->index();
            $table->string('product_name', 100);
            $table->text('product_description')->nullable();
            $table->double('product_price')->default(0);
            $table->integer('quantity')->default(0);
            $table->double('discount_price')->default(0);
            $table->foreignId('seller_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
