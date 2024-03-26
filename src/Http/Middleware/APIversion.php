<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class APIversion
{
    public function handle(
        Request $request,
        Closure $next,
        mixed $guard
    ): mixed {
        Config::set(
            key: 'laravel-api-skeleton.api_version',
            value: (int) $guard,
        );

        return $next($request);
    }
}
