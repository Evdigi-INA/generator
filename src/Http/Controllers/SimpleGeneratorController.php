<?php

namespace EvdigiIna\Generator\Http\Controllers;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Enums\GeneratorVariant;
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
         * will added in next realease
         * now it's not working, because it's not implemented
         * only focus to fix the bug
         */
        // $checkFile = $this->generatorService->checkFilesAreSame($validated);

        // if(count($checkFile) > 0){
        //     return response()->json($checkFile, 403);
        // }

        if ($request->generate_type == GeneratorType::ALL->value) {
            $this->generatorService->generate($validated);
        } else {
            $this->generatorService->onlyGenerateModelAndMigration($validated);
        }

        $model = GeneratorUtils::setModelName($validated['model'], 'default');

        return response()->json([
            'message' => 'Success',
            'route' => GeneratorUtils::isGenerateApi() ? 'api/' . GeneratorUtils::pluralKebabCase($model) : GeneratorUtils::pluralKebabCase($model)
        ], Response::HTTP_CREATED);
    }
}
