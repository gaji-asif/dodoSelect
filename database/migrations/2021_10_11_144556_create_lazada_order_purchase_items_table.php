<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaOrderPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_order_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('order_item_id');
            $table->string('package_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('name');
            $table->string('shop_id')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('item_price', 10, 2)->default(0);
            $table->decimal('paid_price', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_fee_original', 10, 2)->default(0);
            $table->string('variation')->nullable();
            $table->string('currency')->nullable();
            $table->string('order_flag')->nullable();
            $table->string('shop_sku')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('status')->nullable();
            $table->string('tracking_code_pre')->nullable();
            $table->integer('is_digital')->nullable();
            $table->string('cancel_return_initiator')->nullable();
            $table->string('purchase_order_id')->nullable();
            $table->string('voucher_platform')->nullable();
            $table->string('voucher_seller')->nullable();
            $table->string('order_type')->nullable();
            $table->string('stage_pay_status')->nullable();
            $table->string('warehouse_code')->nullable();
            $table->string('voucher_seller_lpi')->nullable();
            $table->string('voucher_platform_lpi')->nullable();
            $table->string('buyer_id')->nullable();
            $table->string('voucher_code')->nullable();
            $table->string('voucher_code_seller')->nullable();
            $table->string('voucher_code_platform')->nullable();
            $table->string('delivery_option_sof')->nullable();
            $table->string('is_fbl')->nullable();
            $table->string('is_reroute')->nullable();
            $table->text('reason')->nullable();
            $table->string('digital_delivery_info')->nullable();
            $table->string('return_status')->nullable();
            $table->string('shipping_type')->nullable();
            $table->string('shipment_provider')->nullable();
            $table->string('shipping_provider_type')->nullable();
            $table->decimal('shipping_fee_discount_seller', 10, 2)->default(0);
            $table->decimal('shipping_fee_discount_platform', 10, 2)->default(0);
            $table->decimal('voucher_amount', 10, 2)->default(0);
            $table->decimal('wallet_credits', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->integer('shipping_service_cost')->nullable();
            $table->dateTime('promised_shipping_time')->nullable();
            $table->dateTime('sla_time_stamp')->nullable();
            $table->text('product_main_image')->nullable();
            $table->text('product_detail_url')->nullable();
            $table->text('reason_detail')->nullable();
            $table->text('extra_attributes')->nullable();
            $table->dateTime('order_created_at')->nullable();
            $table->dateTime('order_updated_at')->nullable();
            $table->timestamps();
        });
    }
}
