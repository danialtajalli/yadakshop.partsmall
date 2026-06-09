<?php

namespace App\Services;

use App\Models\RepairShop;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepairShopProfileService
{
    /**
     * @return array{
     *     repairShop: RepairShop,
     *     title: string,
     * }
     */
    public function getProfilePageData(string $slug): array
    {
        $repairShop = RepairShop::query()
            ->with([
                'state',
                'images',
                'phones',
                'links',
                'repairCategories',
            ])
            ->where('slug', $slug)
            ->first();

        if ($repairShop === null) {
            throw (new ModelNotFoundException)->setModel(RepairShop::class, [$slug]);
        }

        ShopImageUrlBuilder::attachRepairShopMedia($repairShop);
        $repairShop->description = $this->sanitizeDescription($repairShop->description);

        return [
            'repairShop' => $repairShop,
            'title' => $repairShop->name,
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
