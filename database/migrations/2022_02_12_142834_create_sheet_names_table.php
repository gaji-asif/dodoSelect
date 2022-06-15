<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_names', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sheet_doc_id')->default(0);
            $table->string('sheet_name', 50);
            $table->dateTime('last_sync')->nullable();
            $table->boolean('sync_status')->default(0);
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->timestamps();
        });
    }
}
