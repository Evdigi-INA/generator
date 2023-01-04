<?php

namespace Zzzul\Generator\Http\Controllers;

use Zzzul\Generator\Enums\GeneratorType;
use Zzzul\Generator\Services\GeneratorService;
use Symfony\Component\HttpFoundation\Response;
use Zzzul\Generator\Http\Requests\StoreSimpleGeneratorRequest;
use Zzzul\Generator\Generators\GeneratorUtils;

class SimpleGeneratorController extends Controller
{
    public function __construct(protected $generatorService = new GeneratorService())
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('generator::simple-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSimpleGeneratorRequest $request)
    {
        $attrs = $request->validated();
        $attrs['is_simple_generator'] = true;

        /**
         * will added in next realease
         * now it's not working, because it's not implemented
         * only focus to fix the bug
         */
        // $checkFile = $this->generatorService->checkFilesAreSame($attrs);

        // if(count($checkFile) > 0){
        //     return response()->json($checkFile, 403);
        // }

        if ($request->generate_type == GeneratorType::ALL->value) {
            $this->generatorService->simpleGenerator($attrs);
        } else {
            $this->generatorService->onlyGenerateModelAndMigration($attrs);
        }

        $model = GeneratorUtils::setModelName($attrs['model'], 'default');

        return response()->json([
            'message' => 'qwerty',
            'route' => GeneratorUtils::pluralKebabCase($model)
        ], Response::HTTP_CREATED);
    }
}
