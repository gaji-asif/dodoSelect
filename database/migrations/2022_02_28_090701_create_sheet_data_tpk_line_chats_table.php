<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetDataTpkLineChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_data_tpk_line_chats', function (Blueprint $table) {
            $table->id();
            $table->char('type', 5)->default('user');
            $table->string('chat_id');
            $table->timestamps();
        });
    }
}
