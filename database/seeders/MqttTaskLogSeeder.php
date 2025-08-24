<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MqttTaskLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = DB::table('station_information')->pluck('station_id');
        
        if ($stations->isEmpty()) {
            $this->command->warn('No stations found. Please run StationInformationSeeder first.');
            return;
        }

        $taskTypes = ['heartbeat', 'data_upload', 'configuration_update'];
        $directions = ['request', 'response'];
        $statuses = ['SEND', 'RECEIVE', 'acknowledged', 'failed'];

        $logs = [];
        $now = Carbon::now();

        // Generate 7 days of MQTT task logs
        for ($day = 7; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day);
            
            foreach ($stations as $stationId) {
                // Generate random number of logs per day per station (10-30)
                $logsPerDay = rand(10, 30);
                
                for ($i = 0; $i < $logsPerDay; $i++) {
                    $taskType = $taskTypes[array_rand($taskTypes)];
                    $direction = $directions[array_rand($directions)];
                    $status = $statuses[array_rand($statuses)];
                    
                    $logTime = $date->copy()->addMinutes(rand(0, 1439)); // Random time during the day
                    
                    $logs[] = [
                        'station_id' => $stationId,
                        'topic' => "iot/{$stationId}/{$taskType}/{$direction}",
                        'task_type' => $taskType,
                        'direction' => $direction,
                        'status' => $status,
                        'response_time_ms' => $direction === 'response' ? rand(50, 500) : null,
                        'received_at' => $logTime,
                        'processed_at' => $direction === 'response' ? $logTime->copy()->addMilliseconds(rand(50, 500)) : null,
                        'created_at' => $logTime,
                        'updated_at' => $logTime,
                    ];
                }
            }
        }

        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($logs, 500);
        foreach ($chunks as $chunk) {
            DB::table('mqtt_task_logs')->insert($chunk);
        }

        $this->command->info("Created " . count($logs) . " MQTT task log entries");
    }
}