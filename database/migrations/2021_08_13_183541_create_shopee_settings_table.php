<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->string('path');
            $table->string('redirect_url');
            $table->string('parent_id');
            $table->string('parent_key');
            $table->timestamps();
        });
    }
}
