<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightXToDecimalShippingCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            $table->decimal('weight_from', 11, 3)->default(0)->change();
            $table->decimal('weight_to', 11, 3)->default(0)->change();
        });
    }
}
