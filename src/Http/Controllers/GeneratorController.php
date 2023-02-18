<?php

namespace EvdigiIna\Generator\Http\Controllers;

use App\Http\Controllers\Controller;
use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Services\GeneratorService;
use EvdigiIna\Generator\Http\Requests\StoreGeneratorRequest;
use Symfony\Component\HttpFoundation\Response;
use EvdigiIna\Generator\Generators\GeneratorUtils;

class GeneratorController extends Controller
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
        return view('generator::create');
    }

    /**
     * Show the form for creating a new resource.(bootstrap only)
     *
     * @return \Illuminate\Http\Response
     */
    public function simpleCreate()
    {
        return view('generator::simple-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGeneratorRequest $request)
    {
        if ($request->generate_type == GeneratorType::ALL->value) {
            $this->generatorService->generateAll($request->validated());
        } else {
            $this->generatorService->onlyGenerateModelAndMigration($request->validated());
        }

        $model = GeneratorUtils::setModelName($request->model, 'default');

        return response()->json([
            'message' => 'success',
            'route' => GeneratorUtils::pluralKebabCase($model)
        ], Response::HTTP_CREATED);
    }

    /**
     * Get all sidebar menus on config by index.
     *
     * @param int $index
     * @return \Illuminate\Http\Response
     */
    public function getSidebarMenus(int $index)
    {
        $sidebar = $this->generatorService->getSidebarMenusByIndex($index);

        return response()->json($sidebar['menus'], Response::HTTP_OK);
    }
}
