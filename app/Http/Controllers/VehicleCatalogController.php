<?php

namespace App\Http\Controllers;

use App\Services\VehicleCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleCatalogController extends Controller
{
    public function __construct(
        private readonly VehicleCatalogService $vehicleCatalogService,
    ) {}

    public function companies(Request $request): View
    {
        return view('catalog.companies', $this->vehicleCatalogService->getCompaniesIndexData($request));
    }

    public function cars(Request $request): View
    {
        return view('catalog.cars', $this->vehicleCatalogService->getCarsIndexData($request));
    }

    public function models(Request $request): View
    {
        return view('catalog.models', $this->vehicleCatalogService->getModelsIndexData($request));
    }
}
