<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Company;
use App\Models\Part;
use App\Models\RepairShop;
use App\Models\Representation;
use App\Models\Shop;
use App\Support\CarModelLabel;
use App\Support\CarModelSort;
use App\Support\MetaDescription;
use App\Support\ModelCategoryLabel;
use App\Support\SafeCache;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Support\Collection;

class HomePageService
{
    private const FEATURED_LIMIT = 17;

    private const CACHE_TTL = 86400;

    /**
     * @return array{
     *     shops: Collection<int, Shop>,
     *     repairShops: Collection<int, RepairShop>,
     *     companies: Collection<int, Company>,
     *     companyPicker: list<array{
     *         slug: string,
     *         name: string,
     *         logo_url: ?string,
     *         cars: list<array{
     *             slug: string,
     *             name: string,
     *             modelCategories: list<array{
     *                 slug: string,
     *                 label: string,
     *                 models: list<array{slug: string, name: string, url: string}>,
     *             }>,
     *         }>,
     *     }>,
     *     vehicleFilter: array{
     *         companies: list<array{slug: string, name: string, logo_url: ?string}>,
     *         carsByCompany: array<string, list<array{slug: string, name: string}>>,
     *         modelsByCar: array<string, list<array{slug: string, name: string, url: string}>>,
     *         parts: list<array{slug: string, name: string}>,
     *     },
     *     representations: Collection<int, Representation>,
     *     bestShops: Collection<int, Shop>,
     *     parts: Collection<int, Part>,
     *     title: string,
     * }
     */
    public function getHomePageData(): array
    {
        $companies = $this->featuredCompanies();
        $companyPicker = $this->buildCompanyPicker($companies);
        $parts = $this->allParts();

        return [
            'shops' => $this->featuredShops(),
            'repairShops' => $this->featuredRepairShops(),
            'companies' => $companies,
            'companyPicker' => $companyPicker,
            'vehicleFilter' => $this->buildVehicleFilter($companyPicker, $parts),
            'representations' => $this->featuredRepresentations(),
            'bestShops' => $this->bestShowcaseShops(),
            'parts' => $parts,
            'partCategoryParts' => $this->partCategoryParts(),
            'title' => 'پارتسمال | مرجع لوازم یدکی خودرو',
            'metaDescription' => MetaDescription::home(),
        ];
    }

    /**
     * @return Collection<int, Company>
     */
    private function featuredCompanies(): Collection
    {
        return collect($this->rememberHomeData('home:companies:v1', function (): array {
            $companies = Company::query()
                ->with([
                    'images' => fn ($query) => $query
                        ->select(['id', 'company_id', 'type', 'path'])
                        ->where('type', ImageType::Logo),
                    'cars' => fn ($query) => $query->select(['id', 'company_id', 'name', 'slug']),
                    'cars.models' => fn ($query) => $query->select(['models.id', 'models.name', 'models.slug', 'models.category_id']),
                    'cars.models.category:id,name,slug',
                ])
                ->orderBy('id')
                ->get(['id', 'name', 'slug']);

            return $companies
                ->map(function (Company $company): array {
                    $logo = $company->images->firstWhere('type', ImageType::Logo);
                    $cars = $company->cars
                        ->sortBy('name')
                        ->values()
                        ->map(function ($car) use ($company): array {
                            $modelCategories = $car->models
                                ->sortBy('name')
                                ->groupBy(fn ($model) => (string) ($model->category_id ?? 0))
                                ->map(function ($models) use ($company, $car): array {
                                    $category = $models->first()->category;
                                    $categorySlug = ModelCategoryLabel::slug($category);

                                    return [
                                        'slug' => $categorySlug,
                                        'label' => ModelCategoryLabel::display($category),
                                        'models' => CarModelSort::prioritize(
                                            $models
                                                ->map(function ($model) use ($company, $car, $categorySlug): array {
                                                    return [
                                                        'slug' => $model->slug,
                                                        'name' => CarModelLabel::display($model),
                                                        'category_slug' => $categorySlug,
                                                        'url' => route('car.parts.vehicle', [
                                                            'company' => $company->slug,
                                                            'car' => $car->slug,
                                                            'model' => $model->slug,
                                                        ]),
                                                    ];
                                                })
                                                ->values(),
                                        ),
                                    ];
                                })
                                ->sortBy(fn (array $category): int => CarModelSort::bucketForCategory($category['slug']))
                                ->values()
                                ->all();

                            return [
                                'slug' => $car->slug,
                                'name' => strtoupper($car->name),
                                'modelCategories' => $modelCategories,
                            ];
                        })
                        ->all();

                    return [
                        'slug' => $company->slug,
                        'name' => $company->name,
                        'logo_url' => $logo
                            ? ShopImageUrlBuilder::buildCompanyLogoUrl('company', $company->id, $logo->path)
                            : null,
                        'has_models' => collect($cars)->contains(fn (array $car): bool => $car['modelCategories'] !== []),
                        'cars' => $cars,
                    ];
                })
                ->all();
        }));
    }

