<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWooCronReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_cron_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', [ 'order', 'product' ])->nullable(false);
            $table->integer('shop_id')->nullable(false);
            $table->integer('number_of_record_updated')->nullable(false);
            $table->string('result', 191)->nullable(false);
            $table->timestamps();
        });
    }
}
