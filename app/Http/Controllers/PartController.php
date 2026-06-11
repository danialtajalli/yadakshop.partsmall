<?php

namespace App\Http\Controllers;

use App\Services\PartPageService;
use App\Services\VehicleCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartController extends Controller
{
    public function __construct(
        private readonly PartPageService $partPageService,
        private readonly VehicleCatalogService $vehicleCatalogService,
    ) {}

    public function index(Request $request): View
    {
        return view('catalog.parts', $this->vehicleCatalogService->getPartsIndexData($request));
    }

    public function show(Request $request, string $part): View
    {
        return view('part.show', $this->partPageService->getPartPageData($part, $request));
    }
}
