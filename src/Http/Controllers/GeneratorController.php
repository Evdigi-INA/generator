<?php

namespace EvdigiIna\Generator\Http\Controllers;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Generators\GeneratorUtils;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use EvdigiIna\Generator\Http\Requests\StoreGeneratorRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

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
            ? (GeneratorUtils::isGenerateApi() ? 'api/'.GeneratorUtils::pluralKebabCase($model) : GeneratorUtils::pluralKebabCase($model))
            : request()->path().'/create';

        return response()->json([
            'message' => 'success',
            'route' => $route,
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
