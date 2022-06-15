<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 120)->nullable();
            $table->string('contact_phone', 15)->nullable();
            $table->integer('seller_id')->default(0);
            $table->timestamps();
        });
    }
}