    /**
     * @param  Collection<int, Company>  $companies
     * @return list<array{
     *     slug: string,
     *     name: string,
     *     logo_url: ?string,
     *     cars: list<array{
     *         slug: string,
     *         name: string,
     *         modelCategories: list<array{
     *             slug: string,
     *             label: string,
     *             models: list<array{slug: string, name: string, url: string}>,
     *         }>,
     *     }>,
     * }>
     */
    private function buildCompanyPicker(Collection $companies): array
    {
        return $companies
            ->map(function (array $company): array {
                return [
                    'slug' => $company['slug'],
                    'name' => $company['name'],
                    'logo_url' => $company['logo_url'],
                    'cars' => collect($company['cars'])
                        ->filter(fn (array $car) => $car['modelCategories'] !== [])
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $company) => $company['cars'] !== [])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{
     *     slug: string,
     *     name: string,
     *     cars: list<array{
     *         slug: string,
     *         name: string,
     *         modelCategories: list<array{
     *             slug: string,
     *             models: list<array{slug: string, name: string, url: string, category_slug?: string}>,
     *         }>,
     *     }>,
     * }>  $companyPicker
     * @param  Collection<int, Part>  $parts
     * @return array{
     *     companies: list<array{slug: string, name: string, logo_url: ?string}>,
     *     carsByCompany: array<string, list<array{slug: string, name: string}>>,
     *     modelsByCar: array<string, list<array{slug: string, name: string, url: string}>>,
     *     parts: list<array{slug: string, name: string}>,
     * }
     */
    private function buildVehicleFilter(array $companyPicker, Collection $parts): array
    {
        $companies = [];
        $carsByCompany = [];
        $modelsByCar = [];

        foreach ($companyPicker as $company) {
            $companies[] = [
                'slug' => $company['slug'],
                'name' => $company['name'],
                'logo_url' => $company['logo_url'] ?? null,
            ];

            $carsByCompany[$company['slug']] = [];

            foreach ($company['cars'] as $car) {
                $carsByCompany[$company['slug']][] = [
                    'slug' => $car['slug'],
                    'name' => $car['name'],
                ];

                $models = collect($car['modelCategories'])
                    ->flatMap(function (array $category) {
                        return collect($category['models'])->map(function (array $model) use ($category): array {
                            return [
                                'slug' => $model['slug'],
                                'name' => $model['name'],
                                'url' => $model['url'],
                                'category_slug' => $model['category_slug'] ?? $category['slug'],
                            ];
                        });
                    })
                    ->unique('slug')
                    ->values();

                $modelsByCar[$company['slug'].'|'.$car['slug']] = CarModelSort::prioritize($models);
            }
        }

        return [
            'companies' => $companies,
            'carsByCompany' => $carsByCompany,
            'modelsByCar' => $modelsByCar,
            'parts' => $parts
                ->map(fn (array $part): array => [
                    'slug' => $part['slug'],
                    'name' => $part['name'],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return Collection<int, Shop>
     */
    private function bestShowcaseShops(): Collection
    {
        $ids = config('partsmall.home_best_shop_ids', [1, 2, 3]);

        if ($ids === []) {
            return collect();
        }

        return collect($this->rememberHomeData('home:best-shops:'.md5(json_encode($ids)), function () use ($ids): array {
            return Shop::query()
                ->with([
                    'images' => fn ($query) => $query
                        ->select(['id', 'shop_id', 'type', 'path'])
                        ->where('type', ImageType::Logo),
                ])
                ->whereIn('id', $ids)
                ->get(['id', 'name', 'slug'])
                ->sortBy(fn (Shop $shop) => array_search($shop->id, $ids, true))
                ->values()
                ->map(fn (Shop $shop): array => [
                    'name' => $shop->name,
                    'slug' => $shop->slug,
                    'logo' => $this->shopLogoUrl($shop),
                ])
                ->all();
        }));
    }

    /**
     * @return Collection<int, Shop>
     */
    private function featuredShops(): Collection
    {
        return collect($this->rememberHomeData('home:featured-shops:v1', function (): array {
            return Shop::query()
                ->with([
                    'images' => fn ($query) => $query
                        ->select(['id', 'shop_id', 'type', 'path'])
                        ->where('type', ImageType::Logo),
                ])
                ->whereHas('images', fn ($query) => $query->where('type', ImageType::Logo))
                ->ordered()
                ->limit(self::FEATURED_LIMIT)
                ->get(['id', 'name', 'slug', 'verified', 'order'])
                ->map(fn (Shop $shop): array => [
                    'name' => $shop->name,
                    'slug' => $shop->slug,
                    'verified' => (bool) $shop->verified,
                    'logo' => $this->shopLogoUrl($shop),
                ])
                ->all();
        }));
    }

    /**
     * @return Collection<int, RepairShop>
     */
    private function featuredRepairShops(): Collection
    {
        return collect($this->rememberHomeData('home:featured-repair-shops:v1', function (): array {
            return RepairShop::query()
                ->with([
                    'images' => fn ($query) => $query
                        ->select(['id', 'repair_shop_id', 'type', 'path'])
                        ->where('type', ImageType::Logo),
                ])
                ->orderBy('name')
                ->limit(self::FEATURED_LIMIT)
                ->get(['id', 'name', 'slug'])
                ->map(fn (RepairShop $shop): array => [
                    'name' => $shop->name,
                    'slug' => $shop->slug,
                    'profile_url' => $shop->profileUrl(),
                    'logo' => $this->repairShopLogoUrl($shop),
                ])
                ->all();
        }));
    }

    /**
     * @return Collection<int, Representation>
     */
    private function featuredRepresentations(): Collection
    {
        return collect($this->rememberHomeData('home:featured-representations:v1', function (): array {
            return Representation::query()
                ->orderBy('name')
                ->limit(self::FEATURED_LIMIT)
                ->get(['id', 'name', 'slug', 'logo'])
                ->map(fn (Representation $representation): array => [
                    'name' => $representation->name,
                    'slug' => $representation->slug,
                    'logo' => $this->representationLogoUrl($representation),
                ])
                ->all();
        }));
    }

    /**
     * @return Collection<int, Part>
     */
    private function allParts(): Collection
    {
        return collect($this->rememberHomeData('home:parts:v1', function (): array {
            return Part::query()
                ->with('partsCategory:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'parts_category_id'])
                ->map(fn (Part $part): array => [
                    'name' => $part->name,
                    'title' => $part->name,
                    'slug' => $part->slug,
                    'partsCategory' => $part->partsCategory
                        ? ['name' => $part->partsCategory->name]
                        : null,
                ])
                ->all();
        }));
    }

    /**
     * @return array<string, array{slug: string}>
     */
    private function partCategoryParts(): array
    {
        $categories = config('partsmall.home_part_categories', []);
        $partNames = collect($categories)
            ->flatMap(fn (array $category) => $category['parts'] ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($partNames->isEmpty()) {
            return [];
        }

        return $this->rememberHomeData('home:part-category-parts:'.md5(json_encode($partNames->all())), fn (): array => Part::query()
            ->whereIn('name', $partNames->all())
            ->get(['name', 'slug'])
            ->mapWithKeys(fn (Part $part): array => [
                $part->name => ['slug' => $part->slug],
            ])
            ->all());
    }

    private function shopLogoUrl(Shop $shop): ?string
    {
        $logo = $shop->images->firstWhere('type', ImageType::Logo);

        return $logo?->path
            ? ShopImageUrlBuilder::build('shop', ImageType::Logo, $shop->id, $logo->path)
            : null;
    }

    private function repairShopLogoUrl(RepairShop $shop): string
    {
        $logo = $shop->images->firstWhere('type', ImageType::Logo);

        return $logo?->path
            ? ShopImageUrlBuilder::build('repair', ImageType::Logo, $shop->id, $logo->path)
            : asset('panel/assets/uploads/img/no_image_repair.jpg');
    }

    private function representationLogoUrl(Representation $representation): string
    {
        $logoPath = $representation->getRawOriginal('logo');

        return filled($logoPath)
            ? ShopImageUrlBuilder::build('representation', ImageType::Logo, $representation->id, basename((string) $logoPath))
            : asset('panel/assets/uploads/img/no_image_representation.jpg');
    }

    private function rememberHomeData(string $key, callable $callback): mixed
    {
        if (app()->environment('testing')) {
            return $callback();
        }

        return SafeCache::remember($key, self::CACHE_TTL, $callback, fn (mixed $value): bool => is_array($value));
    }
}
