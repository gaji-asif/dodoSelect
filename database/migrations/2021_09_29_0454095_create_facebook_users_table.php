<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->json('fb_profile_photo')->nullable();
            $table->text('access_token');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('fb_id');
            $table->timestamp('deleted')->nullable()->default(null);
            $table->index('user_id');
            $table->timestamps();
        });
    }
}
