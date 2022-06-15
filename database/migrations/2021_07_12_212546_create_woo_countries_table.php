<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 10)->nullable(false);
            $table->string('name', 250)->nullable(false);
            $table->timestamps();
        });
    }
}
