<?php

namespace App\Support;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use Illuminate\Http\Request;

class VehicleCatalogContext
{
    public function __construct(
        public readonly ?Company $company = null,
        public readonly ?Car $car = null,
        public readonly ?CarModel $model = null,
    ) {}

    public static function fromRequest(Request $request, ?string $company_data = null, ?string $car_data = null, ?string $model_data = null): self
    {
        $company = null;
        $car = null;
        $model = null;
        $companySlug = $request->string('company')->trim()->toString();

        $companySlug = !empty($companySlug) ? ($request->string('company')->trim()->toString()) : $company_data;

        if ($companySlug !== '') {
            $company = Company::query()->where('slug', $companySlug)->first();
        }

        $carSlug = $request->string('car')->trim()->toString();
        $carSlug = !empty($carSlug) ? $carSlug : $car_data;

        if ($company !== null && $carSlug !== '') {
            $car = Car::query()
                ->where('slug', $carSlug)
                ->where('company_id', $company->id)
                ->first();
        }

        $modelSlug = $request->string('model')->trim()->toString();
        $modelSlug = !empty($modelSlug) ? $modelSlug : $model_data;

        if ($car !== null && $modelSlug !== '') {
            $model = CarModel::query()
                ->where('slug', $modelSlug)
                ->whereHas('cars', fn ($query) => $query->where('cars.id', $car->id))
                ->first();
        }

        return new self($company, $car, $model);
    }

    /**
     * @return array<string, string>
     */
    public function queryParams(): array
    {
        $params = [];

        if ($this->company !== null) {
            $params['company'] = $this->company->slug;
        }

        if ($this->car !== null) {
            $params['car'] = $this->car->slug;
        }

        if ($this->model !== null) {
            $params['model'] = $this->model->slug;
        }

        return $params;
    }
}
