<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Part;
use App\Support\PageTitle;
use App\Support\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;

class PartPageService
{
    private const PER_PAGE = 60;
    private const APPLICATION_CACHE_TTL = 86400;

    /**
     * @return array{
     *     part: Part,
     *     title: string,
     *     breadcrumbs: list<array{label: string, url?: string, active?: bool, emphasized?: bool}>,
     *     vehicleApplications: LengthAwarePaginator,
     *     filters: array{q: ?string},
     * }
     */
    public function getPartPageData(string $slug, Request $request): array
    {
        $part = Part::query()
            ->with('partsCategory:id,name')
            ->where('slug', $slug)
            ->first(['id', 'name', 'description', 'category_description', 'slug', 'parts_category_id']);

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
            'breadcrumbs' => Pagination::buildBreadcrumbs(
                [
                    ['label' => 'ط®ط§ظ†ظ‡', 'url' => route('home')],
                    ['label' => 'ظ‚ط·ط¹ط§طھ', 'url' => route('car.parts')],
                    ['label' => $part->name],
                ],
                $vehicleApplications->currentPage(),
                Pagination::pageUrl($vehicleApplications, 1),
            ),
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

        $paginator = new Paginator(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            [
                'path' => route('part.show', $part->slug),
            ],
        );

        return $paginator->appends(array_filter([
            'q' => $query,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * @return list<array{label: string, short_label: string, url: string}>
     */
    private function buildVehicleApplications(Part $part): array
    {
        $applications = [];

        foreach ($this->vehicleApplicationMatrix() as $vehicle) {
            $shortLabel = $part->name.' '.$vehicle['label'];

            $applications[] = [
                'label' => $shortLabel,
                'short_label' => $shortLabel,
                'url' => route('product.show', [
                    'company' => $vehicle['company_slug'],
                    'car' => $vehicle['car_slug'],
                    'model' => $vehicle['model_slug'],
                    'part' => $part->slug,
                ]),
            ];
        }

        return $applications;
    }

    /**
     * @return list<array{label: string, company_slug: string, car_slug: string, model_slug: string}>
     */
    private function vehicleApplicationMatrix(): array
    {
        /** @var list<array{label: string, company_slug: string, car_slug: string, model_slug: string}> $matrix */
        $matrix = $this->rememberApplicationData('part-page:vehicle-application-matrix:v1', function (): array {
            $applications = [];
            $companies = Company::query()
                ->with([
                    'cars' => fn ($query) => $query->select(['id', 'company_id', 'name', 'slug']),
                    'cars.models' => fn ($query) => $query->select(['models.id', 'models.name', 'models.slug']),
                ])
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            foreach ($companies as $company) {
                foreach ($company->cars->sortBy('name') as $car) {
                    foreach ($car->models->sortBy('name') as $model) {
                        $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;

                        $applications[] = [
                            'label' => trim($company->name.' '.$car->name.' '.$modelName),
                            'company_slug' => $company->slug,
                            'car_slug' => $car->slug,
                            'model_slug' => $model->slug,
                        ];
                    }
                }
            }

            return $applications;
        });

        return $matrix;
    }

    private function rememberApplicationData(string $key, callable $callback): mixed
    {
        if (app()->environment('testing')) {
            return $callback();
        }

        return Cache::remember($key, self::APPLICATION_CACHE_TTL, $callback);
    }

    private function sanitizeDescription(?string $description, Part $part): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['rn', 'xxx', 'ط·ط·ط·', 'ط¸ط¸ط¸'],
            ['', $part->partsCategory?->name ?? '', ''],
            $description,
        );
    }
}
