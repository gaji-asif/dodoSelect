<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->string('regional_host');
            $table->string('redirect_url');
            $table->string('app_id');
            $table->string('app_secret');
            $table->timestamps();
        });
    }
}
