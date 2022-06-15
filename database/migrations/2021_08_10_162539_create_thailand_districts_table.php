<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThailandDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thailand_districts', function (Blueprint $table) {
            $table->increments('id');
            $table->char('province_code', 4);
            $table->char('code', 6)->unique();
            $table->string('name_en', 50);
            $table->string('name_th', 50);
            $table->timestamps();
        });
    }
}
