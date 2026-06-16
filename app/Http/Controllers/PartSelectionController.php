<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Services\VehicleCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartSelectionController extends Controller
{
    public function __construct(
        private readonly VehicleCatalogService $vehicleCatalogService,
    ) {}

    public function show(Request $request, ?string $company = null, ?string $car = null, ?string $model = null): View
    {
        return view('catalog.parts', $this->vehicleCatalogService->getPartsIndexData(
            $request,
            $company,
            $car,
            $model,
        ));
    }
}
