<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToShopeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopees', function (Blueprint $table) {
            $table->string("prev_code")->nullable();
            $table->dateTime("token_updated_at")->nullable();
        });
    }
}
