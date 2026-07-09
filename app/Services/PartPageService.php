<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Part;
use App\Support\PageTitle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class PartPageService
{
    private const PER_PAGE = 60;

    /**
     * @return array{
     *     part: Part,
     *     title: string,
     *     vehicleApplications: LengthAwarePaginator,
     *     filters: array{q: ?string},
     * }
     */
    public function getPartPageData(string $slug, Request $request): array
    {
        $part = Part::query()
            ->with('partsCategory')
            ->where('slug', $slug)
            ->first();

        if ($part === null) {
            throw (new ModelNotFoundException)->setModel(Part::class, [$slug]);
        }

        $part->description = $this->sanitizeDescription($part->description, $part);
        $part->category_description = $this->sanitizeDescription($part->category_description, $part);

        $filters = [
            'q' => $request->string('q')->trim()->toString() ?: null,
        ];

        $vehicleApplications = $this->paginateVehicleApplications(
            $part,
            $filters['q'],
            $request->integer('page', 1),
        );

        return [
            'part' => $part,
            'title' => PageTitle::appendPageNumber($part->name, $vehicleApplications->currentPage()),
            'vehicleApplications' => $vehicleApplications,
            'filters' => $filters,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array{label: string, short_label: string, url: string}>
     */
    private function paginateVehicleApplications(Part $part, ?string $query, int $page): LengthAwarePaginator
    {
        $applications = collect($this->buildVehicleApplications($part))
            ->sortBy(fn (array $application): int => crc32($part->slug.'|'.$application['url']))
            ->values();

        if ($query) {
            $needle = mb_strtolower($query);
            $applications = $applications->filter(
                fn (array $application) => str_contains(mb_strtolower($application['short_label']), $needle)
                    || str_contains(mb_strtolower($application['label']), $needle),
            )->values();
        }

        $total = $applications->count();
        $items = $applications
            ->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)
            ->values()
            ->all();

        return new Paginator(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            [
                'path' => route('part.show', $part->slug),
            ],
        );
    }

    /**
     * @return list<array{label: string, short_label: string, url: string}>
     */
    private function buildVehicleApplications(Part $part): array
    {
        $applications = [];

        $companies = Company::query()
            ->with(['cars.models'])
            ->orderBy('name')
            ->get();

        foreach ($companies as $company) {
            foreach ($company->cars->sortBy('name') as $car) {
                foreach ($car->models->sortBy('name') as $model) {
                    $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;
                    $shortLabel = $part->name.' '. trim($company->name.' '.$car->name.' '.$modelName);
                    $label = $shortLabel;

                    $applications[] = [
                        'label' => $label,
                        'short_label' => $shortLabel,
                        'url' => route('product.show', [
                            'company' => $company->slug,
                            'car' => $car->slug,
                            'model' => $model->slug,
                            'part' => $part->slug,
                        ]),
                    ];
                }
            }
        }

        return $applications;
    }

    private function sanitizeDescription(?string $description, Part $part): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['rn', 'xxx', 'ططط', 'ظظظ'],
            ['', $part->partsCategory?->name ?? '', ''],
            $description,
        );
    }
}
