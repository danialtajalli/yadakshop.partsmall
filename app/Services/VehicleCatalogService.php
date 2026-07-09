<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\ModelCategory;
use App\Models\Part;
use App\Support\CarModelLabel;
use App\Support\CatalogUrls;
use App\Support\ModelCategoryLabel;
use App\Support\ShopImageUrlBuilder;
use App\Support\VehicleCatalogBreadcrumbs;
use App\Support\VehicleCatalogContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VehicleCatalogService
{
    private const PARTS_PER_PAGE = 24;

    /**
     * @return array{
     *     companies: Collection<int, Company>,
     *     context: VehicleCatalogContext,
     *     breadcrumbs: list<array<string, mixed>>,
     *     title: string,
     * }
     */
    public function getCompaniesIndexData(Request $request): array
    {
        $context = VehicleCatalogContext::fromRequest($request);

        $companies = Company::query()
            ->with(['images', 'cars.models'])
            ->withCount('cars')
            ->orderBy('id')
            ->get();

        $companies->each(function (Company $company): void {
            $logo = $company->images->firstWhere('type', ImageType::Logo);

            $company->logo_url = $logo
                ? ShopImageUrlBuilder::companyLogoUrl($logo)
                : null;
        });

        return [
            'companies' => $companies,
            'context' => $context,
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                terminalLabel: 'کمپانی ها',
            ),
            'title' => 'لیست کمپانی‌ها',
        ];
    }

    /**
     * @return array{
     *     cars: Collection<int, Car>,
     *     companies: Collection<int, Company>,
     *     context: VehicleCatalogContext,
     *     breadcrumbs: list<array<string, mixed>>,
     *     title: string,
     *     description: string,
     * }
     */
    public function getCarsIndexData(Request $request, ?string $companySlug = null): array
    {
        $context = VehicleCatalogContext::fromRequest($request, $companySlug);

        if ($companySlug && $context->company === null) {
            throw (new ModelNotFoundException)->setModel(Company::class, [$companySlug]);
        }

        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $carsQuery = Car::query()
            ->with('company')
            ->withCount('models')
            ->orderBy('name');

        if ($context->company !== null) {
            $carsQuery->where('company_id', $context->company->id);
        }

        $cars = $carsQuery->get();

        $title = $context->company !== null
            ? 'خودروهای '.$context->company->name
            : 'همه خودروها';

        $description = 'خودروی مورد نظر را انتخاب کنید یا ابتدا برند را فیلتر کنید.';

        return [
            'cars' => $cars,
            'companies' => $companies,
            'context' => $context,
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $context->company,
            ),
            'title' => $title,
            'description' => $description,
        ];
    }

    /**
     * @return array{
     *     models: Collection<int, array{model: CarModel, label: string, url: string}>,
     *     modelCategoryGroups: Collection<int, array{
     *         category: ?ModelCategory,
     *         label: string,
     *         slug: string,
     *         models: Collection<int, array{model: CarModel, label: string, url: string}>,
     *     }>,
     *     cars: Collection<int, Car>,
     *     companies: Collection<int, Company>,
     *     context: VehicleCatalogContext,
     *     breadcrumbs: list<array<string, mixed>>,
     *     title: string,
     *     description: string,
     * }
     */
    public function getModelsIndexData(Request $request, ?string $company = null, ?string $car = null): array
    {
        $context = VehicleCatalogContext::fromRequest($request, $company ?? null, $car ?? null);

        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $carsQuery = Car::query()->with('company')->orderBy('name');

        if ($context->company !== null) {
            $carsQuery->where('company_id', $context->company->id);
        }

        $cars = $carsQuery->get();

        $modelsQuery = CarModel::query()
            ->with(['cars.company', 'category'])
            ->orderBy('name');

        if ($context->car !== null) {
            $modelsQuery->whereHas('cars', fn ($query) => $query->where('cars.id', $context->car->id));
        } elseif ($context->company !== null) {
            $modelsQuery->whereHas('cars', fn ($query) => $query->where('company_id', $context->company->id));
        }

        $models = $this->buildModelEntries($modelsQuery->get(), $context);
        $modelCategoryGroups = $this->groupModelEntriesByCategory($models);

        $title = match (true) {
            $context->company !== null && $context->car !== null => 'مدل‌های '.$context->company->name.' '.$context->car->name,
            $context->company !== null => 'مدل‌های '.$context->company->name,
            default => 'همه مدل‌ها',
        };

        $description = match (true) {
            $context->company !== null && $context->car !== null => 'مدل مورد نظر را انتخاب کنید تا به فهرست قطعات هدایت شوید.',
            $context->company !== null => 'ابتدا خودرو را انتخاب کنید یا مستقیماً مدل را بزنید.',
            default => 'مدل خودرو را انتخاب کنید یا با فیلتر برند و خودرو نتایج را محدود کنید.',
        };

        return [
            'models' => $models,
            'modelCategoryGroups' => $modelCategoryGroups,
            'cars' => $cars,
            'companies' => $companies,
            'context' => $context,
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $context->company,
                car: $context->car,
            ),
            'title' => $title,
            'description' => $description,
        ];
    }

    /**
     * @return array{
     *     parts: LengthAwarePaginator,
     *     categories: Collection<int, \App\Models\PartsCategory>,
     *     companies: Collection<int, Company>,
     *     cars: Collection<int, Car>,
     *     context: VehicleCatalogContext,
     *     breadcrumbs: list<array<string, mixed>>,
     *     title: string,
     *     description: string,
     *     filters: array{q: ?string, category: ?int},
     * }
     */
    public function getPartsIndexData(Request $request, ?string $company = null, ?string $car = null, ?string $model = null): array
    {
        $context = VehicleCatalogContext::fromRequest($request, $company ?? null, $car ?? null, $model ?? null);

        if (($model && ! $context->model) || ($car && ! $context->car) || ($company && ! $context->company)) {
            throw (new ModelNotFoundException);
        }

        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $carsQuery = Car::query()->with('company')->orderBy('name');

        if ($context->company !== null) {
            $carsQuery->where('company_id', $context->company->id);
        }

        $cars = $carsQuery->get();

        $filters = [
            'q' => $request->string('q')->trim()->toString() ?: null,
            'category' => $request->integer('category') ?: null,
        ];

        $partsQuery = Part::query()
            ->with('partsCategory')
            ->orderBy('category_description');

        if ($filters['q']) {
            $partsQuery->where('name', 'like', '%'.$filters['q'].'%');
        }

        if ($filters['category']) {
            $partsQuery->where('parts_category_id', $filters['category']);
        }

        $title = match (true) {
            $context->company !== null && $context->car !== null && $context->model !== null => 'لوازم یدکی '
                .$context->company->name.' '
                .$context->car->name.' '
                .CarModelLabel::display($context->model),
            default => 'لیست تمام قطعات',
        };

        $parts = $partsQuery->get();

        $parts->transform(function (Part $part) use ($context): Part {
            $part->setAttribute('catalog_url', $this->partUrl($part, $context));
            if($context->company !== null && $context->car !== null && $context->model !== null)
            {
                $part->setAttribute('title', $this->buildTitle($part, $context->company, $context->car, $context->model));
            }
            else
            {
                $part->setAttribute('title', $part->name);
            }
            return $part;
        });

        $description = match (true) {
            $context->model !== null => 'قطعات موجود برای این خودرو — برای مشاهده فروشگاه‌ها و قیمت کلیک کنید.',
            $context->car !== null => 'قطعات را برای خودروی انتخاب‌شده مرور کنید یا مدل را مشخص کنید.',
            $context->company !== null => 'قطعات مرتبط با برند انتخاب‌شده یا کل فهرست قطعات.',
            default => 'فهرست کامل قطعات — برای جزئیات و خودروهای مرتبط روی هر قطعه کلیک کنید.',
        };

        return [
            'parts' => $parts,
            'categories' => \App\Models\PartsCategory::query()->orderBy('name')->get(),
            'companies' => $companies,
            'cars' => $cars,
            'context' => $context,
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $context->company,
                car: $context->car,
                model: $context->model,
            ),
            'title' => $title,
            'description' => $description,
            'filters' => $filters,
        ];
    }

    private function buildTitle(Part $part, Company $company, Car $car, CarModel $model): string
    {
        $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;

        return $part->name.' '.$company->name.' '.$car->name.' '.$modelName;
    }

    public function partUrl(Part $part, VehicleCatalogContext $context): string
    {
        if ($context->company !== null && $context->car !== null && $context->model !== null) {
            return route('product.show', [
                'company' => $context->company->slug,
                'car' => $context->car->slug,
                'model' => $context->model->slug,
                'part' => $part->slug,
            ]);
        }

        return route('part.show', $part->slug);
    }

    /**
     * @param  Collection<int, CarModel>  $models
     * @return Collection<int, array{model: CarModel, label: string, url?: string}>
     */
    private function buildModelEntries(Collection $models, VehicleCatalogContext $context, bool $includeUrl = true): Collection
    {
        return $models->map(function (CarModel $model) use ($context, $includeUrl): array {
            $car = $context->car ?? $model->cars->sortBy('name')->first();
            $company = $context->company ?? $car?->company;

            $entry = [
                'model' => $model,
                'label' => $company && $car
                    ? trim($company->name.' '.$car->name.' '.CarModelLabel::display($model))
                    : CarModelLabel::display($model),
            ];

            if ($includeUrl) {
                $entry['url'] = $company && $car
                    ? CatalogUrls::parts($company->slug, $car->slug, $model->slug)
                    : CatalogUrls::models($company?->slug, $car?->slug);
            }

            return $entry;
        })->values();
    }

    /**
     * @param  Collection<int, array{model: CarModel, label: string, url?: string}>  $entries
     * @return Collection<int, array{
     *     category: ?ModelCategory,
     *     label: string,
     *     slug: string,
     *     models: Collection<int, array{model: CarModel, label: string, url?: string}>,
     * }>
     */
    private function groupModelEntriesByCategory(Collection $entries): Collection
    {
        return $entries
            ->groupBy(fn (array $entry): string => (string) ($entry['model']->category_id ?? 0))
            ->map(function (Collection $group): array {
                $category = $group->first()['model']->category;

                return [
                    'category' => $category,
                    'label' => ModelCategoryLabel::display($category),
                    'slug' => ModelCategoryLabel::slug($category),
                    'models' => $group->values(),
                ];
            })
            ->sortByDesc(fn (array $group): int => $group['models']->count())
            ->values();
    }
}
