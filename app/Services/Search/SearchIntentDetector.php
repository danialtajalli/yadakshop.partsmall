<?php

namespace App\Services\Search;

use App\Models\Car;
use App\Models\Company;
use App\Models\Part;
use App\Support\Search\SearchTextNormalizer;
use Illuminate\Support\Collection;

class SearchIntentDetector
{
    public function __construct(
        private readonly SearchTextNormalizer $normalizer,
    ) {}

    /**
     * @return array{
     *     type: string,
     *     normalized: string,
     *     company: ?Company,
     *     car: ?Car,
     *     part: ?Part,
     *     parts: Collection<int, Part>
     * }
     */
    public function detect(string $query): array
    {
        $normalized = $this->normalizer->normalize($query);

        $intent = [
            'type' => 'generic',
            'normalized' => $normalized,
            'company' => null,
            'car' => null,
            'part' => null,
            'parts' => collect(),
        ];

        if ($normalized === '') {
            return $intent;
        }

        $exactVehicle = $this->findExactVehicle($normalized);
        $exactCompany = $this->findExactCompany($normalized);
        $partMatches = $this->findPartMatches($normalized);
        $broadPartMatches = $this->findPartMatches($normalized, broad: true);
        $containedVehicle = $exactVehicle ?? $this->findContainedVehicle($normalized);

        if ($containedVehicle && $broadPartMatches->isNotEmpty() && $normalized !== $this->normalizer->normalize($containedVehicle->name))
        {
            return [
                ...$intent,
                'type' => 'mixed_part_vehicle',
                'company' => $containedVehicle->company,
                'car' => $containedVehicle,
                'part' => $broadPartMatches->first(),
                'parts' => $broadPartMatches,
            ];
        }

        if ($containedVehicle && $normalized !== $this->normalizer->normalize($containedVehicle->name)) {
            return [
                ...$intent,
                'type' => 'vehicle_context',
                'company' => $containedVehicle->company,
                'car' => $containedVehicle,
            ];
        }

        if ($exactVehicle) {
            return [
                ...$intent,
                'type' => 'exact_vehicle',
                'company' => $exactVehicle->company,
                'car' => $exactVehicle,
            ];
        }

        if ($exactCompany) {
            return [
                ...$intent,
                'type' => 'exact_company',
                'company' => $exactCompany,
            ];
        }

        if ($partMatches->isNotEmpty()) {
            return [
                ...$intent,
                'type' => 'exact_part',
                'part' => $partMatches->first(),
                'parts' => $partMatches,
            ];
        }

        return $intent;
    }

    private function findExactCompany(string $normalized): ?Company
    {
        return Company::query()
            ->get()
            ->first(fn (Company $company): bool => $this->normalizer->normalize($company->name) === $normalized);
    }

    private function findExactVehicle(string $normalized): ?Car
    {
        return Car::query()
            ->with('company')
            ->get()
            ->first(fn (Car $car): bool => in_array($normalized, $this->vehicleAliases($car), true));
    }

    private function findContainedVehicle(string $normalized): ?Car
    {
        $queryTokens = $this->normalizer->tokens($normalized);

        return Car::query()
            ->with('company')
            ->get()
            ->first(function (Car $car) use ($normalized, $queryTokens): bool
            {
                foreach ($this->vehicleAliases($car) as $alias)
                {
                    if ($alias !== '' && str_contains($normalized, $alias))
                    {
                        return true;
                    }

                    $aliasTokens = $this->normalizer->tokens($alias);

                    if ($this->containsOrderedTokens($queryTokens, $aliasTokens))
                    {
                        return true;
                    }
                }

                return false;
            });
    }

    /**
     * @param  list<string>  $queryTokens
     * @param  list<string>  $aliasTokens
     */
    private function containsOrderedTokens(array $queryTokens, array $aliasTokens): bool
    {
        if ($queryTokens === [] || $aliasTokens === [] || count($aliasTokens) > count($queryTokens)) {
            return false;
        }

        $aliasLength = count($aliasTokens);
        $lastStart = count($queryTokens) - $aliasLength;

        for ($start = 0; $start <= $lastStart; $start++) {
            if (array_slice($queryTokens, $start, $aliasLength) === $aliasTokens) {
                return true;
            }
        }

        $aliasIndex = 0;

        foreach ($queryTokens as $queryToken) {
            if ($queryToken !== $aliasTokens[$aliasIndex]) {
                continue;
            }

            $aliasIndex++;

            if ($aliasIndex === $aliasLength) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, Part>
     */
    private function findPartMatches(string $normalized, bool $broad = false): Collection
    {
        $queryTokens = $this->normalizer->tokens($normalized);

        return Part::query()
            ->with('partsCategory')
            ->orderBy('name')
            ->get()
            ->filter(function (Part $part) use ($normalized, $queryTokens, $broad): bool {
                $partName = $this->normalizer->normalize($part->name);
                $partTokens = $this->normalizer->tokens($part->name);

                $strictMatch = $partName === $normalized
                    || str_starts_with($partName, $normalized.' ');

                if ($strictMatch || ! $broad) {
                    return $strictMatch;
                }

                return $queryTokens !== [] && $partTokens !== [] && array_intersect($queryTokens, $partTokens) !== [];
            })
            ->values();
    }

    /**
     * @return list<string>
     */
    private function vehicleAliases(Car $car): array
    {
        return array_values(array_filter(array_unique([
            $this->normalizer->normalize($car->name),
            $this->normalizer->normalize($car->slug),
            $this->normalizer->normalize($car->company?->name.' '.$car->name),
            $this->normalizer->normalize($car->company?->name.' '.$car->slug),
        ])));
    }
}
