<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Services\Search\SearchIntentDetector;
use App\Support\CarModelLabel;
use App\Support\CatalogUrls;
use App\Support\Search\SearchTextNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SearchService
{
    private const PER_GROUP = 8;

    public function __construct(
        private readonly SearchIntentDetector $intentDetector,
        private readonly SearchTextNormalizer $normalizer,
    ) {}

    /**
     * @return array{
     *     groups: Collection<int, array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}>,
     *     total: int,
     * }
     */
    public function search(?string $query): array
    {
        $query = trim((string) $query);
        $intent = $this->intentDetector->detect($query);

        if ($query === '') {
            return [
                'groups' => collect(),
                'total' => 0,
            ];
        }

        $groups = match ($intent['type']) {
            'exact_vehicle' => collect([
                $this->vehicleGroupFromCars(collect([$intent['car']])->filter()),
            ]),
            'vehicle_context' => collect([
                $this->contextualVehicleGroup($query),
            ]),
            'exact_company' => collect([
                $this->vehicleGroupFromCompanies(collect([$intent['company']])->filter()),
            ]),
            'exact_part' => collect([
                $this->partsGroupFromParts($intent['parts']),
            ]),
            'mixed_part_vehicle' => collect([
                $this->productGroupForPartVehicle($intent['parts'], $intent['car']),
            ]),
            default => $this->genericGroups($query),
        };

        $groups = $groups->filter(fn (?array $group): bool => $group !== null && $group['total'] > 0)->values();

        return [
            'groups' => $groups,
            'total' => $groups->sum('total'),
        ];
    }

    /**
     * @return Collection<int, array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}>
     */
    private function genericGroups(string $query): Collection
    {
        $groups = collect([
            $this->storeGroup($query),
            $this->buildGroup('repair_shops', 'تعمیرگاه‌ها', RepairShop::class, $query, ['state', 'repairCategories'], searchableFields: ['name']),
            $this->vehicleGroup($query),
            $this->buildGroup('parts', 'قطعات', Part::class, $query, ['partsCategory'], ['name', 'description']),
        ])->filter(fn (?array $group): bool => $group !== null && $group['total'] > 0)->values();

        return $groups;
    }

    /**
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function storeGroup(string $query): array
    {
        $shops = Shop::search($query)
            ->query(fn ($builder) => $builder->with('state')->where('name', 'like', '%'.$query.'%'))
            ->paginate(self::PER_GROUP);

        $representations = Representation::search($query)
            ->query(fn ($builder) => $builder->with(['company', 'state', 'city'])->where('name', 'like', '%'.$query.'%'))
            ->paginate(self::PER_GROUP);

        $items = collect($shops->items())
            ->map(fn (Shop $shop): array => $this->mapResult($shop, 'shops'))
            ->concat(collect($representations->items())->map(fn (Representation $representation): array => $this->mapResult($representation, 'representations')))
            ->take(self::PER_GROUP)
            ->values();

        return [
            'key' => 'stores',
            'label' => 'فروشگاه‌ها و نمایندگی‌ها',
            'total' => $items->count(),
            'items' => $items,
        ];
    }

    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $with
     * @param  list<string>  $searchableFields
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function buildGroup(string $key, string $label, string $model, string $query, array $with = [], array $searchableFields = []): array
    {
        $results = $model::search($query)
            ->query(function ($builder) use ($with, $searchableFields, $query) {
                if ($with !== []) {
                    $builder->with($with);
                }

                if ($searchableFields !== []) {
                    $builder->where(function ($inner) use ($searchableFields, $query): void {
                        foreach ($searchableFields as $field) {
                            $inner->orWhere($field, 'like', '%'.$query.'%');
                        }
                    });
                }

                return $builder;
            })
            ->paginate(self::PER_GROUP);

        return [
            'key' => $key,
            'label' => $label,
            'total' => $results->count(),
            'items' => collect($results->items())->map(fn (Model $result): array => $this->mapResult($result, $key)),
        ];
    }

    /**
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function vehicleGroup(string $query): array
    {
        $companies = Company::search($query)->query(fn ($builder) => $builder->with('cars'))->paginate(self::PER_GROUP);
        $cars = Car::search($query)->query(fn ($builder) => $builder->with(['company']))->paginate(self::PER_GROUP);
        $items = collect($companies->items())
            ->map(fn (Company $company): array => $this->mapResult($company, 'companies'))
            ->concat(collect($cars->items())->map(fn (Car $car): array => $this->mapResult($car, 'cars')))
            ->concat($this->contextualVehicleItems($query))
            ->unique(fn (array $item): string => $item['type'].'|'.$item['title'].'|'.$item['url'])
            ->take(self::PER_GROUP)
            ->values();

        return [
            'key' => 'vehicles',
            'label' => 'خودرو',
            'total' => $companies->count() + $cars->count(),
            'items' => $items,
        ];
    }

    /**
     * @return Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>
     */
    private function contextualVehicleItems(string $query): Collection
    {
        $queryTokens = $this->normalizer->tokens($query);

        if ($queryTokens === []) {
            return collect();
        }

        $companies = Company::query()
            ->with('cars')
            ->get()
            ->filter(fn (Company $company): bool => $this->containsOrderedTokens($queryTokens, $this->normalizer->tokens($company->name)));

        $cars = Car::query()
            ->with(['company', 'models'])
            ->get()
            ->filter(fn (Car $car): bool => $this->containsAnyOrderedTokenSet($queryTokens, [
                $this->normalizer->tokens($car->name),
                $this->normalizer->tokens($car->slug),
                $this->normalizer->tokens($car->company?->name.' '.$car->name),
                $this->normalizer->tokens($car->company?->name.' '.$car->slug),
            ]));

        //TODO: Start from here
        return $companies
            ->map(fn (Company $company): array => $this->mapResult($company, 'companies'))
            ->concat($cars->map(fn (Car $car): array => $this->mapResult($car, 'cars')))
            ->values();
    }

    /**
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function contextualVehicleGroup(string $query): array
    {
        $items = $this->contextualVehicleItems($query);

        return [
            'key' => 'vehicles',
            'label' => 'خودرو',
            'total' => $items->count(),
            'items' => $items->take(self::PER_GROUP)->values(),
        ];
    }

    /**
     * @param  list<string>  $queryTokens
     * @param  list<string>  $tokens
     */
    private function containsOrderedTokens(array $queryTokens, array $tokens): bool
    {
        if ($queryTokens === [] || $tokens === [] || count($tokens) > count($queryTokens)) {
            return false;
        }

        $tokenCount = count($tokens);
        $lastStart = count($queryTokens) - $tokenCount;

        for ($start = 0; $start <= $lastStart; $start++) {
            if (array_slice($queryTokens, $start, $tokenCount) === $tokens) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $queryTokens
     * @param  list<list<string>>  $tokenSets
     */
    private function containsAnyOrderedTokenSet(array $queryTokens, array $tokenSets): bool
    {
        foreach ($tokenSets as $tokens) {
            if ($this->containsOrderedTokens($queryTokens, $tokens)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, Company>  $companies
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function vehicleGroupFromCompanies(Collection $companies): array
    {
        return [
            'key' => 'vehicles',
            'label' => 'خودرو',
            'total' => $companies->count(),
            'items' => $companies->map(fn (Company $company): array => $this->mapResult($company, 'companies'))->values(),
        ];
    }

    /**
     * @param  Collection<int, Car>  $cars
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function vehicleGroupFromCars(Collection $cars): array
    {
        return [
            'key' => 'vehicles',
            'label' => 'خودرو',
            'total' => $cars->count(),
            'items' => $cars->map(fn (Car $car): array => $this->mapResult($car, 'cars'))->values(),
        ];
    }

    /**
     * @param  Collection<int, Part>  $parts
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function partsGroupFromParts(Collection $parts): array
    {
        return [
            'key' => 'parts',
            'label' => 'قطعات',
            'total' => $parts->count(),
            'items' => $parts->take(self::PER_GROUP)->map(fn (Part $part): array => $this->mapResult($part, 'parts'))->values(),
        ];
    }

    /**
     * @param  Collection<int, Part>  $parts
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function productGroupForPartVehicle(Collection $parts, ?Car $car): array
    {
        if (! $car || $parts->isEmpty()) {
            return $this->emptyGroup('products', 'محصولات');
        }

        $car->loadMissing(['company', 'models']);
        $items = $car->models
            ->flatMap(fn (CarModel $model) => $parts->take(3)->map(fn (Part $part): array => $this->mapProduct($model, $car, $part)))
            ->take(self::PER_GROUP)
            ->values();

        return [
            'key' => 'products',
            'label' => 'محصولات',
            'total' => $items->count(),
            'items' => $items,
        ];
    }

    /**
     * @return array{title: string, subtitle: ?string, url: string, type: string}
     */
    private function mapResult(Model $result, string $key): array
    {
        return match (true) {
            $result instanceof Part => [
                'title' => $result->name,
                'subtitle' => $result->partsCategory?->name,
                'url' => route('part.show', $result->slug),
                'type' => 'قطعه',
            ],
            $result instanceof Shop => [
                'title' => $result->name,
                'subtitle' => $result->secondary_name ?: $result->state?->name,
                'url' => route('shop.profile', $result->slug),
                'type' => 'فروشگاه',
            ],
            $result instanceof RepairShop => [
                'title' => $result->name,
                'subtitle' => $result->work_description ?: $result->state?->name,
                'url' => route('repair-shop.profile', $result->slug),
                'type' => 'تعمیرگاه',
            ],
            $result instanceof Representation => [
                'title' => $result->name,
                'subtitle' => $result->company?->name ?: $result->city?->name,
                'url' => route('representation.profile', $result->slug),
                'type' => 'نمایندگی',
            ],
            $result instanceof Company => [
                'title' => $result->name,
                'subtitle' => $result->country,
                'url' => route('cars.index', ['company' => $result->slug]),
                'type' => 'کمپانی',
            ],
            $result instanceof Car => [
                'title' => $result->name,
                'subtitle' => $result->company?->name,
                'url' => $result->company
                    ? route('models.index', ['company' => $result->company->slug, 'car' => $result->slug])
                    : CatalogUrls::companies(),
                'type' => 'خودرو',
            ],
            // $result instanceof CarModel => $this->mapCarModel($result),
            default => [
                'title' => (string) ($result->getAttribute('name') ?? ''),
                'subtitle' => null,
                'url' => route('search.index', ['q' => $result->getAttribute('name')]),
                'type' => $key,
            ],
        };
    }

    /**
     * @return array{title: string, subtitle: ?string, url: string, type: string}
     */
    private function mapProduct(CarModel $model, Car $car, ?Part $part = null): array
    {
        $car->loadMissing('company');
        $company = $car->company;
        $title = collect([$company?->name, $car->name, CarModelLabel::display($model)])->filter()->implode(' ');

        if ($part) {
            $title .= ' '.$part->name;
        }

        return [
            'title' => $title,
            'subtitle' => $part?->partsCategory?->name,
            'url' => $company && $part
                ? route('product.show', [
                    'company' => $company->slug,
                    'car' => $car->slug,
                    'model' => $model->slug,
                    'part' => $part->slug,
                ])
                : ($company ? CatalogUrls::parts($company->slug, $car->slug, $model->slug) : CatalogUrls::companies()),
            'type' => 'محصول',
        ];
    }

    /**
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function emptyGroup(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'total' => 0,
            'items' => collect(),
        ];
    }
}
