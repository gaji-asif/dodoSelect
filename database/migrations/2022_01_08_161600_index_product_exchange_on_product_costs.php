<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexProductExchangeOnProductCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_costs', function (Blueprint $table) {
            $table->index([ 'product_id', 'default_supplier' ]);
            $table->index([ 'exchange_rate_id' ]);
        });
    }
}
