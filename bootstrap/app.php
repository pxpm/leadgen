<?php

use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\ValidateTwilioWebhook;
use App\Http\Middleware\VerifyTurnstile;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active-subscription' => EnsureActiveSubscription::class,
            'turnstile' => VerifyTurnstile::class,
            'twilio-webhook' => ValidateTwilioWebhook::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Strip sensitive debug info from API error responses.
        // APP_DEBUG may be true locally, but we never leak file paths / traces.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $code = method_exists($e, 'getStatusCode')
                    ? $e->getStatusCode()
                    : ($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);

                return response()->json([
                    'error' => class_basename($e),
                    'message' => app()->isProduction()
                        ? 'Server error.'
                        : $e->getMessage(),
                ], $code);
            }
        });
    })->create();
