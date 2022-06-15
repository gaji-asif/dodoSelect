<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSelectedToCustomerShippingMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_shipping_methods', function (Blueprint $table) {
            $table->boolean('is_selected')->default(false)->after('discount_price');
        });
    }
}
