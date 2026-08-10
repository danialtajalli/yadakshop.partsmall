<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Enums\LinkType;
use App\Services\ProductService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function show(string $company, string $car, string $model, string $part): View
    {
        $company = Company::query()
            ->with([
                'links' => fn ($query) => $query
                    ->select(['id', 'company_id', 'link_type', 'name'])
                    ->where('link_type', LinkType::Telegram),
            ])
            ->where('slug', $company)
            ->firstOrFail(['id', 'name', 'slug', 'wage_strike']);

        $car = Car::query()
            ->where('slug', $car)
            ->where('company_id', $company->id)
            ->firstOrFail(['id', 'name', 'description', 'slug', 'company_id']);

        $model = CarModel::query()
            ->where('slug', $model)
            ->whereHas('cars', fn ($query) => $query->where('cars.id', $car->id))
            ->firstOrFail(['id', 'name', 'slug', 'category_id']);

        $part = Part::query()
            ->with([
                'partsCategory:id,name',
                'repairCategories:id,name',
                'wages:id,name,variable,coefficient',
            ])
            ->where('slug', $part)
            ->firstOrFail(['id', 'name', 'description', 'category_description', 'slug', 'parts_category_id']);


        $SanitizedData = $this->productService->getProductPageData($company, $car, $model, $part);
        return view('product.show', $SanitizedData);
    }
}
