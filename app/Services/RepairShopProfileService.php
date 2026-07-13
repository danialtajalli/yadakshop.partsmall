<?php

namespace App\Services;

use App\Models\Phone;
use App\Models\RepairShop;
use App\Support\EnglishDigits;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepairShopProfileService
{
    /**
     * @return array{
     *     repairShop: RepairShop,
     *     title: string,
     * }
     */
    public function getProfilePageData(int $id, string $slug): array
    {
        $repairShop = RepairShop::query()
            ->with([
                'city.state',
                'images',
                'phones',
                'links',
                'repairCategories',
            ])
            ->whereKey($id)
            ->first();

        if ($repairShop === null || $repairShop->slug !== $slug) {
            throw (new ModelNotFoundException)->setModel(RepairShop::class, [$id]);
        }

        ShopImageUrlBuilder::attachRepairShopMedia($repairShop);
        $repairShop->description = $this->sanitizeDescription($repairShop->description);
        $this->normalizePhoneNumbers($repairShop->phones);

        return [
            'repairShop' => $repairShop,
            'title' => $repairShop->name,
        ];
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
