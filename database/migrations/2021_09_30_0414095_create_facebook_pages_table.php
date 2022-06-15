<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('fb_id');
            $table->string('page_id');
            $table->json('page_cover')->nullable();
            $table->json('page_profile')->nullable();
            $table->text('page_name')->nullable();
            $table->string('username');
            $table->text('page_access_token');
            $table->string('page_email');
            $table->text('generic_comment_reply')->nullable();
            $table->text('generic_private_reply')->nullable();
            $table->enum('autoreply_enabled', ['yes', 'no'])->default('yes');
            $table->enum('private_reply_enabled', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });
    }
}
