<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('station_id');
            $table->string('api_token', 64)->unique();
            $table->string('mac_address')->nullable();
            $table->integer('data_interval')->default(2);
            $table->integer('data_collection_time')->default(30);
            $table->boolean('configuration_update')->default(false);
            $table->timestamps();
            
            $table->index('station_id');
            $table->foreign('station_id')->references('station_id')->on('station_information')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_configurations');
    }
};