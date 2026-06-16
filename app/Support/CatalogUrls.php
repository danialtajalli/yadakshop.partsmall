<?php

namespace App\Support;

class CatalogUrls
{
    public static function companies(): string
    {
        return route('companies.index');
    }

    public static function cars(?string $companySlug): string
    {
        if ($companySlug === null || $companySlug === '') {
            return route('companies.index');
        }

        return route('cars.index', ['company' => $companySlug]);
    }

    public static function models(?string $companySlug = null, ?string $carSlug = null): string
    {
        if ($companySlug && $carSlug) {
            return route('models.index', [
                'company' => $companySlug,
                'car' => $carSlug,
            ]);
        }

        if ($companySlug) {
            return route('cars.index', ['company' => $companySlug]);
        }

        return route('companies.index');
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function parts(?string $companySlug = null, ?string $carSlug = null, ?string $modelSlug = null, array $query = []): string
    {
        if ($companySlug && $carSlug && $modelSlug) {
            return route('car.parts.vehicle', [
                'company' => $companySlug,
                'car' => $carSlug,
                'model' => $modelSlug,
            ]);
        }

        $query = array_filter(array_merge([
            'company' => $companySlug,
            'car' => $carSlug,
            'model' => $modelSlug,
        ], $query), fn ($value) => filled($value));

        $url = route('car.parts');

        if ($query === []) {
            return $url;
        }

        return $url.'?'.http_build_query($query);
    }
}
