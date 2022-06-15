<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_rate_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('tax_rate', 11, 2)->default(0);
            $table->string('tax_number', 20)->nullable();
            $table->string('company_name', 50)->nullable();
            $table->string('company_phone', 20)->nullable();
            $table->string('company_contact_person', 30)->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_province', 50)->nullable();
            $table->string('company_district', 50)->nullable();
            $table->string('company_sub_district', 50)->nullable();
            $table->string('company_postcode', 5)->nullable();
            $table->timestamps();
        });
    }
}
