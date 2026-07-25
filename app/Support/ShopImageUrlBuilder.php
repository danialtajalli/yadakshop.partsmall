<?php

namespace App\Support;

use App\Enums\ImageType;
use App\Models\Image;
use App\Models\RepairShop;
use App\Models\Representation;
use Illuminate\Database\Eloquent\Model;

class ShopImageUrlBuilder
{
    public static function build(string $modelType, ImageType $imageType, int|string $modelId, string $path): string
    {
        return asset(str_replace('//', '/',
            str_replace(
            ['{model_type}', '{image_type}', '{model_id}', '{image_name}'],
            [$modelType, $imageType->value, (string) $modelId, $path],
            config('partsmall.image_url'))
        ));
    }

    public static function buildCompanyLogoUrl(string $modelType, int|string $modelId, string $path): string
    {
        return asset(str_replace('//', '/',
            str_replace(
            ['{model_type}', '{image_type}', '{model_id}', '{image_name}'],
            [$modelType, '', (string) $modelId, $path],
            config('partsmall.image_url'))
        ));
    }

    public static function attachShopMedia(Model $model, string $modelType = 'shop'): void
    {
        if (! method_exists($model, 'images')) {
            return;
        }

        $model->images->each(function (Image $image) use ($model, $modelType): void {
            if (! in_array($image->type, [ImageType::Cover, ImageType::Logo], true)) {
                return;
            }

            $property = $image->type === ImageType::Cover ? 'cover' : 'logo';
            $model->{$property} = self::build($modelType, $image->type, $model->id, $image->path);
        });
    }

    public static function companyLogoUrl(Image $image): string
    {
        return self::buildCompanyLogoUrl('company', $image->company_id, $image->path);
    }

    public static function attachRepairShopMedia(RepairShop $repairShop): void
    {
        $logo = $repairShop->images->firstWhere('type', ImageType::Logo);

        $repairShop->logo = $logo?->path
            ? self::build('repair', ImageType::Logo, $repairShop->id, $logo->path)
            : asset('panel/assets/uploads/img/no_image_repair.jpg');

        $cover = $repairShop->images->firstWhere('type', ImageType::Cover);

        if ($cover?->path) {
            $repairShop->cover = self::build('repair', ImageType::Cover, $repairShop->id, $cover->path);
        }
    }

    public static function attachRepresentationMedia(Representation $representation): void
    {
        $logoPath = $representation->getRawOriginal('logo');

        if (! filled($logoPath)) {
            $representation->logo = asset('panel/assets/uploads/img/no_image_representation.jpg');

            return;
        }

        $representation->logo = self::build(
            'representation',
            ImageType::Logo,
            $representation->id,
            basename((string) $logoPath),
        );
    }
}
