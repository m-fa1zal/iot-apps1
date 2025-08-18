<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mqtt_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('station_id');
            $table->string('topic');
            $table->enum('task_type', [
                'heartbeat', 
                'configuration_update', 
                'data_upload'
            ]);
            $table->enum('direction', ['request', 'response']);
            $table->enum('status', [
                'pending',
                'sent', 
                'received',
                'acknowledged',
                'failed',
                'timeout'
            ]);
            $table->timestamp('received_at');
            
            $table->index(['station_id', 'task_type']);
            $table->index(['status', 'received_at']);
            $table->index('topic');
            $table->foreign('station_id')->references('station_id')->on('station_information')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mqtt_task_logs');
    }
};