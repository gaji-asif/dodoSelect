<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->integer('default_supplier')->default(0);
            $table->decimal('cost', 11, 3)->default(0);
            $table->integer('exchange_rate_id')->default(0);
            $table->decimal('operation_cost', 11, 2)->default(0);
            $table->string('pieces_per_pack', 10)->nullable();
            $table->string('pieces_per_carton', 10)->nullable();
            $table->decimal('lowest_sell_price', 11, 2)->default(0);
            $table->text('file');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
