<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentCargoMarkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_cargo_mark', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_cargo_id')->default(0);
            $table->string('shipping_mark');
            $table->integer('ship_type_id')->default(0);
            $table->timestamps();
        });
    }
}
