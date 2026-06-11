<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Support\CarModelLabel;
use App\Support\VehicleCatalogBreadcrumbs;
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
     *     breadcrumbs: list<array<string, mixed>>,
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

        $modelName = CarModelLabel::display($model);

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
            'parts' => $parts,
            'title' => $this->buildTitle($company, $car, $model),
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $company,
                car: $car,
                model: $model,
                terminalLabel: 'انتخاب قطعه',
                terminalActive: true,
                terminalUrl: route('car.parts', [
                    'company' => $company->slug,
                    'car' => $car->slug,
                    'model' => $model->slug,
                ]),
            ),
        ];
    }

    private function buildTitle(Company $company, Car $car, CarModel $model): string
    {
        return 'لوازم یدکی '.$company->name.' '.$car->name.' '.CarModelLabel::display($model);
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
