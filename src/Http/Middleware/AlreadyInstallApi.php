<?php

namespace EvdigiIna\Generator\Http\Middleware;

use Closure;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlreadyInstallApi
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if(!(new GeneratorService)->apiRouteAlreadyExists()){
            abort(Response::HTTP_FORBIDDEN, 'You have not yet installed the API, to use this feature, you must be running the artisan command: "php artisan api:install", and then you can use the API.');
        }

        return $next($request);
    }
}
