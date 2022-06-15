<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnsInLazadaOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lazada_order_purchases', function (Blueprint $table) {
            $table->text('customer_first_name')->change();
            $table->text('customer_last_name')->change();
            $table->text('tax_code')->change();
            $table->text('payment_method')->change();
            $table->text('payment_method_title')->change();
            $table->text('national_registration_number')->change();
            $table->text('billing')->change();
            $table->text('shipping')->change();
            $table->text('remarks')->change();
            $table->text('voucher')->change();
            $table->text('voucher_code')->change();
            $table->text('voucher_seller')->change();
            $table->text('voucher_platform')->change();
            $table->text('gift_message')->change();
            $table->text('delivery_info')->change();
            $table->text('branch_number')->change();
        });
    }
}
