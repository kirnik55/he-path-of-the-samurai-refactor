<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Простой rate-limit: 60 запросов в минуту на IP+route.
     */
    public function handle(Request $request, Closure $next)
    {
        // ключ вида rate:127.0.0.1:/api/astro/events
        $key = sprintf('rate:%s:%s', $request->ip(), $request->path());

        $maxAttempts = 60; // сколько запросов
        $decay       = 60; // за сколько секунд

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retry = RateLimiter::availableIn($key);

            return response()->json([
                'error'       => 'Too many requests',
                'retry_after' => $retry,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, $decay);

        return $next($request);
    }
}
