<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_order_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('website_id');
            $table->string('order_id');
            $table->string('order_number')->nullable()->comment('Assigned by seller center');
            $table->date('order_date')->nullable();
            $table->string('package_id')->nullable();
            $table->integer('seller_id');
            $table->string('tracking_number')->nullable()->comment('Assigned during setting "ready_to_ship"');
            $table->text('statuses', 100)->nullable()->comment('Unique status of the items in the order');
            $table->string('status_custom', 20)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('items_count')->default(0);
            $table->text('order_item_ids')->nullable();
            $table->string('branch_number')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_method_title', 50)->nullable();
            $table->string('customer_first_name');
            $table->string('customer_last_name')->nullable();
            $table->string('national_registration_number', 50)->nullable();
            $table->text('billing')->nullable();
            $table->text('shipping')->nullable();
            $table->string('delivery_type')->default('dropship');
            $table->text('remarks')->nullable();
            $table->text('awb_document')->nullable();
            $table->string('address_updated_at')->nullable();
            $table->string('voucher_code', 50)->nullable();
            $table->string('voucher', 50)->nullable();
            $table->string('voucher_platform', 50)->nullable();
            $table->string('voucher_seller', 50)->nullable();
            $table->string('promised_shipping_times', 50)->nullable();
            $table->string('shipping_fee')->nullable();
            $table->string('shipping_fee_original')->nullable();
            $table->string('shipping_fee_discount_seller')->nullable();
            $table->string('shipping_fee_discount_platform')->nullable();
            $table->text('delivery_info')->nullable();
            $table->string('warehouse_code', 50)->nullable();
            $table->text('extra_attributes')->nullable();
            $table->boolean('gift_option')->default(false);
            $table->string('gift_message')->nullable();
            $table->dateTime('package_created_at')->nullable();
            $table->dateTime('awb_printed_at')->nullable();
            $table->dateTime('downloaded_at')->nullable();
            $table->dateTime('shipped_on_date')->nullable();
            $table->dateTime('mark_as_shipped_at')->nullable();
            $table->dateTime('pickup_confirmed_at')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->dateTime('process_start_date')->nullable();
            $table->dateTime('process_complete_date')->nullable();
            $table->string('process_completion_duration', 60)->nullable();
            $table->timestamps();
        });
    }
}
