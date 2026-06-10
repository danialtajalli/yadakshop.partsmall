<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Enums\PhoneType;
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
     *     contacts: list<array{label: string, value: string, kind: string, url: string, external: bool}>,
     *     socialLinks: list<array{label: string, url: string, kind: string}>,
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

    /** @return list<array{label: string, value: string, kind: string, url: string, external: bool}> */
    private function buildContacts(Representation $representation): array
    {
        $contacts = [];

        if ($representation->telephone) {
            $contacts[] = $this->contactEntry(
                'تلفن ثابت',
                $representation->telephone,
                PhoneType::Land,
            );
        }

        if ($representation->mobile) {
            $contacts[] = $this->contactEntry(
                'تلفن همراه',
                $representation->mobile,
                PhoneType::Mobile,
            );
        }

        if ($representation->whatsapp_phone) {
            $contacts[] = $this->contactEntry(
                'واتساپ',
                $representation->whatsapp_phone,
                PhoneType::Whatsapp,
            );
        }

        if ($representation->telegram_phone) {
            $contacts[] = $this->contactEntry(
                'تلگرام',
                $representation->telegram_phone,
                PhoneType::Telegram,
            );
        }

        return $contacts;
    }

    /** @return array{label: string, value: string, kind: string, url: string, external: bool} */
    private function contactEntry(string $label, string $value, PhoneType $type): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'kind' => $type->value,
            'url' => $type->actionUrl($value),
            'external' => ! in_array($type, [PhoneType::Land, PhoneType::Mobile], true),
        ];
    }

    /** @return list<array{label: string, url: string, kind: string}> */
    private function buildSocialLinks(Representation $representation): array
    {
        $links = [];

        if ($representation->website) {
            $links[] = [
                'label' => $representation->website_name ?: 'وب‌سایت',
                'url' => $this->externalUrl($representation->website),
                'kind' => 'website',
            ];
        }

        if ($representation->instagram) {
            $instagram = $representation->instagram;
            $links[] = [
                'label' => 'اینستاگرام',
                'url' => str_starts_with($instagram, 'http')
                    ? $instagram
                    : 'https://www.instagram.com/'.$instagram,
                'kind' => 'instagram',
            ];
        }

        if ($representation->telegram) {
            $links[] = [
                'label' => 'تلگرام',
                'url' => $this->externalUrl($representation->telegram),
                'kind' => 'telegram',
            ];
        }

        if ($representation->whatsapp) {
            $links[] = [
                'label' => 'واتساپ',
                'url' => $this->externalUrl($representation->whatsapp),
                'kind' => 'whatsapp',
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
