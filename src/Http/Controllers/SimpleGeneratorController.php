<?php

namespace Zzzul\Generator\Http\Controllers;

use App\Http\Controllers\Controller;
use Zzzul\Generator\Enums\GeneratorType;
use Zzzul\Generator\Services\GeneratorService;
use Symfony\Component\HttpFoundation\Response;
use Zzzul\Generator\Http\Requests\StoreSimpleGeneratorRequest;
use Zzzul\Generator\Generators\GeneratorUtils;

class SimpleGeneratorController extends Controller
{
    protected $generatorService;

    public function __construct()
    {
        $this->generatorService = new GeneratorService();
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

        if ($request->generate_type == GeneratorType::ALL->value) {
            $this->generatorService->simpleGenerator($attrs);
        } else {
            $this->generatorService->onlyGenerateModelAndMigration($attrs);
        }

        $model =  GeneratorUtils::setModelName($attrs['model']);

        return response()->json([
            'message' => 'success',
            'route' => GeneratorUtils::pluralKebabCase($model)
        ], Response::HTTP_CREATED);
    }
}
