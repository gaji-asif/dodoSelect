<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxEnableToOrderManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_managements', function (Blueprint $table) {
            $table->boolean('tax_enable')->default(false)->after('shipping_postcode');
        });
    }
}
