<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxInfoToOrderManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_managements', function (Blueprint $table) {
            $table->decimal('tax_rate', 11, 2)->default(0)->after('sub_total');
            $table->string('tax_number', 20)->nullable()->after('shipping_postcode');
            $table->string('company_name', 50)->nullable()->after('tax_number');
            $table->text('company_address')->nullable()->after('company_name');
            $table->string('company_province', 50)->nullable()->after('company_address');
            $table->string('company_district', 50)->nullable()->after('company_province');
            $table->string('company_sub_district', 50)->nullable()->after('company_district');
            $table->string('company_postcode', 5)->nullable()->after('company_sub_district');
        });
    }
}
