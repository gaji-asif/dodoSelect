<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpMyAdmin\Table;

class CreateStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('quatity');
            $table->integer('staff_id')->nullable();
            $table->integer('seller_id')->nullable();
            $table->dateTime('date');
            $table->integer('check_in_out');
            $table->date('created_at');
            $table->date('updated_at');
        });
    }
}
