<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class StrictTransportSecurity
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $response->headers->set(
            key: 'Strict-Transport-Security',
            values: (string) (config('headers.strict-transport-security')),
        );

        return $response;
    }
}
