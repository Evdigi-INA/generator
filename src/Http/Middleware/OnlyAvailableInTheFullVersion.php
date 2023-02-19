<?php

namespace EvdigiIna\Generator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAvailableInTheFullVersion
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $dir = __DIR__ . '/../../../generator-cache.json';

        abort_if(!file_exists($dir), Response::HTTP_FORBIDDEN, 'You have not yet selected a version, to use this feature, you must be running the artisan command: "php artisan generator:publish all", and then you can use the full version.');

        $cache = file_get_contents($dir);

        $selectedVersion = collect(json_decode($cache))->toArray();

        if ($selectedVersion['generator_publish_all'] == null || $selectedVersion['generator_publish_all'] < 1) {
            abort(Response::HTTP_FORBIDDEN, 'You are using the simple version, to use this feature, you must be running the artisan command: "php artisan generator:publish all", and then you can use the full version.');
        }

        return $next($request);
    }
}
