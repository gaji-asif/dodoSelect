<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToOrderManagementDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_management_details', function (Blueprint $table) {
            $table->decimal('price', 11, 3)->default(0)->after('quantity');
            $table->decimal('discount_price', 11, 3)->default(0)->after('price');
        });
    }
}
