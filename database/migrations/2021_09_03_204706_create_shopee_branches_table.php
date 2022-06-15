<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_branches', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('address');
            $table->string('zipcode');
            $table->string('district')->nullable();
            $table->string('town')->nullable();
            $table->timestamps();
        });
    }
}
