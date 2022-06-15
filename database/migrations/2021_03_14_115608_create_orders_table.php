<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignIdFor(User::class, 'shipper_id');
            $table->string('shop_id', 10)->index();
            $table->string('tracking_id');
            $table->string('buyer');
            $table->enum('input_method', [ 'manual', 'import' ]);
            $table->date('date');
            $table->time('time');
            $table->timestamp('updated_at')->useCurrent();
        });
    }
}
