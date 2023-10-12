<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sellers')) {
            // The "users" table exists...
        }else{

            Schema::create('sellers', function (Blueprint $table) {

                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
    
                $table->bigIncrements('id');
                $table->string('name',40)->nullable();
                $table->mediumText('refresh_token')->nullable();
                $table->mediumText('access_token')->nullable();
                $table->mediumText('restricted_token')->nullable();
                $table->string('seller_id',100)->nullable();
                $table->string('mws_auth_token',255)->nullable();
                $table->timestamp('token_updated_at')->nullable();
                $table->string('account_id',50)->nullable();
                $table->boolean('is_active')->default('1');
                $table->timestamps();
            });
        }

       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sellers');
    }
}
