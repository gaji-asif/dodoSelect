<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeDefectColumnsToStockLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->integer('is_defect')->default(0)->nullable()->after('check_in_out');
            $table->text('deffect_note')->nullable()->after('is_defect');
            $table->string('deffect_status', 50)->nullable()->after('deffect_note');
        });
    }
}
