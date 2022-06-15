<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropshipperAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropshipper_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->text('address')->nullable();
            $table->string('district', 191)->nullable();
            $table->string('sub_district', 191)->nullable();
            $table->string('province', 191)->nullable();
            $table->string('postcode', 191)->nullable();
            $table->timestamps();
        });
    }
}
