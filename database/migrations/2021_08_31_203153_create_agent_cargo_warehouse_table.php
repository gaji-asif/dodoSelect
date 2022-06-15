<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentCargoWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_cargo_warehouse', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_cargo_id')->default(0);
            $table->string('location');
            $table->text('address');
            $table->timestamps();
        });
    }
}
