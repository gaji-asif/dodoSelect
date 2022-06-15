<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThailandSubDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thailand_sub_districts', function (Blueprint $table) {
            $table->increments('id');
            $table->char('district_code', 6);
            $table->char('code', 8)->unique();
            $table->string('name_en');
            $table->string('name_th');
            $table->timestamps();
        });
    }
}
