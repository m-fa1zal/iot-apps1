<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_status', function (Blueprint $table) {
            $table->string('station_id')->primary();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->boolean('request_update')->default(false);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('last_seen');
            $table->foreign('station_id')->references('station_id')->on('station_information')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_status');
    }
};