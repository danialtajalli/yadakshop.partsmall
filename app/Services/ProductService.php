<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Company;
use App\Models\Image;
use App\Models\Part;
use App\Models\Shop;
use App\Models\State;
use App\Support\VehicleCatalogBreadcrumbs;
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
     *     breadcrumbs: list<array<string, mixed>>,
     *     repairLocator: ?array{
     *         category: \App\Models\RepairCategory,
     *         carName: string,
     *         buttonLabel: string,
     *         states: Collection<int, State>,
     *         citiesByState: array<int, list<array{id: int, name: string}>>,
     *         defaultStateId: ?int,
     *     },
     * }
     */
    public function getProductPageData(Company $company, Car $car, CarModel $model, Part $part,): array {


        $car->description = $this->sanitizeDescription($car->description, $company, $car, $model);
        $part->description = $this->sanitizeDescription($part->description, $company, $car, $model);

        $repairCards = $this->buildRepairCards($part, $company);
        $shops = $this->loadShopsForPart($part, $car->company_id);

        $shops->each(function (Shop $shop) use ($company, $car, $model): void {
            $shop->description = $this->sanitizeDescription($shop->description, $company, $car, $model);
            $this->loadImagesForShops($shop);
        });

        $title = $this->buildTitle($part, $company, $car, $model);

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
            'part' => $part,
            'repairCards' => $repairCards,
            'shops' => $shops,
            'title' => $title,
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $company,
                car: $car,
                model: $model,
                part: $part,
                terminalLabel: $title,
            ),
            'repairLocator' => $this->buildRepairLocatorContext($part, $car),
        ];
    }

    /**
     * @return ?array{
     *     category: \App\Models\RepairCategory,
     *     carName: string,
     *     buttonLabel: string,
     *     states: Collection<int, State>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     defaultStateId: ?int,
     * }
     */
    private function buildRepairLocatorContext(Part $part, Car $car): ?array
    {
        $category = $part->repairCategories->first();

        if ($category === null) {
            return null;
        }

        $location = $this->locationFilterData();

        return [
            'category' => $category,
            'carName' => $car->name,
            'buttonLabel' => "مشاهده خدمات {$category->name} {$car->name} در محدوده شما",
            'states' => $location['states'],
            'citiesByState' => $location['citiesByState'],
            'defaultStateId' => null,
        ];
    }

    /**
     * @return array{
     *     states: Collection<int, State>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     * }
     */
    private function locationFilterData(): array
    {
        return [
            'states' => State::query()->orderBy('name')->get(['id', 'name']),
            'citiesByState' => City::query()
                ->orderBy('name')
                ->get(['id', 'name', 'state_id'])
                ->groupBy('state_id')
                ->map(fn (Collection $cities) => $cities->map(fn (City $city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                ])->values()->all())
                ->all(),
        ];
    }

    private function loadImagesForShops(Shop $shop): void
    {
        $shop->images->each(function (Image $image) use ($shop) : void {
            if($image->type === ImageType::Cover)
            {
                $shop->cover = config('partsmall.image_url', 'https://partsmall.ir/panel/assets/uploads/{model_type}/{image_type}/{model_id}/{image_name}');
                $shop->cover = str_replace('{model_type}', "shop", $shop->cover);
                $shop->cover = str_replace('{image_type}', $image->type->value, $shop->cover);
                $shop->cover = str_replace('{model_id}', $shop->id, $shop->cover);
                $shop->cover = str_replace('{image_name}', $image->path, $shop->cover);
            }
            elseif($image->type === ImageType::Logo)
            {
                $shop->logo = config('partsmall.image_url', 'https://partsmall.ir/panel/assets/uploads/{model_type}/{image_type}/{model_id}/{image_name}');
                $shop->logo = str_replace('{model_type}', "shop", $shop->logo);
                $shop->logo = str_replace('{image_type}', $image->type->value, $shop->logo);
                $shop->logo = str_replace('{model_id}', $shop->id, $shop->logo);
                $shop->logo = str_replace('{image_name}', $image->path, $shop->logo);
            }

            $image->save();
        });
    }

    private function sanitizeDescription(?string $description, Company $company, Car $car, CarModel $model): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['ظظظ', 'rn', 'ططط', 'ممم'],
            [$company->name, '', $car->name, $model->name],
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
                    ? (int) (($wage->variable * ($wage->coefficient??1) * $company->wage_strike) * 100000)
                    : null,
                'wage_name' => $wage?->name,
            ];
        }

        return $cards;
    }

    /** @return Collection<int, Shop> */
    private function loadShopsForPart(Part $part, int $company_id): Collection
    {
        $query = fn () => Shop::query()
            ->visibleUnderProduct()
            ->ordered()
            ->with(['phones', 'links', 'state', 'images'])
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating');

        $shops = $query()
            ->whereHas('parts', fn ($q) => $q->whereKey($part->id))
            ->get();

        if ($shops->isEmpty() && $company_id) {
            $shops = $query()
                ->whereHas(
                    'companies',
                    fn ($q) => $q->where('companies.id', $company_id),
                )
                ->whereHas('images', fn ($q) => $q->where('type', ImageType::Logo))
                ->get();
        }

        return $shops;
    }
}
