<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reorders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->default(0);
            $table->string('status', 100)->default('');
            $table->string('type', 100)->default('');
            $table->string('quantity', 10)->default('');
            $table->timestamps();
        });
    }
}
