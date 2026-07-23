<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Company;
use App\Models\Part;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Support\CarModelLabel;
use App\Support\CarModelSort;
use App\Support\ModelCategoryLabel;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Support\Collection;

class HomePageService
{
    private const FEATURED_LIMIT = 17;

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
        $parts->transform(function (Part $part): Part {
            $part->title = $part->name;
            return $part;
        });

        return [
            'shops' => $this->featuredShops(),
            'repairShops' => $this->featuredRepairShops(),
            'companies' => $companies,
            'companyPicker' => $companyPicker,
            'vehicleFilter' => $this->buildVehicleFilter($companyPicker, $parts),
            'representations' => $this->featuredRepresentations(),
            'bestShops' => $this->bestShowcaseShops(),
            'parts' => $parts,
            'title' => "پارتس‌مال",
        ];
    }

    /**
     * @return Collection<int, Company>
     */
    private function featuredCompanies(): Collection
    {
        $companies = Company::query()
            ->with(['images', 'cars.models.category'])
            ->orderBy('id')
            ->get();

        $companies->each(function (Company $company): void {
            $logo = $company->images->firstWhere('type', ImageType::Logo);

            $company->logo_url = $logo
                ? ShopImageUrlBuilder::companyLogoUrl($logo)
                : null;
        });

        return $companies;
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
            ->map(function (Company $company): array {
                return [
                    'slug' => $company->slug,
                    'name' => $company->name,
                    'logo_url' => $company->logo_url,
                    'cars' => $company->cars
                        ->sortBy('name')
                        ->values()
                        ->map(function ($car) use ($company): array {
                            return [
                                'slug' => $car->slug,
                                'name' => strtoupper($car->name),
                                'modelCategories' => $car->models
                                    ->sortBy('name')
                                    ->groupBy(fn ($model) => (string) ($model->category_id ?? 0))
                                    ->map(function ($models, $categoryId) use ($company, $car): array {
                                        $category = $models->first()->category;

                                        return [
                                            'slug' => ModelCategoryLabel::slug($category),
                                            'label' => ModelCategoryLabel::display($category),
                                            'models' => CarModelSort::prioritize(
                                                $models
                                                    ->map(function ($model) use ($company, $car, $category): array {
                                                        return [
                                                            'slug' => $model->slug,
                                                            'name' => CarModelLabel::display($model),
                                                            'category_slug' => ModelCategoryLabel::slug($category),
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
                                    ->all(),
                            ];
                        })
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
                ->map(fn (Part $part): array => [
                    'slug' => $part->slug,
                    'name' => $part->name,
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

        $shops = Shop::query()
            ->with(['images'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Shop $shop) => array_search($shop->id, $ids, true))
            ->values();

        $shops->each(fn (Shop $shop) => ShopImageUrlBuilder::attachShopMedia($shop, 'shop'));

        return $shops;
    }

    /**
     * @return Collection<int, Shop>
     */
    private function featuredShops(): Collection
    {
        $shops = Shop::query()
            ->with(['images'])
            ->whereHas('images', fn ($query) => $query->where('type', ImageType::Logo))
            ->ordered()
            ->limit(self::FEATURED_LIMIT)
            ->get();

        $shops->each(fn (Shop $shop) => ShopImageUrlBuilder::attachShopMedia($shop, 'shop'));

        return $shops;
    }

    /**
     * @return Collection<int, RepairShop>
     */
    private function featuredRepairShops(): Collection
    {
        $repairShops = RepairShop::query()
            ->with(['images'])
            ->orderBy('name')
            ->limit(self::FEATURED_LIMIT)
            ->get();

        $repairShops->each(fn (RepairShop $shop) => ShopImageUrlBuilder::attachRepairShopMedia($shop));

        return $repairShops;
    }

    /**
     * @return Collection<int, Representation>
     */
    private function featuredRepresentations(): Collection
    {
        $representations = Representation::query()
            ->orderBy('name')
            ->limit(self::FEATURED_LIMIT)
            ->get();

        $representations->each(fn (Representation $representation) => ShopImageUrlBuilder::attachRepresentationMedia($representation));

        return $representations;
    }

    /**
     * @return Collection<int, Part>
     */
    private function allParts(): Collection
    {
        return Part::query()
            ->with('partsCategory')
            ->orderBy('name')
            ->get();
    }
}
