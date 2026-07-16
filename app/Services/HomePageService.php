<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Company;
use App\Models\Part;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Support\CarModelLabel;
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
     *     representations: Collection<int, Representation>,
     *     parts: Collection<int, Part>,
     *     title: string,
     * }
     */
    public function getHomePageData(): array
    {
        $companies = $this->featuredCompanies();
        $parts = $this->allParts();
        $parts->transform(function (Part $part): Part {
            $part->title = $part->name;
            return $part;
        });

        return [
            'shops' => $this->featuredShops(),
            'repairShops' => $this->featuredRepairShops(),
            'companies' => $companies,
            'companyPicker' => $this->buildCompanyPicker($companies),
            'representations' => $this->featuredRepresentations(),
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
                                            'models' => $models
                                                ->map(function ($model) use ($company, $car): array {
                                                    return [
                                                        'slug' => $model->slug,
                                                        'name' => CarModelLabel::display($model),
                                                        'url' => route('car.parts.vehicle', [
                                                            'company' => $company->slug,
                                                            'car' => $car->slug,
                                                            'model' => $model->slug,
                                                        ]),
                                                    ];
                                                })
                                                ->values()
                                                ->all(),
                                        ];
                                    })
                                    ->sortByDesc(fn (array $category): int => count($category['models']))
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
