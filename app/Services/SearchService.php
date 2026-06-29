<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Support\CarModelLabel;
use App\Support\CatalogUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SearchService
{
    private const PER_GROUP = 8;

    /**
     * @return array{
     *     groups: Collection<int, array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}>,
     *     total: int,
     * }
     */
    public function search(?string $query): array
    {
        $query = trim((string) $query);

        if ($query === '') {
            return [
                'groups' => collect(),
                'total' => 0,
            ];
        }

        $groups = collect([
            $this->buildGroup('parts', 'قطعات', Part::class, $query, ['partsCategory']),
            $this->buildGroup('shops', 'فروشگاه‌ها', Shop::class, $query, ['state']),
            $this->buildGroup('repair_shops', 'تعمیرگاه‌ها', RepairShop::class, $query, ['state', 'repairCategories']),
            $this->buildGroup('representations', 'نمایندگی‌ها', Representation::class, $query, ['company', 'state', 'city']),
            $this->buildGroup('companies', 'کمپانی‌ها', Company::class, $query, ['cars']),
            $this->buildGroup('cars', 'خودروها', Car::class, $query, ['company', 'models']),
            $this->buildGroup('car_models', 'مدل‌ها', CarModel::class, $query, ['cars.company', 'category']),
        ])->filter(fn (array $group): bool => $group['total'] > 0)->values();

        return [
            'groups' => $groups,
            'total' => $groups->sum('total'),
        ];
    }

    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $with
     * @return array{key: string, label: string, total: int, items: Collection<int, array{title: string, subtitle: ?string, url: string, type: string}>}
     */
    private function buildGroup(string $key, string $label, string $model, string $query, array $with = []): array
    {
        $results = $model::search($query)
            ->query(fn ($builder) => $with === [] ? $builder : $builder->with($with))
            ->paginate(self::PER_GROUP);

        return [
            'key' => $key,
            'label' => $label,
            'total' => $results->total(),
            'items' => $results->getCollection()->map(fn (Model $result): array => $this->mapResult($result, $key)),
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
            $result instanceof CarModel => $this->mapCarModel($result),
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
    private function mapCarModel(CarModel $model): array
    {
        $car = $model->cars->sortBy('name')->first();
        $company = $car?->company;

        return [
            'title' => CarModelLabel::display($model),
            'subtitle' => collect([$company?->name, $car?->name, $model->category?->name])->filter()->implode(' / ') ?: null,
            'url' => $company && $car
                ? CatalogUrls::parts($company->slug, $car->slug, $model->slug)
                : CatalogUrls::companies(),
            'type' => 'مدل',
        ];
    }
}
