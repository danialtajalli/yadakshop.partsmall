<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Models\Company;
use App\Models\Phone;
use App\Models\Shop;
use App\Support\EnglishDigits;
use App\Support\ShopImageUrlBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\RateLimiter;

class ShopProfileService
{
    private const RELATED_SHOPS_LIMIT = 8;

    private const MAX_VISITS_PER_IP_PER_DAY = 2;

    /**
     * @return array{
     *     shop: Shop,
     *     title: string,
     *     averageRating: ?float,
     *     commentsCount: int,
     *     relatedShops: Collection<int, Shop>,
     * }
     */
    public function getProfilePageData(string $slug): array
    {
        $shop = Shop::query()
            ->with([
                'city.state:id,name',
                'images' => fn ($query) => $query
                    ->select(['id', 'shop_id', 'type', 'path'])
                    ->whereIn('type', [ImageType::Logo, ImageType::Cover]),
                'phones:id,shop_id,phone_number,type',
                'links:id,shop_id,link_type,name',
                'partsCategories:id,name',
                'companies:id,name,slug',
                'companies.images' => fn ($query) => $query
                    ->select(['id', 'company_id', 'type', 'path'])
                    ->where('type', ImageType::Logo),
                'comments' => fn ($query) => $query
                    ->confirmed()
                    ->latest()
                    ->select(['id', 'shop_id', 'fullname', 'rating', 'body', 'created_at']),
            ])
            ->withAvg(['comments as average_rating' => fn ($query) => $query->confirmed()], 'rating')
            ->where('slug', $slug)
            ->first();

        if ($shop === null) {
            throw (new ModelNotFoundException)->setModel(Shop::class, [$slug]);
        }

        $this->incrementVisitedCount($shop);

        $shop->website_show = $shop->links->firstWhere('link_type', LinkType::Website);
        $shop->links = $shop->links->where('link_type', '!=', LinkType::Website);


        ShopImageUrlBuilder::attachShopMedia($shop);
        $shop->description = $this->sanitizeDescription($shop->description);
        $this->normalizePhoneNumbers($shop->phones);

        $shop->companies->each(function (Company $company): void {
            $logo = $company->images->firstWhere('type', ImageType::Logo);

            $company->logo_url = $logo
                ? ShopImageUrlBuilder::companyLogoUrl($logo)
                : null;
        });

        return [
            'shop' => $shop,
            'title' => "پروفایل " . $shop->name . " " . " در پارتس‌مال",
            'averageRating' => $shop->average_rating !== null ? (float) $shop->average_rating : null,
            'commentsCount' => $shop->comments->count(),
            'relatedShops' => $this->loadRelatedShops($shop),
        ];
    }

    public function incrementVisitedCount(Shop $shop): void
    {
        $ip = request()->ip() ?: '0.0.0.0';
        $key = 'shop-profile-visit:'.$shop->getKey().':'.$ip;
        $decaySeconds = max(1, (int) now()->diffInSeconds(now()->copy()->endOfDay()));

        RateLimiter::attempt(
            $key,
            self::MAX_VISITS_PER_IP_PER_DAY,
            function () use ($shop): void {
                $shop->increment('visited_count');
            },
            $decaySeconds,
        );
    }

    /** @return Collection<int, Shop> */
    private function loadRelatedShops(Shop $shop): Collection
    {
        $companyIds = $shop->companies->modelKeys();

        if ($companyIds === []) {
            return new Collection;
        }

        $relatedShops = Shop::query()
            ->with([
                'images' => fn ($query) => $query
                    ->select(['id', 'shop_id', 'type', 'path'])
                    ->where('type', ImageType::Logo),
            ])
            ->whereKeyNot($shop->id)
            ->whereHas(
                'companies',
                fn ($query) => $query->whereIn('companies.id', $companyIds),
            )
            ->whereHas('images', fn ($query) => $query->where('type', ImageType::Logo))
            ->ordered()
            ->limit(self::RELATED_SHOPS_LIMIT)
            ->get(['id', 'name', 'slug', 'verified', 'order']);

        $relatedShops->each(fn (Shop $relatedShop) => ShopImageUrlBuilder::attachShopMedia($relatedShop));

        return $relatedShops;
    }

    public function isOpen($shop): bool
    {
        $now = Carbon::now()->addHours(3)->addMinutes(30);

        switch ($now->dayOfWeek) {
            case 4: // پنجشنبه
                $open = $shop->open_time_thursday;
                $close = $shop->close_time_thursday;
                break;

            case 5: // جمعه
                $open = $shop->open_time_friday;
                $close = $shop->close_time_friday;
                break;

            default: // شنبه تا چهارشنبه
                $open = $shop->open_time;
                $close = $shop->close_time;
                break;
        }

        if (!$open || !$close) {
            return false;
        }

        $openTime = $this->parseShopTime($open)?->setDateFrom($now);
        $closeTime = $this->parseShopTime($close)?->setDateFrom($now);

        if ($openTime === null || $closeTime === null) {
            return false;
        }

        return $now->between($openTime, $closeTime);
    }

    private function parseShopTime(string $time): ?Carbon
    {
        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $time);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /** @param  Collection<int, Phone>  $phones */
    private function normalizePhoneNumbers(Collection $phones): void
    {
        $phones->each(function (Phone $phone): void {
            $phone->phone_number = EnglishDigits::convert($phone->phone_number);
        });
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
