# MQTT Test Script for ESP32 IoT Communication
# Requires mosquitto_pub and mosquitto_sub tools
# Download from: https://mosquitto.org/download/

param(
    [string]$Action = "help",
    [string]$Broker = "railway",
    [string]$StationId = "JHR03004"
)

# MQTT Broker Configurations
$LocalBroker = @{
    Host = "10.70.33.40"
    Port = 1883
    Username = ""
    Password = ""
}

$RailwayBroker = @{
    Host = "shinkansen.proxy.rlwy.net"
    Port = 41071
    Username = "root"
    Password = "k6n6fxfyrtip7gln7abr00fgtie41tkd"
}

$ApiKey = "Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg"

function Show-Help {
    Write-Host "MQTT Test Commands for ESP32 IoT Communication" -ForegroundColor Green
    Write-Host "=============================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Usage: .\test-mqtt.ps1 -Action <action> -Broker <broker> -StationId <id>"
    Write-Host ""
    Write-Host "Actions:" -ForegroundColor Yellow
    Write-Host "  subscribe    - Listen to all ESP32 topics"
    Write-Host "  heartbeat    - Send heartbeat request"
    Write-Host "  config       - Send config request"
    Write-Host "  data         - Send data upload request"
    Write-Host "  test-all     - Run all tests"
    Write-Host ""
    Write-Host "Brokers:" -ForegroundColor Yellow
    Write-Host "  local        - Local Mosquitto (10.70.33.40:1883)"
    Write-Host "  railway      - Railway MQTT (shinkansen.proxy.rlwy.net:41071)"
    Write-Host ""
    Write-Host "Examples:" -ForegroundColor Cyan
    Write-Host "  .\test-mqtt.ps1 -Action subscribe -Broker railway"
    Write-Host "  .\test-mqtt.ps1 -Action heartbeat -Broker local -StationId JHR03004"
    Write-Host "  .\test-mqtt.ps1 -Action test-all -Broker railway"
}

function Get-BrokerConfig {
    param([string]$BrokerType)
    
    if ($BrokerType -eq "local") {
        return $LocalBroker
    } else {
        return $RailwayBroker
    }
}

