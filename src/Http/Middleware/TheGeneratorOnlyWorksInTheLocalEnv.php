<?php

namespace EvdigiIna\Generator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TheGeneratorOnlyWorksInTheLocalEnv
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        abort_if(env('APP_ENV') !== 'local', Response::HTTP_FORBIDDEN, 'Generator only work on local or development environment');

        return $next($request);
    }
}
