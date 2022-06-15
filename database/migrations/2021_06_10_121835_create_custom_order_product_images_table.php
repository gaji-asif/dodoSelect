<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomOrderProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_order_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_order_detail_id');
            $table->string('image');
            $table->timestamps();
        });
    }
}
