<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Services\ProductService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function show(string $company, string $car, string $model, string $part): View
    {
        $company = Company::where('slug', $company)->firstOrFail();
        $car = Car::where('slug', $car)->where('company_id', $company->id)->firstOrFail();
        $model = CarModel::where('slug', $model)->whereHas('cars', fn ($query) => $query->where('cars.id', $car->id))->firstOrFail();
        $part = Part::where('slug', $part)->with(['partsCategory', 'repairCategories', 'wages'])->firstOrFail();


        $SanitizedData = $this->productService->getProductPageData($company, $car, $model, $part);
        return view('product.show', $SanitizedData);
    }
}
