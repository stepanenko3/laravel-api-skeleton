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
     * @param mixed $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard)
    {
        Config::set('logic-containers.api_version', (int) $guard);

        return $next($request);
    }
}
