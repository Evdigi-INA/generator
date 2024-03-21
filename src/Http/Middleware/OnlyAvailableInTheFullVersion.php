<?php

namespace EvdigiIna\Generator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAvailableInTheFullVersion
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $dir = __DIR__ . '/../../../generator-cache';

        abort_if(!file_exists($dir), Response::HTTP_FORBIDDEN, 'You have not yet selected a version, to use this feature, you must be running the artisan command: "php artisan generator:install full", and then you can use the full version.');

        $cache = file_get_contents($dir);

        $selectedVersion = collect(json_decode($cache))->toArray();

        if ($selectedVersion['full_version_publish_count'] == null || $selectedVersion['full_version_publish_count'] < 1) {
            abort(Response::HTTP_FORBIDDEN, 'You are using the simple version, to use this feature, you must be running the artisan command: "php artisan generator:install full", and then you can use the full version.');
        }

        return $next($request);
    }
}
