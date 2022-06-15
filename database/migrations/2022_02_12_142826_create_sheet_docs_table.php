<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_docs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 50);
            $table->char('spreadsheet_id', 44);
            $table->unsignedBigInteger('seller_id')->default(0);
            $table->timestamps();
        });
    }
}
