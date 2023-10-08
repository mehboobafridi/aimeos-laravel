<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_order_histories', function (Blueprint $table) {
            $table->id();
            $table->string('seller_email', 20);
            $table->string('seller_amz_id', 20)->nullable();
            $table->string('site_code', 5);
            $table->string('site_id', 25);
            $table->string('last_download_date', 25)->nullable();
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
        Schema::dropIfExists('seller_order_histories');
    }
}
