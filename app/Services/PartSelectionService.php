<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Support\CarModelLabel;
use App\Support\VehicleCatalogBreadcrumbs;
use App\Support\VehicleCatalogContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

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
        Request $request,
        Company $company,
        Car $car,
        CarModel $model,
    ): array {
        $parts = Part::query()
            ->with('partsCategory')
            ->get();

        $modelName = CarModelLabel::display($model);

        return [
            'company' => $company,
            'context' => VehicleCatalogContext::fromRequest($request, $company, $car),
            'car' => $car,
            'model' => $model,
            'description' => $this->sanitizeDescription($car->description, $company, $car),
            'parts' => $parts,
            'title' => $this->buildTitle($company, $car, $model),
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $company,
                car: $car,
                model: $model,
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
