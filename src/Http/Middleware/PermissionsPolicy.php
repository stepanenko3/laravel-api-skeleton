<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PermissionsPolicy
{
    public function handle(
        Request $request,
        Closure $next,
    ): mixed {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $response->headers->set(
            key: 'Permissions-Policy',
            values: (string) (config('headers.permissions-policy')),
        );

        return $response;
    }
}
