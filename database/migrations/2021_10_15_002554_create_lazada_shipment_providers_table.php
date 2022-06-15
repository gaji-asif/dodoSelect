<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaShipmentProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_shipment_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('is_default');
            $table->string('name');
            $table->string('tracking_code_example')->nullable();
            $table->text('enabled_delivery_options')->nullable();
            $table->integer('cod')->nullable();
            $table->string('tracking_code_validation_regex')->nullable();
            $table->text('tracking_url')->nullable();
            $table->integer('api_integration')->nullable();
            $table->integer('website_id')->nullable();
            $table->timestamps();
        });
    }
}
