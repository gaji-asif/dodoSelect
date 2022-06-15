<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThailandPostCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thailand_post_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->char('sub_district_code', 8);
            $table->string('post_code', 5);
            $table->timestamps();
        });
    }
}
