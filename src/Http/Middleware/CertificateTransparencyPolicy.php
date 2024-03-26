<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CertificateTransparencyPolicy
{
    public function handle(
        Request $request,
        Closure $next,
    ) {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $response->headers->set(
            key: 'Expect-CT',
            values: (string) (config('headers.certificate-transparency')),
        );

        return $response;
    }
}
