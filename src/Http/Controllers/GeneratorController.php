<?php

namespace Zzzul\Generator\Http\Controllers;

use App\Http\Controllers\Controller;
use Zzzul\Generator\Enums\GeneratorType;
use Zzzul\Generator\Services\GeneratorService;
use Zzzul\Generator\Http\Requests\StoreGeneratorRequest;
use Symfony\Component\HttpFoundation\Response;

class GeneratorController extends Controller
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
        return view('generator::create');
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

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
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
