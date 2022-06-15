<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->string('shop_id', 15)->default('');
            $table->string('ordersn', 20)->default('');
            $table->string('buyer_user_name', 100)->nullable();
            $table->text('returnsn_list')->nullable();
            $table->text('refund_id_list')->nullable();
            $table->float('escrow_amount', 8, 2)->default(0);
            $table->float('buyer_total_amount', 8, 2)->default(0);
            $table->float('original_price', 8, 2)->default(0);
            $table->float('seller_discount', 8, 2)->default(0);
            $table->float('shopee_discount', 8, 2)->default(0);
            $table->float('voucher_from_seller', 8, 2)->default(0);
            $table->float('voucher_from_shopee', 8, 2)->default(0);
            $table->float('coins', 8, 2)->default(0);
            $table->float('buyer_paid_shipping_fee', 8, 2)->default(0);
            $table->float('buyer_transaction_fee', 8, 2)->default(0);
            $table->float('cross_border_tax', 8, 2)->default(0);
            $table->float('payment_promotion', 8, 2)->default(0);
            $table->float('commission_fee', 8, 2)->default(0);
            $table->float('service_fee', 8, 2)->default(0);
            $table->float('seller_transaction_fee', 8, 2)->default(0);
            $table->float('seller_lost_compensation', 8, 2)->default(0);
            $table->float('seller_coin_cash_back', 8, 2)->default(0);
            $table->float('escrow_tax', 8, 2)->default(0);
            $table->float('final_shipping_fee', 8, 2)->default(0);
            $table->float('actual_shipping_fee', 8, 2)->default(0);
            $table->float('shopee_shipping_rebate', 8, 2)->default(0);
            $table->float('shipping_fee_discount_from_3pl', 8, 2)->default(0);
            $table->float('seller_shipping_discount', 8, 2)->default(0);
            $table->float('estimated_shipping_fee', 8, 2)->default(0);
            $table->text('seller_voucher_code')->nullable();
            $table->float('drc_adjustable_refund', 8, 2)->default(0);
            $table->float('escrow_amount_aff', 8, 2)->default(0);
            $table->float('exchange_rate', 8, 2)->default(0);
            $table->float('local_currency', 8, 2)->default(0);
            $table->float('escrow_currency', 8, 2)->default(0);
            $table->float('reverse_shipping_fee', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['seller_id', 'shop_id', 'ordersn']);
        });
    }
}
