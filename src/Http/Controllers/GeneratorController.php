<?php

namespace EvdigiIna\Generator\Http\Controllers;

use App\Http\Controllers\Controller;
use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use EvdigiIna\Generator\Http\Requests\StoreGeneratorRequest;
use Symfony\Component\HttpFoundation\Response;
use EvdigiIna\Generator\Generators\GeneratorUtils;

class GeneratorController extends Controller
{
    public function __construct(protected GeneratorService $generatorService)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('generator::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGeneratorRequest $request): \Illuminate\Http\JsonResponse
    {
        if ($request->generate_type == GeneratorType::ALL->value) {
            $this->generatorService->generate($request->validated());
        } else {
            $this->generatorService->onlyGenerateModelAndMigration($request->validated());
        }

        $model = GeneratorUtils::setModelName($request->model, 'default');

        return response()->json([
            'message' => 'success',
            'route' => GeneratorUtils::isGenerateApi() ? 'api/' . GeneratorUtils::pluralKebabCase($model) : GeneratorUtils::pluralKebabCase($model)
        ], Response::HTTP_CREATED);
    }

    /**
     * Get all sidebar menus on config by index.
     */
    public function getSidebarMenus(int $index): \Illuminate\Http\JsonResponse
    {
        $sidebar = $this->generatorService->getSidebarMenusByIndex($index);

        return response()->json($sidebar['menus'], Response::HTTP_OK);
    }

    public function apiCreate(): \Illuminate\Contracts\View\View
    {
        return view('generator::api-create');
    }
}
