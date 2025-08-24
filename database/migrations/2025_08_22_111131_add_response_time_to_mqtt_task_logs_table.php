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
        Schema::table('mqtt_task_logs', function (Blueprint $table) {
            $table->integer('response_time_ms')->nullable()->after('status');
            $table->timestamp('processed_at')->nullable()->after('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mqtt_task_logs', function (Blueprint $table) {
            $table->dropColumn(['response_time_ms', 'processed_at']);
        });
    }
};
