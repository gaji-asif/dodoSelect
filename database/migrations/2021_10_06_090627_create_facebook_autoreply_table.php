<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookAutoreplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_autoreply', function (Blueprint $table) {
            $table->id();
            $table->string('page_id');
            $table->integer('user_id');
            $table->text('filter_words');
            $table->text('comment_body');
            $table->text('private_message');
            $table->timestamps();
        });
    }
}
