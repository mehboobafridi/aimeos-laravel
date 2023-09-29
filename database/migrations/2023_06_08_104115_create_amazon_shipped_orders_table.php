<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmazonShippedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amazon_shipped_orders', function (Blueprint $table) {
            $table->id();
            $table->string('amazon_order_id', 30)->nullable();
            $table->string('purchase_date', 30)->nullable();
            $table->string('OrderStatus', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amazon_shipped_orders');
    }
}
