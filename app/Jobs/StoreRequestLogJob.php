<?php

namespace App\Jobs;

use App\Exceptions\RequestLogWriteException;
use App\Models\RequestLog;
use App\Support\RequestLogPayload;
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

    public int $tries = 3;

    public array $backoff = [10, 60];

    public readonly array $data;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->data = RequestLogPayload::limitException($data);
    }

    public function handle(): void
    {
        try {
            if (! Schema::hasTable('request_logs')) {
                return;
            }

            // Deserializing existing jobs does not call the constructor.
            RequestLog::query()->create(RequestLogPayload::limitException($this->data));
        } catch (Throwable $exception) {
            throw new RequestLogWriteException($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('StoreRequestLogJob exhausted all attempts.', [
            'job_id' => $this->job?->getJobId(),
            'attempt' => $this->attempts(),
            'path' => mb_strcut((string) ($this->data['path'] ?? ''), 0, 256, 'UTF-8'),
            'status_code' => $this->data['status_code'] ?? null,
            'exception_class' => $exception instanceof RequestLogWriteException
                ? $exception->originalClass
                : ($exception === null ? null : $exception::class),
            'exception_code' => $exception instanceof RequestLogWriteException
                ? $exception->originalCode
                : mb_strcut((string) ($exception?->getCode() ?? ''), 0, 64, 'UTF-8'),
            'exception_message' => mb_strcut($exception?->getMessage() ?? '', 0, 1200, 'UTF-8'),
        ]);
    }
}
