<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Company;
use App\Models\Scopes\ShopOrderScope;
use App\Models\Scopes\ShopProductScope;
use App\Models\Shop;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShopProfileService
{
    /**
     * @return array{
     *     shop: Shop,
     *     title: string,
     *     averageRating: ?float,
     *     commentsCount: int,
     * }
     */
    public function getProfilePageData(string $slug): array
    {
        $shop = Shop::withoutGlobalScopes([ShopProductScope::class, ShopOrderScope::class])
            ->with([
                'state',
                'images',
                'phones',
                'links',
                'partsCategories',
                'companies.images',
                'comments' => fn ($query) => $query->where('confirmed', true)->latest(),
            ])
            ->withAvg(['comments as average_rating' => fn ($query) => $query->where('confirmed', true)], 'rating')
            ->where('slug', $slug)
            ->first();

        if ($shop === null) {
            throw (new ModelNotFoundException)->setModel(Shop::class, [$slug]);
        }

        ShopImageUrlBuilder::attachShopMedia($shop);
        $shop->description = $this->sanitizeDescription($shop->description);

        $shop->companies->each(function (Company $company): void {
            $logo = $company->images->firstWhere('type', ImageType::Logo);

            $company->logo_url = $logo
                ? ShopImageUrlBuilder::companyLogoUrl($logo)
                : null;
        });

        return [
            'shop' => $shop,
            'title' => $shop->name,
            'averageRating' => $shop->average_rating !== null ? (float) $shop->average_rating : null,
            'commentsCount' => $shop->comments->count(),
        ];
    }

    private function sanitizeDescription(?string $description): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['ظظظ', 'rn'],
            ['', ''],
            $description,
        );
    }
}
