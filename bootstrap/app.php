<?php

use App\Http\Middleware\LogIncomingRequestsToDatabase;
use App\Http\Middleware\LogReportableResponsesToDatabase;
use App\Http\Middleware\NoIndexAdminRoutes;
use App\Jobs\StoreRequestLogJob;
use App\Support\RequestLogPayload;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(NoIndexAdminRoutes::class);
        $middleware->append(LogReportableResponsesToDatabase::class);
        $middleware->append(LogIncomingRequestsToDatabase::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->report(function (Throwable $exception): void {
            if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500) {
                return;
            }

            StoreRequestLogJob::dispatch(RequestLogPayload::make(
                request: request(),
                logType: 'reportable_exception',
                statusCode: $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500,
                exception: $exception,
            ));
        });
    })->create();
