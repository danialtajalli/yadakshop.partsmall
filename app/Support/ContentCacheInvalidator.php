<?php

namespace App\Support;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Image;
use App\Models\ModelCategory;
use App\Models\Page;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\RepairCategory;
use App\Models\RepairShop;
use App\Models\Representation;
use App\Models\Shop;
use App\Models\State;
use Illuminate\Database\Eloquent\Model;

final class ContentCacheInvalidator
{
    public static function forModel(Model $model): void
    {
        SafeCache::flushTags(self::tagsFor($model));
    }

    /**
     * @return list<string>
     */
    public static function tagsFor(Model $model): array
    {
        return match (true) {
            $model instanceof Company,
            $model instanceof Car,
            $model instanceof CarModel,
            $model instanceof ModelCategory => [
                ContentCacheTag::HOME,
                ContentCacheTag::CATALOG,
            ],
            $model instanceof Shop => [
                ContentCacheTag::HOME,
                ContentCacheTag::DIRECTORY,
                ContentCacheTag::PRODUCT,
            ],
            $model instanceof RepairShop,
            $model instanceof Representation => [
                ContentCacheTag::HOME,
                ContentCacheTag::DIRECTORY,
            ],
            $model instanceof Part => [
                ContentCacheTag::HOME,
                ContentCacheTag::CATALOG,
                ContentCacheTag::PART_PAGE,
            ],
            $model instanceof PartsCategory => [
                ContentCacheTag::HOME,
                ContentCacheTag::CATALOG,
            ],
            $model instanceof State,
            $model instanceof City => [
                ContentCacheTag::DIRECTORY,
                ContentCacheTag::PRODUCT,
            ],
            $model instanceof RepairCategory => [
                ContentCacheTag::DIRECTORY,
            ],
            $model instanceof Page => [
                ContentCacheTag::PAGES,
            ],
            $model instanceof Comment => [
                ContentCacheTag::HOME,
                ContentCacheTag::DIRECTORY,
                ContentCacheTag::PRODUCT,
            ],
            $model instanceof Image => self::tagsForImage($model),
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    private static function tagsForImage(Image $image): array
    {
        if (filled($image->company_id)) {
            return [ContentCacheTag::HOME, ContentCacheTag::CATALOG];
        }

        if (filled($image->shop_id)) {
            return [
                ContentCacheTag::HOME,
                ContentCacheTag::DIRECTORY,
                ContentCacheTag::PRODUCT,
            ];
        }

        if (filled($image->repair_shop_id)) {
            return [ContentCacheTag::HOME, ContentCacheTag::DIRECTORY];
        }

        return [ContentCacheTag::HOME];
    }
}
