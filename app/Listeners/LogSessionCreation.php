<?php

namespace App\Listeners;

use Illuminate\Session\Events\SessionCreated;
use Illuminate\Support\Facades\Log;

class LogSessionCreation
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Session\Events\SessionCreated  $event
     * @return void
     */
    public function handle(SessionCreated $event)
    {
        // Get the session ID
        $sessionId = $event->session->getId();

        // Get Redis host being used from the configuration
        $redisConfig = config('database.redis.default');
        $redisHost = $redisConfig['host'] ?? 'unknown';
        $redisPort = $redisConfig['port'] ?? 'unknown';

        // Format log entry as JSON
        $logEntry = [
            'event' => 'session_creation',
            'session_id' => $sessionId,
            'redis_host' => $redisHost,
            'redis_port' => $redisPort,
            'timestamp' => now()->toIso8601String(),  // ISO 8601 formatted timestamp
        ];

        // Log the entry as JSON (CloudWatch will interpret this correctly)
        Log::info(json_encode($logEntry));
    }
}
