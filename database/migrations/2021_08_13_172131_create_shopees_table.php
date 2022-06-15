<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopees', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name');
            $table->string('code');
            $table->string('shop_id');
            $table->integer('seller_id');
            $table->timestamps();
        });
    }
}
