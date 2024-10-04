<?php

namespace EvdigiIna\Generator\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use EvdigiIna\Generator\Enums\GeneratorType;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\HttpFoundation\Response;
use EvdigiIna\Generator\Generators\GeneratorUtils;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use EvdigiIna\Generator\Http\Requests\StoreGeneratorRequest;
use EvdigiIna\Generator\Generators\Services\GeneratorService;

class GeneratorController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(public GeneratorService $generatorService)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('generator::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGeneratorRequest $request): JsonResponse
    {
        switch ($request->generate_type) {
            case GeneratorType::ALL->value:
                $this->generatorService->generate($request->validated());
                break;
            default:
                $this->generatorService->onlyGenerateModelAndMigration($request->validated());
                break;
        }

        $model = GeneratorUtils::setModelName($request->model, 'default');

        $route = $request->generate_type == GeneratorType::ALL->value
            ? (GeneratorUtils::isGenerateApi() ? 'api/' . GeneratorUtils::pluralKebabCase($model) : GeneratorUtils::pluralKebabCase($model))
            : request()->path() . '/create';

        return response()->json([
            'message' => 'success',
            'route' => $route
        ], Response::HTTP_CREATED);
    }

    /**
     * Get all sidebar menus on config by index.
     */
    public function getSidebarMenus(int $index): JsonResponse
    {
        $sidebar = $this->generatorService->getSidebarMenusByIndex($index);

        return response()->json($sidebar['menus'], Response::HTTP_OK);
    }

    public function apiCreate(): View
    {
        return view('generator::api-create');
    }
}
