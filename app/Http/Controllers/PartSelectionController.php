<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Services\PartSelectionService;
use Illuminate\View\View;

class PartSelectionController extends Controller
{
    public function __construct(
        private readonly PartSelectionService $partSelectionService,
    ) {}

    public function show(string $company, string $car, string $model): View
    {
        $company = Company::where('slug', $company)->firstOrFail();
        $car = Car::where('slug', $car)->where('company_id', $company->id)->firstOrFail();
        $model = CarModel::where('slug', $model)->whereHas('cars', fn ($query) => $query->where('cars.id', $car->id))->firstOrFail();

        return view('car.parts', $this->partSelectionService->getPartSelectionPageData($company, $car, $model));
    }
}
