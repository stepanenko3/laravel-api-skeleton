<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ETagMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $response = $next($request);

        if ($response instanceof Response) {
            $etag = md5($response->getContent());

            if ($request->getETags() && in_array($etag, $request->getETags(), true)) {
                return response('', 304);
            }

            $response->setEtag($etag);
        }

        return $response;
    }
}
