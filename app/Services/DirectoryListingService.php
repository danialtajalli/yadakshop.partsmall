<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\City;
use App\Models\Company;
use App\Models\RepairCategory;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Models\State;
use App\Support\PageTitle;
use App\Support\Pagination;
use App\Support\ShopImageUrlBuilder;
use __PHP_Incomplete_Class;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DirectoryListingService
{
    private const PER_PAGE = 24;
    private const FILTER_CACHE_TTL = 86400;

    /**
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     breadcrumbs: list<array{label: string, url?: string, active?: bool, emphasized?: bool}>,
     *     states: Collection<int, State>,
     *     cities: Collection<int, City>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     specializations: Collection<int, RepairCategory>,
     *     filters: array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int},
     *     showSpecializationFilter: bool,
     *     shopCompanies: Collection<int, Company>,
     *     filterCompany: ?Company,
     * }
     */
    public function getShopListing(Request $request, ?Company $company = null): array
    {
        $filters = $this->filtersFromRequest($request);

        $query = Shop::query()
            ->with([
                'city.state:id,name',
                'images' => fn ($relation) => $relation
                    ->select(['id', 'shop_id', 'type', 'path'])
                    ->whereIn('type', [ImageType::Logo, ImageType::Cover]),
            ])
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating');

        if ($company !== null) {
            $query->whereHas(
                'companies',
                fn (Builder $relation) => $relation->whereKey($company->id),
            );
        }

        $this->applyCommonFilters($query, $filters, [
            'name',
            'secondary_name',
            'address',
            'person_responsible_name',
        ]);

        $listings = $query
            ->whereHas('images', fn ($q) => $q->where('type', ImageType::Logo))
            ->paginate(self::PER_PAGE)
            ->appends($this->paginationQuery($filters));

        $listings->getCollection()->each(fn (Shop $shop) => $this->attachImageUrls($shop, 'shop'));

        $title = $company !== null
            ? 'فروشگاه‌های '.$company->name
            : 'فروشگاه‌های لوازم یدکی';

        return $this->buildPageData(
            listings: $listings,
            type: 'shop',
            title: $title,
            filters: $filters,
            showSpecializationFilter: false,
            filterCompany: $company,
        );
    }

    /**
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     breadcrumbs: list<array{label: string, url?: string, active?: bool, emphasized?: bool}>,
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
            ->with([
                'city.state:id,name',
                'images' => fn ($relation) => $relation
                    ->select(['id', 'repair_shop_id', 'type', 'path'])
                    ->whereIn('type', [ImageType::Logo, ImageType::Cover]),
                'repairCategories:id,name',
            ]);

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
            ->appends($this->paginationQuery($filters));

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
     *     breadcrumbs: list<array{label: string, url?: string, active?: bool, emphasized?: bool}>,
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
            ->with([
                'city.state:id,name',
                'company:id,name',
            ]);

        $this->applyCommonFilters($query, $filters, [
            'name',
            'address',
            'responsible_person_name',
            'service_type',
            'work_fields',
        ], searchCompanyName: true);

        $listings = $query
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->appends($this->paginationQuery($filters));

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
            $query->whereHas(
                'city',
                fn (Builder $relation) => $relation->where('state_id', $filters['state_id']),
            );
        }

        if ($filters['city_id']) {
            $query->where('city_id', $filters['city_id']);
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
     * @return array<string, int|string>
     */
    private function paginationQuery(array $filters): array
    {
        return array_filter([
            'q' => $filters['q'],
            'state_id' => $filters['state_id'],
            'city_id' => $filters['city_id'],
            'specialization_id' => $filters['specialization_id'],
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int}  $filters
     * @return array{
     *     listings: LengthAwarePaginator,
     *     type: string,
     *     title: string,
     *     breadcrumbs: list<array{label: string, url?: string, active?: bool, emphasized?: bool}>,
     *     states: Collection<int, State>,
     *     cities: Collection<int, City>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     specializations: Collection<int, RepairCategory>,
     *     filters: array{q: ?string, state_id: ?int, city_id: ?int, specialization_id: ?int},
     *     showSpecializationFilter: bool,
     *     shopCompanies: Collection<int, Company>,
     *     filterCompany: ?Company,
     * }
     */
    private function buildPageData(
        LengthAwarePaginator $listings,
        string $type,
        string $title,
        array $filters,
        bool $showSpecializationFilter,
        ?Company $filterCompany = null,
    ): array {
        $states = $this->states();
        $cities = $filters['state_id']
            ? City::query()->where('state_id', $filters['state_id'])->orderBy('name')->get(['id', 'name', 'state_id'])
            : collect();

        $breadcrumbTrail = [
            ['label' => 'خانه', 'url' => url('/')],
        ];

        if ($type === 'shop' && $filterCompany !== null) {
            $breadcrumbTrail[] = [
                'label' => 'فروشگاه‌های لوازم یدکی',
                'url' => route('shops.index'),
            ];
            $breadcrumbTrail[] = ['label' => $filterCompany->name];
        } else {
            $breadcrumbTrail[] = ['label' => $title];
        }

        $shopCompanies = $type === 'shop' ? $this->shopFilterCompanies() : collect();

        if ($type === 'shop' && $filterCompany !== null) {
            $matched = $shopCompanies->firstWhere('id', $filterCompany->id);
            $filterCompany->logo_url = $matched?->logo_url;
        }

        return [
            'listings' => $listings,
            'type' => $type,
            'title' => PageTitle::appendPageNumber($title, $listings->currentPage()),
            'breadcrumbs' => Pagination::buildBreadcrumbs(
                $breadcrumbTrail,
                $listings->currentPage(),
                Pagination::pageUrl($listings, 1),
            ),
            'states' => $states,
            'cities' => $cities,
            'citiesByState' => $this->citiesGroupedByState(),
            'specializations' => $showSpecializationFilter
                ? $this->repairCategories()
                : collect(),
            'filters' => $filters,
            'showSpecializationFilter' => $showSpecializationFilter,
            'shopCompanies' => $shopCompanies,
            'filterCompany' => $type === 'shop' ? $filterCompany : null,
        ];
    }

    /**
     * @return Collection<int, Company>
     */
    private function shopFilterCompanies(): Collection
    {
        /** @var Collection<int, Company> $companies */
        $companies = $this->rememberFilterData('directory-listing:shop-filter-companies', function (): Collection {
            $companies = Company::query()
                ->with([
                    'images' => fn ($relation) => $relation
                        ->select(['id', 'company_id', 'type', 'path'])
                        ->where('type', ImageType::Logo),
                ])
                ->whereHas('shops')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            $companies->each(function (Company $company): void {
                $logo = $company->images->firstWhere('type', ImageType::Logo);

                $company->logo_url = $logo
                    ? ShopImageUrlBuilder::companyLogoUrl($logo)
                    : null;
            });

            return $companies;
        }, fn (mixed $value): bool => $value instanceof Collection);

        return $companies->values();
    }

    /**
     * @return array<int, list<array{id: int, name: string}>>
     */
    private function citiesGroupedByState(): array
    {
        /** @var array<int, list<array{id: int, name: string}>> $cities */
        $cities = $this->rememberFilterData('directory-listing:cities-by-state', function (): array {
            return City::query()
                ->orderBy('name')
                ->get(['id', 'name', 'state_id'])
                ->groupBy('state_id')
                ->map(fn (Collection $cities) => $cities->map(fn (City $city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                ])->values()->all())
                ->all();
        }, fn (mixed $value): bool => is_array($value));

        return $cities;
    }

    /**
     * @return Collection<int, State>
     */
    private function states(): Collection
    {
        /** @var Collection<int, State> $states */
        $states = $this->rememberFilterData('directory-listing:states', fn (): Collection => State::query()
            ->orderBy('name')
            ->get(['id', 'name']), fn (mixed $value): bool => $value instanceof Collection);

        return $states;
    }

    /**
     * @return Collection<int, RepairCategory>
     */
    private function repairCategories(): Collection
    {
        /** @var Collection<int, RepairCategory> $categories */
        $categories = $this->rememberFilterData('directory-listing:repair-categories', fn (): Collection => RepairCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']), fn (mixed $value): bool => $value instanceof Collection);

        return $categories;
    }

    private function rememberFilterData(string $key, callable $callback, ?callable $isValid = null): mixed
    {
        if (app()->environment('testing')) {
            return $callback();
        }

        $cached = Cache::get($key);

        if (($cached !== null || Cache::has($key)) && $this->isValidCachedFilterData($cached, $isValid)) {
            return $cached;
        }

        Cache::forget($key);

        $value = $callback();
        Cache::put($key, $value, self::FILTER_CACHE_TTL);

        return $value;
    }

    private function isValidCachedFilterData(mixed $value, ?callable $isValid): bool
    {
        if ($value instanceof __PHP_Incomplete_Class) {
            return false;
        }

        return $isValid === null || $isValid($value);
    }

    private function attachImageUrls(Model $model, string $modelType): void
    {
        if ($model instanceof RepairShop) {
            ShopImageUrlBuilder::attachRepairShopMedia($model);

            return;
        }

        ShopImageUrlBuilder::attachShopMedia($model, $modelType);
    }
}
