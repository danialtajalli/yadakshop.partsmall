<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;

class PartSelectionService
{
    /**
     * @return array{
     *     company: Company,
     *     car: Car,
     *     model: CarModel,
     *     parts: Collection<int, Part>,
     *     title: string,
     * }
     */
    public function getPartSelectionPageData(
        Company $company,
        Car $car,
        CarModel $model,
    ): array {
        $parts = Part::query()
            ->with('partsCategory')
            ->orderBy('name')
            ->get();

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
            'parts' => $parts,
            'title' => $this->buildTitle($company, $car, $model),
        ];
    }

    private function buildTitle(Company $company, Car $car, CarModel $model): string
    {
        $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;

        return 'لوازم یدکی '.$company->name.' '.$car->name.' '.$modelName;
    }
}
