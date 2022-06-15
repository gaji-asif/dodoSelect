<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyXFieldToOrderManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_managements', function (Blueprint $table) {
            $table->string('company_phone_number', 20)->nullable()->after('company_name');
            $table->string('company_contact_name', 30)->nullable()->after('company_phone_number');
        });
    }
}
