<?php

namespace App\Http\Middleware;

use App\Jobs\StoreRequestLogJob;
use App\Support\RequestLogPayload;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogIncomingRequestsToDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = now();
        $startedAtMicrotime = microtime(true);

        /** @var Response $response */
        $response = $next($request);
        $statusCode = $response->getStatusCode();
        $statusFamily = intdiv($statusCode, 100) * 100;

        if (in_array($statusFamily, [300, 400, 500], true)) {
            return $response;
        }

        StoreRequestLogJob::dispatch(RequestLogPayload::make(
            request: $request,
            logType: 'incoming_request',
            statusCode: $statusCode,
            occurredAt: $startedAt,
            startedAtMicrotime: $startedAtMicrotime,
        ));

        return $response;
    }
}
