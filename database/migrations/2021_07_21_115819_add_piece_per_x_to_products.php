<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPiecePerXToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('pieces_per_carton', 10)->nullable()->after('dropship_price');
            $table->string('pieces_per_pack', 10)->nullable()->after('pieces_per_carton');
        });
    }
}
