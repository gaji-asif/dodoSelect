<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supplier_id');
            $table->integer('seller_id');
            $table->string('references')->nullable();
            $table->date('e_d_f')->nullable();
            $table->date('e_d_t')->nullable();
            $table->date('e_a_d_f')->nullable();
            $table->date('e_a_d_t')->nullable();
            $table->date('order_date')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 100);
            $table->integer('supply_form');
            $table->string('factory_tracking')->nullable();
            $table->string('cargo_ref')->nullable();
            $table->string('number_of_cartons')->nullable();
            $table->string('domestic_logistics')->nullable();
            $table->string('number_of_cartons1')->nullable();
            $table->string('domestic_logistics1')->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }
}
