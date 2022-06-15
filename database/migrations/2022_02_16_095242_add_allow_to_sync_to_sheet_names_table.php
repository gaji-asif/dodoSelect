<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowToSyncToSheetNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sheet_names', function (Blueprint $table) {
            $table->boolean('allow_to_sync')->after('sheet_name')->default(false);
        });
    }
}
