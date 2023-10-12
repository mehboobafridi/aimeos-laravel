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
            $table->string('seller_email', 40);
            $table->string('seller_amz_id', 30)->nullable();
            $table->string('site_code', 5);
            $table->string('site_id', 30);
            // $table->timestamp('last_download_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('last_download_date')->nullable();
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
