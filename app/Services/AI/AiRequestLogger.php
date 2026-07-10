<?php

namespace App\Services\AI;

use App\Models\AiRequestLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiRequestLogger
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function write(array $attributes): void
    {
        try {
            AiRequestLog::query()->create($attributes);
        } catch (Throwable $exception) {
            Log::warning('Could not persist AI request log.', [
                'provider' => $attributes['provider'] ?? null,
                'status' => $attributes['status'] ?? null,
                'exception' => $exception,
            ]);
        }
    }
}
