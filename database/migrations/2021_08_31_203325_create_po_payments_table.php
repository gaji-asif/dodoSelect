<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_purchase_id')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->decimal('amount', 11, 3)->default(0);
            $table->decimal('paid', 11, 3)->default(0);
            $table->integer('exchange_rate_id')->default(0);
            $table->string('payment_status', 11);
            $table->string('bank_account')->nullable();
            $table->string('notes')->nullable();
            $table->string('file_invoice')->nullable();
            $table->string('file_payment')->nullable();
            $table->integer('user_id')->default(0);
            $table->timestamps();
        });
    }
}
