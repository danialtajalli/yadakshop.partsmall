<?php

namespace App\Support;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Support\CatalogUrls;

class VehicleCatalogBreadcrumbs
{
    /**
     * @return list<array{label: string, url?: string, active?: bool, emphasized?: bool}>
     */
    public static function build(
        ?Company $company = null,
        ?Car $car = null,
        ?CarModel $model = null,
        ?Part $part = null,
        ?string $terminalLabel = null,
        bool $terminalActive = true,
        ?string $terminalUrl = null,
    ): array {

        $items = [
            ['label' => 'خانه', 'url' => route('home')],
        ];

        if ($company !== null) {
            $array_item = [
                'label' => $company->name,
            ];
            if($terminalLabel === null && $car === null)
                $array_item['emphasized'] = true;
            else
                $array_item['url'] = CatalogUrls::cars($company->slug);
            $items[] = $array_item;
        }

        if ($company !== null && $car !== null) {
            $array_item = [
                'label' => $car->name,
            ];
            if($terminalLabel === null && $model === null)
                $array_item['emphasized'] = true;
            else
                $array_item['url'] = CatalogUrls::models($company->slug, $car->slug);

            $items[] = $array_item;
        }

        if ($company !== null && $car !== null && $model !== null) {
            $array_item = [
                'label' => CarModelLabel::display($model),
            ];
            if($terminalLabel === null && $part === null)
                $array_item['emphasized'] = true;
            else
                $array_item['url'] = CatalogUrls::parts($company->slug, $car->slug, $model->slug);
            $items[] = $array_item;
        }

        if ($company !== null && $car !== null && $model !== null && $part !== null) {
            $array_item = [
                'label' => "",
            ];
            $items[] = $array_item;
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
}
