<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopeeAirwayBillPdfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopee_airway_bill_pdfs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->commet('User id');
            $table->integer('website_id')->commet('Shopee id')->nullable();
            $table->integer('total_orders')->default(0)->commet('Total orders for which pdf has been generated');
            $table->text('token')->comment('A token for tracking and naming pdfs');
            $table->text('failed_ordersn')->nullable()->comment('Failed ordersn list');
            $table->text('pdf_name')->nullable()->comment('Name of the pdf file');
            $table->text('pdf_path')->nullable()->comment('Path to the pdf file');
            $table->enum('status', ['processing', 'complete'])->default('processing')->comment('Status of job of generating bulk job');
            $table->timestamps();
        });
    }
}
