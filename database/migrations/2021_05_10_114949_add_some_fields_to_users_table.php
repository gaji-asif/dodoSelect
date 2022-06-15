<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('shop_id', 4)->after('id')->nullable();
            $table->string('username')->nullable();
            $table->string('contactname')->after('username')->nullable();
            $table->string('phone')->after('contactname')->nullable();
            $table->string('lineid')->after('phone')->nullable();
            $table->enum('role', [ 'member', 'admin', 'staff' ]);
            $table->string('otp', 100)->after('is_active');
            $table->string('logo')->after('otp')->nullable();
            $table->integer('max_limit')->default(0);
            $table->date('package_start_date')->nullable();
            $table->date('package_end_date')->nullable();
            $table->integer('seller_id')->nullable();
            $table->string('address')->nullable();
        });
    }
}
