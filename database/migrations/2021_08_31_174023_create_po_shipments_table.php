<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_purchase_id')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->integer('seller_id')->default(0);
            $table->date('e_d_f')->nullable();
            $table->date('e_d_t')->nullable();
            $table->date('e_a_d_f')->nullable();
            $table->date('e_a_d_t')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('supply_from')->nullable();
            $table->string('factory_tracking')->nullable();
            $table->integer('shipping_type_id')->nullable();
            $table->integer('shipping_mark_id')->nullable();
            $table->integer('domestic_shipper_id')->nullable();
            $table->integer('agent_cargo_id')->nullable();
            $table->string('cargo_ref')->nullable();
            $table->integer('number_of_cartons')->nullable();
            $table->string('domestic_logistics')->nullable();
            $table->integer('number_of_cartons1')->nullable();
            $table->string('domestic_logistics1')->nullable();
            $table->date('order_date')->nullable();
            $table->date('ship_date')->nullable();
            $table->string('status', 100)->default('open');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
