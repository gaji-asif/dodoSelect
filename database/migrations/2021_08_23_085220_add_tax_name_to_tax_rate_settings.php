<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxNameToTaxRateSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tax_rate_settings', function (Blueprint $table) {
            $table->string('tax_name', 20)->nullable()->default('VAT')->after('seller_id');
        });
    }
}
