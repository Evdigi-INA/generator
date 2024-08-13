<?php

namespace EvdigiIna\Generator\Http\Controllers;

use EvdigiIna\Generator\Enums\{GeneratorType, GeneratorVariant};
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Symfony\Component\HttpFoundation\Response;
use EvdigiIna\Generator\Http\Requests\StoreSimpleGeneratorRequest;
use EvdigiIna\Generator\Generators\GeneratorUtils;

class SimpleGeneratorController extends Controller
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
        return view('generator::simple-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSimpleGeneratorRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $validated['is_simple_generator'] = true;
        $validated['generate_variant'] = GeneratorVariant::DEFAULT->value;

        /**
         * will added in next release
         * now it's not working, because it's not implemented
         * only focus to fix the bug
         */
        // $checkFile = $this->generatorService->checkFilesAreSame($validated);

        // if(count($checkFile) > 0){
        //     return response()->json($checkFile, 403);
        // }

        switch ($request->generate_type) {
            case GeneratorType::ALL->value:
                $this->generatorService->generate($validated);
                break;
            default:
                $this->generatorService->onlyGenerateModelAndMigration($validated);
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
}
