<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnsToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_time', 50)->nullable()->after('payment_date');
            $table->string('payment_method', 200)->nullable()->after('payment_time');
            $table->string('payment_slip', 200)->nullable()->after('payment_method');
            $table->integer('is_confirmed')->default(0)->after('payment_slip');
        });
    }
}
