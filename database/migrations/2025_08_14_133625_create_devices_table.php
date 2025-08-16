<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->string('station_id')->unique();
            $table->string('api_token', 64)->unique();
            $table->string('mac_address')->nullable();
            $table->integer('data_interval_minutes')->default(2);
            $table->integer('data_collection_time_minutes')->default(30);
            $table->foreignId('state_id')->constrained()->onDelete('cascade');
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->text('address')->nullable();
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->boolean('station_active')->default(true);
            $table->boolean('request_update')->default(false);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
            
            $table->index(['state_id', 'district_id']);
            $table->index('last_seen');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