function Get-MqttCommand {
    param(
        [hashtable]$Config,
        [string]$Type,
        [string]$Topic,
        [string]$Message = ""
    )
    
    $baseCmd = if ($Type -eq "pub") { "mosquitto_pub" } else { "mosquitto_sub" }
    
    $cmd = "$baseCmd -h $($Config.Host) -p $($Config.Port)"
    
    if ($Config.Username) {
        $cmd += " -u `"$($Config.Username)`" -P `"$($Config.Password)`""
    }
    
    if ($Type -eq "pub") {
        $cmd += " -t `"$Topic`" -m `"$Message`""
    } else {
        $cmd += " -t `"$Topic`""
    }
    
    return $cmd
}

function Test-Subscribe {
    param([hashtable]$Config)
    
    Write-Host "Subscribing to ESP32 topics..." -ForegroundColor Green
    Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
    Write-Host ""
    
    $topics = @(
        "iot/$StationId/heartbeat/+",
        "iot/$StationId/config/+",
        "iot/$StationId/data/+"
    )
    
    foreach ($topic in $topics) {
        $cmd = Get-MqttCommand -Config $Config -Type "sub" -Topic $topic
        Write-Host "Topic: $topic" -ForegroundColor Cyan
        Write-Host "Command: $cmd" -ForegroundColor Gray
        
        Start-Process powershell -ArgumentList "-Command", "$cmd" -WindowStyle Normal
        Start-Sleep 1
    }
}

function Test-Heartbeat {
    param([hashtable]$Config)
    
    $topic = "iot/$StationId/heartbeat/request"
    $payload = @{
        station_id = $StationId
        api_key = $ApiKey
        task = "heartbeat"
        message = "SEND"
        params = @{
            status = "online"
        }
    } | ConvertTo-Json -Compress
    
    Write-Host "Sending heartbeat request..." -ForegroundColor Green
    Write-Host "Topic: $topic" -ForegroundColor Cyan
    Write-Host "Payload: $payload" -ForegroundColor Gray
    
    $cmd = Get-MqttCommand -Config $Config -Type "pub" -Topic $topic -Message $payload
    Write-Host "Command: $cmd" -ForegroundColor Gray
    
    Invoke-Expression $cmd
    
    # Listen for response
    $responseTopic = "iot/$StationId/heartbeat/response"
    Write-Host "Listening for response on: $responseTopic" -ForegroundColor Yellow
    $responseCmd = Get-MqttCommand -Config $Config -Type "sub" -Topic $responseTopic
    
    $job = Start-Job -ScriptBlock {
        param($cmd)
        Invoke-Expression $cmd
    } -ArgumentList $responseCmd
    
    Wait-Job $job -Timeout 10
    $response = Receive-Job $job
    Remove-Job $job
    
    if ($response) {
        Write-Host "Response received:" -ForegroundColor Green
        Write-Host $response -ForegroundColor White
    } else {
        Write-Host "No response received (timeout)" -ForegroundColor Red
    }
}

function Test-Config {
    param([hashtable]$Config)
    
    $topic = "iot/$StationId/config/request"
    $payload = @{
        station_id = $StationId
        api_key = $ApiKey
        task = "Configuration Update"
        message = "SEND"
        params = @{
            configuration_update = "false"
        }
    } | ConvertTo-Json -Compress
    
    Write-Host "Sending config request..." -ForegroundColor Green
    Write-Host "Topic: $topic" -ForegroundColor Cyan
    Write-Host "Payload: $payload" -ForegroundColor Gray
    
    $cmd = Get-MqttCommand -Config $Config -Type "pub" -Topic $topic -Message $payload
    Invoke-Expression $cmd
    
    # Listen for response
    $responseTopic = "iot/$StationId/config/response"
    Write-Host "Listening for response on: $responseTopic" -ForegroundColor Yellow
    $responseCmd = Get-MqttCommand -Config $Config -Type "sub" -Topic $responseTopic
    
    $job = Start-Job -ScriptBlock {
        param($cmd)
        Invoke-Expression $cmd
    } -ArgumentList $responseCmd
    
    Wait-Job $job -Timeout 10
    $response = Receive-Job $job
    Remove-Job $job
    
    if ($response) {
        Write-Host "Response received:" -ForegroundColor Green
        Write-Host $response -ForegroundColor White
    } else {
        Write-Host "No response received (timeout)" -ForegroundColor Red
    }
}

function Test-Data {
    param([hashtable]$Config)
    
    $topic = "iot/$StationId/data/request"
    $payload = @{
        station_id = $StationId
        api_key = $ApiKey
        task = "Upload Data"
        message = "SEND"
        params = @{
            humidity = 65.5
            temperature = 28.3
            rssi = -67
            battery_voltage = 3.7
            update_request = $false
        }
    } | ConvertTo-Json -Compress
    
    Write-Host "Sending data upload request..." -ForegroundColor Green
    Write-Host "Topic: $topic" -ForegroundColor Cyan
    Write-Host "Payload: $payload" -ForegroundColor Gray
    
    $cmd = Get-MqttCommand -Config $Config -Type "pub" -Topic $topic -Message $payload
    Invoke-Expression $cmd
    
    # Listen for response
    $responseTopic = "iot/$StationId/data/response"
    Write-Host "Listening for response on: $responseTopic" -ForegroundColor Yellow
    $responseCmd = Get-MqttCommand -Config $Config -Type "sub" -Topic $responseTopic
    
    $job = Start-Job -ScriptBlock {
        param($cmd)
        Invoke-Expression $cmd
    } -ArgumentList $responseCmd
    
    Wait-Job $job -Timeout 10
    $response = Receive-Job $job
    Remove-Job $job
    
    if ($response) {
        Write-Host "Response received:" -ForegroundColor Green
        Write-Host $response -ForegroundColor White
    } else {
        Write-Host "No response received (timeout)" -ForegroundColor Red
    }
}

function Test-All {
    param([hashtable]$Config)
    
    Write-Host "Running all MQTT tests..." -ForegroundColor Green
    Write-Host "Broker: $($Config.Host):$($Config.Port)" -ForegroundColor Cyan
    Write-Host "Station: $StationId" -ForegroundColor Cyan
    Write-Host ""
    
    Test-Heartbeat -Config $Config
    Write-Host ""
    Start-Sleep 2
    
    Test-Config -Config $Config
    Write-Host ""
    Start-Sleep 2
    
    Test-Data -Config $Config
}

# Check if mosquitto tools are available
if (-not (Get-Command "mosquitto_pub" -ErrorAction SilentlyContinue)) {
    Write-Host "Error: mosquitto_pub not found!" -ForegroundColor Red
    Write-Host "Please install Mosquitto tools from: https://mosquitto.org/download/" -ForegroundColor Yellow
    exit 1
}

# Main execution
$config = Get-BrokerConfig -BrokerType $Broker

Write-Host "MQTT Test Tool - ESP32 IoT Communication" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host "Broker: $($config.Host):$($config.Port)" -ForegroundColor Cyan
Write-Host "Station ID: $StationId" -ForegroundColor Cyan
Write-Host ""

switch ($Action.ToLower()) {
    "subscribe" { Test-Subscribe -Config $config }
    "heartbeat" { Test-Heartbeat -Config $config }
    "config" { Test-Config -Config $config }
    "data" { Test-Data -Config $config }
    "test-all" { Test-All -Config $config }
    default { Show-Help }
}