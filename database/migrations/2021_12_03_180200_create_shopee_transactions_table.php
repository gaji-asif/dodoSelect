<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->string('shop_id', 15)->default('');
            $table->unsignedInteger('transaction_id')->nullable();
            $table->string('status', 15)->nullable();
            $table->string('wallet_type', 50)->nullable();
            $table->string('transaction_type', 50)->nullable();
            $table->float('amount', 8, 2)->default(0)->nullable();
            $table->float('current_balance', 8, 2)->default(0)->nullable();
            $table->unsignedInteger('create_time')->nullable();
            $table->string('ordersn', 20)->default('');
            $table->string('refund_sn')->nullable();
            $table->string('withdrawal_type')->nullable();
            $table->float('transaction_fee', 8, 2)->default(0)->nullable();
            $table->text('description')->nullable();
            $table->string('buyer_name', 100)->nullable();
            $table->json('pay_order_list')->nullable();
            $table->float('withdraw_id')->nullable();
            $table->string('reason')->nullable();
            $table->float('root_withdrawal_id')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'shop_id', 'ordersn']);
        });
    }
}
