<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductHasTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_has_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('product_tag_id');
            $table->timestamps();
        });
    }
}
