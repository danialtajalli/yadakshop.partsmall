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
        $car->description = $this->sanitizeDescription($car->description, $company, $car);

        $parts = Part::query()
            ->with('partsCategory')
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

    private function sanitizeDescription(?string $description, Company $company, Car $car): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['ظظظ', 'rn', 'ططط'],
            [$company->name, '', $car->name],
            $description,
        );
    }
}
