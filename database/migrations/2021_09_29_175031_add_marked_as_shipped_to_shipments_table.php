<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarkedAsShippedToShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->integer('mark_as_shipped_status')->default(0);
            $table->dateTime('mark_as_shipped_date_time')->nullable();
            $table->integer('mark_as_shipped_by')->nullable();
        });
    }
}
