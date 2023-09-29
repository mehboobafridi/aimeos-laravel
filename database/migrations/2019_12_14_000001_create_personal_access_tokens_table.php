<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       

        
        if (Schema::hasTable('personal_access_tokens')) {
            // The "users" table exists...
        }else{
            Schema::create('personal_access_tokens', function (Blueprint $table) {

                $table->timestamps();

                $table->id();
                $table->morphs('tokenable',255);
                $table->string('name')->nullable();
                $table->string('token', 64)->unique();
                $table->text('abilities');
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
        Schema::dropIfExists('personal_access_tokens');
    }
}
