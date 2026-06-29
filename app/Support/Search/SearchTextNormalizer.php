<?php

namespace App\Support\Search;

class SearchTextNormalizer
{
    /**
     * @var array<string, string>
     */
    private const CHARACTER_REPLACEMENTS = [
        'ي' => 'ی',
        'ى' => 'ی',
        'ك' => 'ک',
        'ة' => 'ه',
        'ۀ' => 'ه',
        'ؤ' => 'و',
        'إ' => 'ا',
        'أ' => 'ا',
        'آ' => 'ا',
        "\u{200c}" => ' ',
        "\u{200d}" => ' ',
        "\u{00a0}" => ' ',
    ];

    public function normalize(?string $value): string
    {
        $value = mb_strtolower((string) $value, 'UTF-8');
        $value = strtr($value, self::CHARACTER_REPLACEMENTS);
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $value) ?? $value;
        $value = preg_replace('/[ـ\-_\/\\\\|,،؛;:!؟?\.\(\)\[\]\{\}"\'«»+]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * @return list<string>
     */
    public function tokens(?string $value): array
    {
        $normalized = $this->normalize($value);

        if ($normalized === '') {
            return [];
        }

        return array_values(array_filter(
            explode(' ', $normalized),
            fn (string $token): bool => $token !== '',
        ));
    }
}
