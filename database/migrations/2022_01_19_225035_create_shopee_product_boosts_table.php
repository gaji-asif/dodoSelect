<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeProductBoostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_product_boosts', function (Blueprint $table) {
            $table->id();
            $table->integer('website_id');
            $table->bigInteger('item_id');
            $table->integer('cooldown_second')->default(0);
            $table->enum('status', ['boosting', 'queued', 'expired'])->default('queued');
            $table->enum('boosted_from', ['system', 'api'])->default('system');
            $table->timestamps();
        });
    }
}
