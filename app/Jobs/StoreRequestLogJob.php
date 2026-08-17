<?php

namespace App\Jobs;

use App\Models\RequestLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StoreRequestLogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly array $data,
    ) {}

    public function handle(): void
    {
        if (! Schema::hasTable('request_logs')) {
            return;
        }

        try {
            RequestLog::query()->create($this->data);
        } catch (Throwable $exception) {
            Log::error('StoreRequestLogJob failed while writing request log.', [
                'exception' => $exception,
                'attempt' => $this->attempts(),
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
                'request_log_payload' => $this->data,
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('StoreRequestLogJob exhausted all attempts.', [
            'exception' => $exception,
            'exception_class' => $exception ? $exception::class : null,
            'exception_message' => $exception?->getMessage(),
            'request_log_payload' => $this->data,
        ]);
    }
}
