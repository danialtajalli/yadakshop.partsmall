<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $company, string $car, string $model, string $part): View
    {
        $company = Company::where('slug', $company)->firstOrFail();

        $car = Car::query()
            ->where('slug', $car)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $model = CarModel::query()
            ->where('slug', $model)
            ->whereHas('cars', fn ($query) => $query->where('cars.id', $car->id))
            ->firstOrFail();

        $part = Part::query()
            ->where('slug', $part)
            ->with('partsCategory')
            ->firstOrFail();

        return view('product.show', compact('company', 'car', 'model', 'part'));
    }
}
