<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Part;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Support\Collection;

class HomePageService
{
    private const FEATURED_LIMIT = 16;

    /**
     * @return array{
     *     shops: Collection<int, Shop>,
     *     repairShops: Collection<int, RepairShop>,
     *     representations: Collection<int, Representation>,
     *     parts: Collection<int, Part>,
     *     title: string,
     * }
     */
    public function getHomePageData(): array
    {
        return [
            'shops' => $this->featuredShops(),
            'repairShops' => $this->featuredRepairShops(),
            'representations' => $this->featuredRepresentations(),
            'parts' => $this->allParts(),
            'title' => config('app.name', 'پارتس‌مال'),
        ];
    }

    /**
     * @return Collection<int, Shop>
     */
    private function featuredShops(): Collection
    {
        $shops = Shop::query()
            ->with(['images'])
            ->whereHas('images', fn ($query) => $query->where('type', ImageType::Logo))
            ->orderBy('order')
            ->orderBy('name')
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
