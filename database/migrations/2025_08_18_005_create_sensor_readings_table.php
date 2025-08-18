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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->string('station_id');
            $table->decimal('temperature', 5, 2)->nullable()->comment('Temperature in Celsius from DHT11');
            $table->decimal('humidity', 5, 2)->nullable()->comment('Humidity percentage from DHT11');
            $table->integer('rssi')->nullable()->comment('RSSI value for LoRa signal strength in dBm');
            $table->decimal('battery_voltage', 4, 2)->nullable()->comment('Battery voltage in volts');
            $table->timestamp('reading_time')->comment('Time when ESP32 captured the reading');
            $table->boolean('web_triggered')->default(false)->comment('Manual vs scheduled reading');
            $table->timestamps();
            
            $table->index(['station_id', 'created_at']);
            $table->index(['station_id', 'reading_time']);
            $table->foreign('station_id')->references('station_id')->on('station_information')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
