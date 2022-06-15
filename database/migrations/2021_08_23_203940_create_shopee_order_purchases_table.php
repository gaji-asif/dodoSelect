<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_order_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('website_id')->nullable(false);
            $table->string('order_id', 10)->nullable(false);
            $table->integer('product_id')->nullable(false);
            $table->integer('supplier_id')->nullable();
            $table->integer('seller_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('reference')->nullable(false);
            $table->text('billing')->nullable(false);
            $table->text('shipping')->nullable(false);
            $table->text('line_items')->nullable(false);
            $table->text('shipping_lines')->nullable(false);
            $table->string('label_printed')->nullable(false);
            $table->string('payment_method', 10)->nullable(false);
            $table->string('payment_method_title', 50)->nullable(false);
            $table->decimal('total', 10, 2)->nullable(false);
            $table->string('currency_symbol', 10)->nullable(false);
            $table->date('e_d_f')->nullable();
            $table->date('e_d_t')->nullable();
            $table->date('e_a_d_f')->nullable();
            $table->date('e_a_d_t')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 100)->default('open');
            $table->integer('quantity')->nullable(false);
            $table->integer('supply_from')->nullable(false);
            $table->string('factory_tracking')->nullable();
            $table->string('cargo_ref')->nullable();
            $table->integer('number_of_cartons')->nullable();
            $table->string('domestic_logistics')->nullable();
            $table->integer('number_of_cartons1')->nullable();
            $table->string('domestic_logistics1')->nullable();
            $table->date('order_date')->nullable();
            $table->string('order_created_block')->nullable();
            $table->timestamps();
        });
    }
}
