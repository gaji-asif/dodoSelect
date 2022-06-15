<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_discount', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->enum('status', ['upcoming','ongoing','expired']);
            $table->enum('renewable', ['yes', 'no'])->default('no');
            $table->string('start', 191);
            $table->string('end', 191);
            $table->integer('website_id');
            $table->timestamps();
        });
    }
}
