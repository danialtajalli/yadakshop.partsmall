<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Representation;
use App\Support\EnglishDigits;
use App\Support\ShopImageUrlBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepresentationProfileService
{
    /**
     * @return array{
     *     representation: Representation,
     *     title: string,
     *     serviceTypes: list<string>,
     *     contacts: list<array{label: string, value: string}>,
     *     socialLinks: list<array{label: string, url: string}>,
     * }
     */
    public function getProfilePageData(string $slug): array
    {
        $representation = Representation::query()
            ->with(['state', 'city', 'company.images'])
            ->where('slug', $slug)
            ->first();

        if ($representation === null) {
            throw (new ModelNotFoundException)->setModel(Representation::class, [$slug]);
        }

        ShopImageUrlBuilder::attachRepresentationMedia($representation);
        $representation->description = $this->sanitizeDescription($representation->description);
        $this->normalizePhoneFields($representation);

        $companyLogo = $representation->company?->images->firstWhere('type', ImageType::Logo);
        if ($representation->company && $companyLogo) {
            $representation->company->logo_url = ShopImageUrlBuilder::companyLogoUrl($companyLogo);
        }

        return [
            'representation' => $representation,
            'title' => $representation->name,
            'serviceTypes' => $this->parseServiceTypes($representation->service_type),
            'contacts' => $this->buildContacts($representation),
            'socialLinks' => $this->buildSocialLinks($representation),
        ];
    }

    /** @return list<string> */
    private function parseServiceTypes(?string $serviceType): array
    {
        if ($serviceType === null || trim($serviceType) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            trim(...),
            preg_split('/\s*,\s*/', $serviceType) ?: [],
        )));
    }

    /** @return list<array{label: string, value: string}> */
    private function buildContacts(Representation $representation): array
    {
        $contacts = [];

        if ($representation->telephone) {
            $contacts[] = ['label' => 'تلفن ثابت', 'value' => $representation->telephone];
        }

        if ($representation->mobile) {
            $contacts[] = ['label' => 'تلفن همراه', 'value' => $representation->mobile];
        }

        if ($representation->whatsapp_phone) {
            $contacts[] = ['label' => 'واتساپ', 'value' => $representation->whatsapp_phone];
        }

        if ($representation->telegram_phone) {
            $contacts[] = ['label' => 'تلگرام', 'value' => $representation->telegram_phone];
        }

        return $contacts;
    }

    /** @return list<array{label: string, url: string}> */
    private function buildSocialLinks(Representation $representation): array
    {
        $links = [];

        if ($representation->website) {
            $links[] = [
                'label' => $representation->website_name ?: 'وب‌سایت',
                'url' => $this->externalUrl($representation->website),
            ];
        }

        if ($representation->instagram) {
            $instagram = $representation->instagram;
            $links[] = [
                'label' => 'اینستاگرام',
                'url' => str_starts_with($instagram, 'http')
                    ? $instagram
                    : 'https://www.instagram.com/'.$instagram,
            ];
        }

        if ($representation->telegram) {
            $links[] = [
                'label' => 'تلگرام',
                'url' => $this->externalUrl($representation->telegram),
            ];
        }

        if ($representation->whatsapp) {
            $links[] = [
                'label' => 'واتساپ',
                'url' => $this->externalUrl($representation->whatsapp),
            ];
        }

        return $links;
    }

    private function externalUrl(string $value): string
    {
        return str_starts_with($value, 'http') ? $value : 'https://'.$value;
    }

    private function normalizePhoneFields(Representation $representation): void
    {
        foreach (['mobile', 'telephone', 'whatsapp_phone', 'telegram_phone'] as $field) {
            if ($representation->{$field}) {
                $representation->{$field} = EnglishDigits::convert($representation->{$field});
            }
        }
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
