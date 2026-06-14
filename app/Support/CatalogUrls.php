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
        return route('cars.index', ['company' => $companySlug]);
    }

    public static function models(?string $companySlug = null, ?string $carSlug = null): string
    {
        $params = ['company' => $companySlug, 'car' => $carSlug];

        return route('models.index', $params);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function parts(?string $companySlug = null, ?string $carSlug = null, ?string $modelSlug = null, array $query = []): string
    {
        if ($companySlug && $carSlug && $modelSlug) {
            $url = route('car.parts.vehicle', [
                'company' => $companySlug,
                'car' => $carSlug,
                'model' => $modelSlug,
            ]);

            return $url;
        }

        $query = array_filter(array_merge([
            'company' => $companySlug,
            'car' => $carSlug,
            'model' => $modelSlug,
        ], $query), fn ($value) => filled($value));

        $url = route('car.parts');

        return $url;
    }
}
