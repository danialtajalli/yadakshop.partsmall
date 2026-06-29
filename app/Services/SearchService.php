<?php

namespace App\Services;

use App\Models\Part;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    private const PER_PAGE = 24;

    /**
     * @return LengthAwarePaginator<int, Part>
     */
    public function searchParts(?string $query): LengthAwarePaginator
    {
        $query = trim((string) $query);

        if ($query === '') {
            $parts = Part::query()
                ->with('partsCategory')
                ->orderBy('name')
                ->paginate(self::PER_PAGE);

            return $this->prepareParts($parts);
        }

        $parts = Part::search($query)
            ->query(fn ($builder) => $builder->with('partsCategory'))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return $this->prepareParts($parts);
    }

    /**
     * @param  LengthAwarePaginator<int, Part>  $parts
     * @return LengthAwarePaginator<int, Part>
     */
    private function prepareParts(LengthAwarePaginator $parts): LengthAwarePaginator
    {
        $parts->getCollection()->transform(function (Part $part): Part {
            $part->setAttribute('title', $part->name);

            return $part;
        });

        return $parts;
    }
}
