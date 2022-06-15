<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_shops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable(false);
            $table->string('site_url', 200)->nullable(false);
            $table->string('rest_api_key', 100)->nullable(false);
            $table->string('rest_api_secrete', 100)->nullable(false);
            $table->integer('seller_id')->default(0);
            $table->timestamps();
        });
    }
}
