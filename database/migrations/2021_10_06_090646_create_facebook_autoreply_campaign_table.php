<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookAutoreplyCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_autoreply_campaign', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('page_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }
}
