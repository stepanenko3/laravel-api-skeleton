<?php

namespace Stepanenko3\LaravelLogicContainers\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class APIversion
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next, mixed $guard)
    {
        Config::set('logic-containers.api_version', (int) $guard);

        return $next($request);
    }
}
