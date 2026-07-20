<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Models\Company;
use App\Models\Phone;
use App\Models\Shop;
use App\Support\EnglishDigits;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
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
        $shop = Shop::query()
            ->with([
                'city.state',
                'images',
                'phones',
                'links',
                'partsCategories',
                'companies.images',
                'comments' => fn ($query) => $query->confirmed()->latest(),
            ])
            ->withAvg(['comments as average_rating' => fn ($query) => $query->confirmed()], 'rating')
            ->where('slug', $slug)
            ->first();

        if ($shop === null) {
            throw (new ModelNotFoundException)->setModel(Shop::class, [$slug]);
        }

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
        ];
    }


    public function isOpen($shop): bool
    {
        $now = Carbon::now();
        
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
