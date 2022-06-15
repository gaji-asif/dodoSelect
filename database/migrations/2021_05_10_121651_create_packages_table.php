<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpMyAdmin\Table;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_name');
            $table->string('price');
            $table->text('details')->nullable();
            $table->string('max_limit', 100)->nullable();
            $table->integer('package_type');
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }
}
