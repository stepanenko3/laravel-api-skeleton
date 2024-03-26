<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class RemoveHeaders
{
    public function handle(
        Request $request,
        Closure $next,
    ): mixed {
        /**
         * @var Response $response
         */
        $response = $next($request);

        foreach ((array) config('headers.remove') as $header) {
            header_remove($header);

            $response->headers->remove(
                key: $header,
            );
        }

        return $response;
    }
}
