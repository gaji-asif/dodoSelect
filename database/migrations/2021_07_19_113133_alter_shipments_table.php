<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->integer('print_status')->default(0)->after('shipment_date');
            $table->dateTime('print_date_time')->nullable()->after('print_status');
            $table->integer('print_by')->nullable()->after('print_date_time');
            $table->dateTime('packed_date_time')->nullable()->after('pack_status');
            $table->integer('packed_by')->default(0)->after('packed_date_time');
            $table->integer('seller_id')->default(0)->after('order_id');
        });
    }
}
