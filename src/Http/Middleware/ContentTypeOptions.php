<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

final class ContentTypeOptions
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
            key: 'X-Content-Type-Options',
            values: (string) (config('headers.content-type-options')),
        );

        return $response;
    }
}
