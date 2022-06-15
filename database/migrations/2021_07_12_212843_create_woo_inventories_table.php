<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->nullable(false);
            $table->integer('inventory_id')->nullable(false)->unique();
            $table->string('inventory_name')->nullable(false);
            $table->string('inventory_code')->nullable(false);
            $table->integer('website_id')->nullable(false);
            $table->integer('product_id')->nullable(false);
            $table->string('product_code')->nullable(false);
            $table->integer('quantity')->nullable(false);
            $table->timestamps();
        });
    }
}
