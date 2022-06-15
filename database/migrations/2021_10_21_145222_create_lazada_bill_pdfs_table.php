<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLazadaBillPdfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lazada_bill_pdfs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->commet('User id');
            $table->integer('website_id')->commet('Lazada Shop id')->nullable();
            $table->integer('total_orders')->default(0)->commet('Total orders for which pdf has been generated');
            $table->integer('total_items')->default(0)->commet('Total orders for which pdf has been generated');
            $table->text('token')->comment('A token for tracking and naming pdfs');
            $table->text('failed_ordersn')->nullable()->comment('Failed order id list');
            $table->text('failed_items')->nullable()->comment('Failed ordersn list');
            $table->enum('doc_type',['invoice','shippingLabel','carrierManifest'])->default('shippingLabel')->comment('Pdf type');
            $table->text('pdf_name')->nullable()->comment('Name of the pdf file');
            $table->text('pdf_path')->nullable()->comment('Path to the pdf file');
            $table->boolean('is_parent')->default(false);
            $table->enum('status', ['processing', 'complete'])->default('processing')->comment('Status of job of generating bulk job');
            $table->timestamps();
        });
    }
}
