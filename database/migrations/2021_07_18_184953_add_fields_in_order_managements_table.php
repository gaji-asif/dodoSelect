<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsInOrderManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_managements', function (Blueprint $table) {
            $table->integer('customer_type')->after('customer_id')->default(0)->comment('0=normal, 1=dropshipper');
            $table->integer('user_id')->after('customer_type')->nullable()->comment('for dropshippers');
        });
    }
}
