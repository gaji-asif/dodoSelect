<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsManualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments_manual', function (Blueprint $table) {
            $table->increments('id');
            $table->string('amount', 100)->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_time', 50)->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->integer('is_confirmed')->default(0);
            $table->timestamps();
        });
    }
}
