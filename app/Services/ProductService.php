<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * @return array{
     *     company: Company,
     *     car: Car,
     *     model: CarModel,
     *     part: Part,
     *     repairCards: list<array{type: string, cost: ?int, wage_name: ?string}>,
     *     shops: Collection<int, Shop>,
     *     title: string,
     * }
     */
    public function getProductPageData(
        Company $company,
        Car $car,
        CarModel $model,
        Part $part,
    ): array {


        $car->description = $this->sanitizeDescription($car->description, $company, $car);
        $part->description = $this->sanitizeDescription($part->description, $company, $car);

        $repairCards = $this->buildRepairCards($part, $company);
        $shops = $this->loadShopsForPart($part);

        $shops->each(function (Shop $shop) use ($company, $car): void {
            $shop->description = $this->sanitizeDescription($shop->description, $company, $car);
        });

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
            'part' => $part,
            'repairCards' => $repairCards,
            'shops' => $shops,
            'title' => $this->buildTitle($part, $company, $car, $model),
        ];
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

    private function buildTitle(Part $part, Company $company, Car $car, CarModel $model): string
    {
        $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;

        return $part->name.' '.$company->name.' '.$car->name.' '.$modelName;
    }

    /**
     * @return list<array{type: string, cost: ?int, wage_name: ?string}>
     */
    private function buildRepairCards(Part $part, Company $company): array
    {
        $cards = [];
        $wages = $part->wages->values();
        $categories = $part->repairCategories->values();

        for ($i = 0; $i < 3; $i++) {
            $category = $categories->get($i);
            $wage = $wages->get($i) ?? $wages->first();

            if ($category === null && $wage === null) {
                break;
            }

            $cards[] = [
                'type' => $category?->name ?? $wage->name,
                'cost' => $wage
                    ? (int) round($wage->variable * $wage->coefficient * $company->wage_strike)
                    : null,
                'wage_name' => $wage?->name,
            ];
        }

        return $cards;
    }

    /** @return Collection<int, Shop> */
    private function loadShopsForPart(Part $part): Collection
    {
        $query = fn () => Shop::query()
            ->with(['phones', 'links', 'state'])
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating')
            ->orderBy('order');

        $shops = $query()
            ->whereHas('parts', fn ($q) => $q->where('parts.id', $part->id))
            ->get();

        if ($shops->isEmpty() && $part->parts_category_id) {
            $shops = $query()
                ->whereHas('partsCategories', fn ($q) => $q->where('parts_categories.id', $part->parts_category_id))
                ->get();
        }

        return $shops;
    }
}
