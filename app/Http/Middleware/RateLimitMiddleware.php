<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Rate limiter instance
     */
    protected RateLimiter $limiter;

    /**
     * Maximum requests per minute
     */
    protected const MAX_REQUESTS = 100;

    /**
     * Time window in seconds (1 minute)
     */
    protected const TIME_WINDOW = 60;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use IP address as the rate limit key
        $key = 'rate_limit:' . $request->ip();

        // Increment request count
        $requests = $this->limiter->attempts($key);

        // If first request, set expiration
        if ($requests === 1) {
            $this->limiter->hit($key, self::TIME_WINDOW);
        }

        // Check if limit exceeded
        if ($requests > self::MAX_REQUESTS) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de requisições excedido. Máximo de ' . self::MAX_REQUESTS . ' requisições por minuto.',
            ], 429)->header('Retry-After', self::TIME_WINDOW);
        }

        $response = $next($request);

        // Add rate limit headers
        return $response
            ->header('X-RateLimit-Limit', self::MAX_REQUESTS)
            ->header('X-RateLimit-Remaining', max(0, self::MAX_REQUESTS - $requests))
            ->header('X-RateLimit-Reset', now()->addSeconds(self::TIME_WINDOW)->timestamp);
    }
}
