<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_histories', function (Blueprint $table) {
            $table->id();
            $table->string('seller_email', 20);
            $table->string('seller_amz_id', 20);
            $table->string('report_id', 30);
            $table->boolean('is_downloaded')->default('0');
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
        Schema::dropIfExists('reports_histories');
    }
}
