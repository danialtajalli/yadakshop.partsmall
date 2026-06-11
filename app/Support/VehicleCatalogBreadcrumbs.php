<?php

namespace App\Support;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;

class VehicleCatalogBreadcrumbs
{
    /**
     * @return list<array{label: string, url?: string, active?: bool, emphasized?: bool}>
     */
    public static function build(
        ?Company $company = null,
        ?Car $car = null,
        ?CarModel $model = null,
        ?string $terminalLabel = null,
        bool $terminalActive = true,
        ?string $terminalUrl = null,
    ): array {
        $items = [
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'برندها', 'url' => route('companies.index')],
        ];

        if ($company !== null) {
            $items[] = [
                'label' => $company->name,
                'url' => route('cars.index', ['company' => $company->slug]),
                'emphasized' => $terminalLabel === null && $car === null,
            ];
        }

        if ($company !== null && $car !== null) {
            $items[] = [
                'label' => $car->name,
                'url' => route('models.index', [
                    'company' => $company->slug,
                    'car' => $car->slug,
                ]),
                'emphasized' => $terminalLabel === null && $model === null,
            ];
        }

        if ($company !== null && $car !== null && $model !== null) {
            $items[] = [
                'label' => CarModelLabel::display($model),
                'url' => route('car.parts', [
                    'company' => $company->slug,
                    'car' => $car->slug,
                    'model' => $model->slug,
                ]),
                'emphasized' => $terminalLabel === null,
            ];
        }

        if ($terminalLabel !== null) {
            $item = [
                'label' => $terminalLabel,
                'active' => $terminalActive,
            ];

            if ($terminalUrl !== null) {
                $item['url'] = $terminalUrl;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return list<array{label: string, url?: string, active?: bool, emphasized?: bool}>
     */
    public static function forPartsIndex(?Company $company, ?Car $car, ?CarModel $model): array
    {
        return self::build(
            company: $company,
            car: $car,
            model: $model,
            terminalLabel: 'قطعات',
            terminalActive: true,
            terminalUrl: route('parts.index', (new VehicleCatalogContext($company, $car, $model))->queryParams()),
        );
    }
}
