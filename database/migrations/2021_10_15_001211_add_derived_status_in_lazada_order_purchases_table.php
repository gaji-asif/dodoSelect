<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDerivedStatusInLazadaOrderPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lazada_order_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('lazada_order_purchases', 'derived_status')) {
                $table->string('derived_status')->after('statuses')->nullable();
            }
        });
    }
}
