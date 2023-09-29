<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         

        if (Schema::hasTable('settings')) {
            // The "users" table exists...
        }else{
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->string('DevID', 70)->nullable();
                $table->string('AppID', 70)->nullable();
                $table->string('CertID', 70)->nullable();
                $table->string('RuName', 70)->nullable();
                $table->text('Scope')->nullable();
                $table->string('ServerURL', 255)->nullable();
                $table->string('FeedFilePath', 100)->nullable();
                $table->string('ResutlsPath', 100)->nullable();
                $table->string('AccountID', 30)->nullable();
    
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
        Schema::dropIfExists('settings');
    }
}