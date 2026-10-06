<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Company;
use App\Models\Part;
use App\Models\RepairCategory;
use App\Models\Shop;
use App\Models\State;
use App\Support\CarModelLabel;
use App\Support\MetaDescription;
use App\Support\ContentCacheTag;
use App\Support\SafeCache;
use App\Support\ShopImageUrlBuilder;
use App\Support\VehicleCatalogBreadcrumbs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    private const RELATED_PRODUCTS_LIMIT = 8;

    private const FILTER_CACHE_TTL = 86400;

    /**
     * @return array{
     *     company: Company,
     *     car: Car,
     *     model: CarModel,
     *     part: Part,
     *     repairCards: list<array{type: string, cost: ?int, wage_name: ?string}>,
     *     shops: Collection<int, Shop>,
     *     shopFilterStates: Collection<int, State>,
     *     shopFilterCitiesByState: array<int|string, list<array{id: int, name: string}>>,
     *     title: string,
     *     breadcrumbs: list<array<string, mixed>>,
     *     repairLocator: ?array{
     *         category: RepairCategory,
     *         carName: string,
     *         buttonLabel: string,
     *         states: Collection<int, State>,
     *         citiesByState: array<int, list<array{id: int, name: string}>>,
     *         defaultStateId: ?int,
     *     },
     *     relatedProducts: Collection<int, Part>,
     *     telegramTitle: string,
     *     telegramUrl: string,
     *     signupUrl: string,
     * }
     */
    public function getProductPageData(Company $company, Car $car, CarModel $model, Part $part): array
    {
        $car->description = $this->sanitizeDescription($car->description, $company, $car, $model);
        $part->description = $this->sanitizeDescription($part->description, $company, $car, $model);

        $repairCards = $this->buildRepairCards($part, $company);
        $shops = $this->loadShopsForPart($part, $car->company_id);

        $shops->each(function (Shop $shop) use ($company, $car, $model): void {
            $shop->description = $this->sanitizeDescription($shop->description, $company, $car, $model);
            ShopImageUrlBuilder::attachShopMedia($shop);
        });

        $car->name = strtoupper($car->name);

        $title = $this->buildTitle($part, $company, $car, $model);
        $metaLabel = $part->name.' '.$company->name.' '.$car->name.' '.CarModelLabel::display($model);
        $telegramCta = $this->buildTelegramCta($company, $car);
        $relatedProducts = $this->loadRelatedProducts($company, $car, $model, $part);

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
            'part' => $part,
            'repairCards' => $repairCards,
            'shops' => $shops,
            'shopFilterStates' => $this->shopFilterStates($shops),
            'shopFilterCitiesByState' => $this->shopFilterCitiesByState($shops),
            'title' => $title,
            'metaDescription' => MetaDescription::product($metaLabel),
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $company,
                car: $car,
                model: $model,
                part: $part,
                terminalLabel: $title,
            ),
            'repairLocators' => $this->buildRepairLocatorContext($part, $car),
            'relatedProducts' => $relatedProducts,
            'telegramTitle' => $telegramCta['title'],
            'telegramUrl' => $telegramCta['url'],
            'telegramName' => $telegramCta['name'],
            'signupUrl' => route('page.show', 'register'),
        ];
    }

    /**
     * @return array{title: string, url: string}
     */
    private function buildTelegramCta(Company $company, Car $car): array
    {
        return [
            'name' => $company->name.' '.$car->name,
            'title' => 'به گروه تلگرام '.$company->name.' '.$car->name.' سواران بپیوندید',
            'url' => $company->links->firstWhere('link_type', LinkType::Telegram)?->name
                ?? 'https://t.me/' . 'PartsMallClub',
        ];
    }

    /**
     * @return ?array{
     *     category: RepairCategory,
     *     carName: string,
     *     buttonLabel: string,
     *     states: Collection<int, State>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     defaultStateId: ?int,
     * }
     */
    private function buildRepairLocatorContext(Part $part, Car $car): ?array
    {
        $categories = $part->repairCategories;

        if ($categories === null || count($categories) <= 0) {
            return null;
        }

        $location = $this->locationFilterData();

        $repairLocators = [];
        foreach ($categories as $category) {
            $repairLocators[] = [
                'category' => $category,
                'carName' => $car->name,
                'buttonLabel' => "مشاهده خدمات {$category->name} {$car->name} در محدوده شما",
                'states' => $location['states'],
                'citiesByState' => $location['citiesByState'],
                'defaultStateId' => null,

            ];
        }

        return $repairLocators;
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
            'states' => $this->states(),
            'citiesByState' => $this->citiesGroupedByState(),
        ];
    }

    /** @return Collection<int, State> */
    private function states(): Collection
    {
        /** @var Collection<int, State> $states */
        $states = $this->rememberFilterData('product:states', fn (): Collection => State::query()
            ->orderBy('name')
            ->get(['id', 'name']), fn (mixed $value): bool => $value instanceof Collection);

        return $states;
    }

    /**
     * @return array<int, list<array{id: int, name: string}>>
     */
    private function citiesGroupedByState(): array
    {
        /** @var array<int, list<array{id: int, name: string}>> $cities */
        $cities = $this->rememberFilterData('product:cities-by-state', fn (): array => City::query()
            ->orderBy('name')
            ->get(['id', 'name', 'state_id'])
            ->groupBy('state_id')
            ->map(fn (Collection $cities) => $cities->map(fn (City $city) => [
                'id' => $city->id,
                'name' => $city->name,
            ])->values()->all())
            ->all(), fn (mixed $value): bool => is_array($value));

        return $cities;
    }

    private function sanitizeDescription(?string $description, Company $company, Car $car, CarModel $model): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['ظظظ', 'rn', 'ططط', 'ممم'],
            [$company->name, '', $car->name, CarModelLabel::display($model)],
            $description,
        );
    }

    private function buildTitle(Part $part, Company $company, Car $car, CarModel $model): string
    {
        $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;

        return $part->name.' '.$company->name.' '.$car->name.' '.$modelName;
    }

    /** @return Collection<int, Part> */
    private function loadRelatedProducts(Company $company, Car $car, CarModel $model, Part $part): Collection
    {
        return Part::query()
            ->with('partsCategory:id,name')
            ->whereKeyNot($part->id)
            ->orderBy('id')
            ->limit(self::RELATED_PRODUCTS_LIMIT)
            ->get(['id', 'name', 'slug', 'parts_category_id'])
            ->each(function (Part $related) use ($company, $car, $model): void {
                $related->setAttribute('title', $this->buildTitle($related, $company, $car, $model));
                $related->setAttribute('url', route('product.show', [
                    'company' => $company->slug,
                    'car' => $car->slug,
                    'model' => $model->slug,
                    'part' => $related->slug,
                ]));
            });
    }

    /**
     * @return list<array{type: string, cost: ?int, wage_name: ?string}>
     */
    private function buildRepairCards(Part $part, Company $company): array
    {
        $cards = [];
        $wages = $part->wages->values();
        $categories = $part->repairCategories->values();

        foreach ($wages->take(3) as $index => $wage) {
            $cards[] = [
                'type' => $categories->get($index)?->name,
                'cost' => $wage
                    ? (int) (($wage->variable * ($wage->coefficient ?? 1) * $company->wage_strike) * 100000)
                    : null,
                'wage_name' => $wage?->name,
            ];
        }

        return $cards;
    }

    /** @return Collection<int, Shop> */
    private function loadShopsForPart(Part $part, int $company_id): Collection
    {
        // KIA (1) / Hyundai (2) have curated lists; all shops must match the company.
        $shopIds = match ($company_id) {
            1 => [1, 2, 3, 411, 412], // KIA
            2 => [1, 2, 3, 411, 413], // Hyundai
            default => null,
        };

        $query = $shopIds === null
            ? fn () => Shop::query()
            : fn () => Shop::whereIn('id', $shopIds);

        $query = fn () => $this->applyShopListingConstraints($query())
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company_id));

        $shops = $query()
            ->whereHas('parts', fn ($q) => $q->whereKey($part->id))
            ->get();

        if ($shops->isEmpty() && $company_id) {
            $shops = $query()
                ->whereHas('images', fn ($q) => $q->where('type', ImageType::Logo))
                ->get();
        }

        return $this->pinPriorityShopsForCompany($shops, $company_id);
    }

    /**
     * For companies linked to shop 409, pin eligible shops 409, 4, and 6 at the top.
     *
     * @param  Collection<int, Shop>  $shops
     * @return Collection<int, Shop>
     */
    private function pinPriorityShopsForCompany(Collection $shops, int $company_id): Collection
    {
        $anchorShopId = 409;
        $pinnedIds = [409, 4, 6];

        $companyUsesAnchorShop = Shop::query()
            ->whereKey($anchorShopId)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company_id))
            ->exists();

        if (! $companyUsesAnchorShop) {
            return $shops;
        }

        $pinned = Shop::query()
            ->whereIn('id', $pinnedIds)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company_id))
            ->with($this->shopListingRelations())
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating')
            ->get()
            ->sortBy(fn (Shop $shop): int|false => array_search($shop->id, $pinnedIds, true))
            ->values();

        $rest = $shops
            ->reject(fn (Shop $shop): bool => in_array($shop->id, $pinnedIds, true))
            ->values();

        return $pinned->concat($rest);
    }

    private function applyShopListingConstraints(Builder $query): Builder
    {
        return $query
            ->visibleUnderProduct()
            ->ordered()
            ->with($this->shopListingRelations())
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating');
    }

    /** @return array<string, mixed> */
    private function shopListingRelations(): array
    {
        return [
            'phones:id,shop_id,phone_number,type',
            'links:id,shop_id,link_type,name',
            'city.state:id,name',
            'images' => fn ($query) => $query
                ->select(['id', 'shop_id', 'type', 'path'])
                ->whereIn('type', [ImageType::Logo, ImageType::Cover]),
        ];
    }

    private function rememberFilterData(string $key, callable $callback, ?callable $isValid = null): mixed
    {
        if (app()->environment('testing')) {
            return $callback();
        }

        return SafeCache::remember($key, self::FILTER_CACHE_TTL, $callback, $isValid, [
            ContentCacheTag::PRODUCT,
        ]);
    }

    /** @return Collection<int, State> */
    private function shopFilterStates(Collection $shops): Collection
    {
        return $shops
            ->filter(fn (Shop $shop): bool => $shop->city_id !== null)
            ->map(fn (Shop $shop) => $shop->city?->state)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    /**
     * @return array<int|string, list<array{id: int, name: string}>>
     */
    private function shopFilterCitiesByState(Collection $shops): array
    {
        return $shops
            ->filter(fn (Shop $shop): bool => $shop->city_id !== null && $shop->city !== null)
            ->map(fn (Shop $shop) => $shop->city)
            ->unique('id')
            ->groupBy('state_id')
            ->map(fn (Collection $cities) => $cities
                ->sortBy('name')
                ->values()
                ->map(fn (City $city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                ])
                ->all())
            ->all();
    }
}
