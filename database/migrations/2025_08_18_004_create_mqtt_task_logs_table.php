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
        Schema::create('mqtt_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('station_id');
            $table->string('topic');
            $table->string('task_type'); // heartbeat, data_upload, configuration_update
            $table->string('direction'); // request, response
            $table->enum('status', ['pending', 'sent', 'received', 'acknowledged', 'failed', 'timeout'])->default('pending');
            $table->timestamp('received_at');
            $table->timestamps();
            
            $table->index(['station_id', 'created_at']);
            $table->index(['station_id', 'task_type']);
            $table->index('status');
            $table->foreign('station_id')->references('station_id')->on('station_information')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mqtt_task_logs');
    }
};