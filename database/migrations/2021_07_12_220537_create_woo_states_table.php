<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 30)->nullable(false);
            $table->string('name', 250)->nullable(false);
            $table->timestamps();
        });
    }
}
