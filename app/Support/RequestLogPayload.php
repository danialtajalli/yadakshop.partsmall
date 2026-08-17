<?php

namespace App\Support;

use Illuminate\Http\Request;
use Throwable;

class RequestLogPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function make(
        Request $request,
        string $logType,
        int $statusCode,
        ?\DateTimeInterface $occurredAt = null,
        ?float $startedAtMicrotime = null,
        ?Throwable $exception = null,
    ): array {
        $statusFamily = intdiv($statusCode, 100) * 100;
        $route = $request->route();

        return [
            'log_type' => $logType,
            'occurred_at' => $occurredAt ?? now(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'route_name' => $route?->getName(),
            'route_action' => $route?->getActionName(),
            'status_code' => $statusCode,
            'status_family' => $statusFamily,
            'is_reportable_status' => in_array($statusFamily, [300, 400, 500], true),
            'duration_ms' => $startedAtMicrotime === null
                ? null
                : (int) round((microtime(true) - $startedAtMicrotime) * 1000),
            'user_id' => $request->user()?->getAuthIdentifier(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->headers->get('referer'),
            'exception' => $exception ? $exception::class.': '.$exception->getMessage() : null,
            'query' => $request->query(),
        ];
    }
}
