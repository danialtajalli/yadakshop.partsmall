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

        StoreRequestLogJob::dispatch(RequestLogPayload::make(
            request: $request,
            logType: 'incoming_request',
            statusCode: $response->getStatusCode(),
            occurredAt: $startedAt,
            startedAtMicrotime: $startedAtMicrotime,
        ));

        return $response;
    }
}
