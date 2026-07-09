<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\City;
use App\Models\Image;
use App\Models\RepairCategory;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Models\State;
use App\Support\PageTitle;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DirectoryListingService
{
    private const PER_PAGE = 24;

    /**
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     states: Collection<int, State>,
     *     cities: Collection<int, City>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     specializations: Collection<int, RepairCategory>,
     *     filters: array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int},
     *     showSpecializationFilter: bool,
     * }
     */
    public function getShopListing(Request $request): array
    {
        $filters = $this->filtersFromRequest($request);

        $query = Shop::query()
            ->with(['state', 'images'])
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating');

        $this->applyCommonFilters($query, $filters, [
            'name',
            'secondary_name',
            'address',
            'person_responsible_name',
        ]);

        $listings = $query
        ->whereHas('images', fn ($q) => $q->where('type', ImageType::Logo))
        ->paginate(self::PER_PAGE)
        ->withQueryString();

        $listings->getCollection()->each(fn (Shop $shop) => $this->attachImageUrls($shop, 'shop'));

        return $this->buildPageData(
            listings: $listings,
            type: 'shop',
            title: 'فروشگاه‌های لوازم یدکی',
            filters: $filters,
            showSpecializationFilter: false,
        );
    }

    /**
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     states: Collection<int, State>,
     *     cities: Collection<int, City>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     specializations: Collection<int, RepairCategory>,
     *     filters: array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int},
     *     showSpecializationFilter: bool,
     * }
     */
    public function getRepairShopListing(Request $request): array
    {
        $filters = $this->filtersFromRequest($request);

        $query = RepairShop::query()
            ->with(['state', 'images', 'repairCategories']);

        $this->applyCommonFilters($query, $filters, [
            'name',
            'address',
            'responsible_person_name',
            'work_description',
        ]);

        if ($filters['specialization_id']) {
            $query->whereHas(
                'repairCategories',
                fn (Builder $relation) => $relation->whereKey($filters['specialization_id']),
            );
        }

        $listings = $query
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $listings->getCollection()->each(fn (RepairShop $shop) => $this->attachImageUrls($shop, 'repair_shop'));

        return $this->buildPageData(
            listings: $listings,
            type: 'repair_shop',
            title: 'تعمیرگاه‌ها',
            filters: $filters,
            showSpecializationFilter: true,
        );
    }

    /**
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     states: Collection<int, State>,
     *     cities: Collection<int, City>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     specializations: Collection<int, RepairCategory>,
     *     filters: array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int},
     *     showSpecializationFilter: bool,
     * }
     */
    public function getRepresentationListing(Request $request): array
    {
        $filters = $this->filtersFromRequest($request);

        $query = Representation::query()
            ->with(['state', 'city', 'company']);

        $this->applyCommonFilters($query, $filters, [
            'name',
            'address',
            'responsible_person_name',
            'service_type',
            'work_fields',
        ], matchCityById: true, searchCompanyName: true);

        $listings = $query
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $listings->getCollection()->each(function (Representation $representation): void {
            ShopImageUrlBuilder::attachRepresentationMedia($representation);
        });

        return $this->buildPageData(
            listings: $listings,
            type: 'representation',
            title: 'نمایندگی‌ها',
            filters: $filters,
            showSpecializationFilter: false,
        );
    }

    /**
     * @param  list<string>  $searchColumns
     */
    private function applyCommonFilters(
        Builder $query,
        array $filters,
        array $searchColumns,
        bool $matchCityById = false,
        bool $searchCompanyName = false,
    ): void {
        if ($filters['q']) {
            $search = $filters['q'];

            $query->where(function (Builder $builder) use ($search, $searchColumns, $searchCompanyName): void {
                foreach ($searchColumns as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }

                if ($searchCompanyName) {
                    $builder->orWhereHas(
                        'company',
                        fn (Builder $relation) => $relation->where('name', 'like', '%'.$search.'%'),
                    );
                }
            });
        }

        if ($filters['state_id']) {
            $query->where('state_id', $filters['state_id']);
        }

        if ($filters['city_id']) {
            if ($matchCityById) {
                $query->where('city_id', $filters['city_id']);
            } else {
                $city = City::query()->find($filters['city_id']);

                if ($city) {
                    $query
                        ->where('state_id', $city->state_id)
                        ->where('address', 'like', '%'.$city->name.'%');
                }
            }
        }
    }

    /**
     * @return array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int}
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => $request->string('q')->trim()->toString() ?: null,
            'state_id' => $request->integer('state_id') ?: null,
            'city_id' => $request->integer('city_id') ?: null,
            'specialization_id' => $request->integer('specialization_id') ?: null,
        ];
    }

    /**
     * @param  array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int}  $filters
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     states: Collection<int, State>,
     *     cities: Collection<int, City>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     specializations: Collection<int, RepairCategory>,
     *     filters: array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int},
     *     showSpecializationFilter: bool,
     * }
     */
    private function buildPageData(
        LengthAwarePaginator $listings,
        string $type,
        string $title,
        array $filters,
        bool $showSpecializationFilter,
    ): array {
        $states = State::query()->orderBy('name')->get(['id', 'name']);
        $cities = $filters['state_id']
            ? City::query()->where('state_id', $filters['state_id'])->orderBy('name')->get(['id', 'name', 'state_id'])
            : collect();

        return [
            'listings' => $listings,
            'type' => $type,
            'title' => PageTitle::appendPageNumber($title, $listings->currentPage()),
            'states' => $states,
            'cities' => $cities,
            'citiesByState' => $this->citiesGroupedByState(),
            'specializations' => $showSpecializationFilter
                ? RepairCategory::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'filters' => $filters,
            'showSpecializationFilter' => $showSpecializationFilter,
        ];
    }

    /**
     * @return array<int, list<array{id: int, name: string}>>
     */
    private function citiesGroupedByState(): array
    {
        return City::query()
            ->orderBy('name')
            ->get(['id', 'name', 'state_id'])
            ->groupBy('state_id')
            ->map(fn (Collection $cities) => $cities->map(fn (City $city) => [
                'id' => $city->id,
                'name' => $city->name,
            ])->values()->all())
            ->all();
    }

    private function attachImageUrls(Model $model, string $modelType): void
    {
        if ($model instanceof RepairShop) {
            ShopImageUrlBuilder::attachRepairShopMedia($model);
        } else {
            $model->images->each(function (Image $image) use ($model, $modelType): void {
                if (! in_array($image->type, [ImageType::Cover, ImageType::Logo], true)) {
                    return;
                }

                $property = $image->type === ImageType::Cover ? 'cover' : 'logo';
                $model->{$property} = str_replace(
                    ['{model_type}', '{image_type}', '{model_id}', '{image_name}'],
                    [$modelType, $image->type->value, (string) $model->id, $image->path],
                    config('partsmall.image_url'),
                );
            });
        }
    }
}
