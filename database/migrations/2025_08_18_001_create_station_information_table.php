<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_information', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->string('station_id')->unique();
            $table->foreignId('state_id')->constrained()->onDelete('cascade');
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->text('address')->nullable();
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->boolean('station_active')->default(true);
            $table->timestamps();
            
            $table->index(['state_id', 'district_id']);
            $table->index('station_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_information');
    }
};