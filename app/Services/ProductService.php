<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Company;
use App\Models\Image;
use App\Models\Part;
use App\Models\Shop;
use App\Models\State;
use App\Support\ShopImageUrlBuilder;
use App\Support\VehicleCatalogBreadcrumbs;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * @return array{
     *     company: Company,
     *     car: Car,
     *     model: CarModel,
     *     part: Part,
     *     repairCards: list<array{type: string, cost: ?int, wage_name: ?string}>,
     *     shops: Collection<int, Shop>,
     *     shopFilterStates: Collection<int, State>,
     *     title: string,
     *     breadcrumbs: list<array<string, mixed>>,
     *     repairLocator: ?array{
     *         category: \App\Models\RepairCategory,
     *         carName: string,
     *         buttonLabel: string,
     *         states: Collection<int, State>,
     *         citiesByState: array<int, list<array{id: int, name: string}>>,
     *         defaultStateId: ?int,
     *     },
     *     telegramTitle: string,
     *     telegramUrl: string,
     *     signupUrl: string,
     * }
     */
    public function getProductPageData(Company $company, Car $car, CarModel $model, Part $part,): array {
        $car->description = $this->sanitizeDescription($car->description, $company, $car);
        $part->description = $this->sanitizeDescription($part->description, $company, $car);

        $repairCards = $this->buildRepairCards($part, $company);
        $shops = $this->loadShopsForPart($part, $car->company_id);

        $shops->each(function (Shop $shop) use ($company, $car): void {
            $shop->description = $this->sanitizeDescription($shop->description, $company, $car);
            $this->loadImagesForShops($shop);
        });

        $car->name = strtoupper($car->name);

        $title = $this->buildTitle($part, $company, $car, $model);
        $telegramCta = $this->buildTelegramCta($company, $car);
;

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
            'part' => $part,
            'repairCards' => $repairCards,
            'shops' => $shops,
            'shopFilterStates' => $this->shopFilterStates($shops),
            'title' => $title,
            'breadcrumbs' => VehicleCatalogBreadcrumbs::build(
                company: $company,
                car: $car,
                model: $model,
                part: $part,
                terminalLabel: $title,
            ),
            'repairLocators' => $this->buildRepairLocatorContext($part, $car),
            'telegramTitle' => $telegramCta['title'],
            'telegramUrl' => $telegramCta['url'],
            'telegramName' => $telegramCta['name'],
            'signupUrl' => route('page.show', 'register'),
        ];
    }

    /**
     * @return array{title: string, url: string}
     */
    private function buildTelegramCta(Company $company, Car $car): array
    {
        return [
            'name' => $company->name.' '.$car->name,
            'title' => 'به گروه تلگرام '.$company->name.' '.$car->name.' سواران بپیوندید',
            'url' => $company->links()->where('link_type', LinkType::Telegram)->first()->name??'#',
        ];
    }

    /**
     * @return ?array{
     *     category: \App\Models\RepairCategory,
     *     carName: string,
     *     buttonLabel: string,
     *     states: Collection<int, State>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     *     defaultStateId: ?int,
     * }
     */
    private function buildRepairLocatorContext(Part $part, Car $car): ?array
    {
        $categories = $part->repairCategories;

        if ($categories === null || count($categories) <= 0) {
            return null;
        }

        $location = $this->locationFilterData();


        $repairLocators = [];
        foreach ($categories as $category) {
            $repairLocators[] = [
                'category' => $category,
                'carName' => $car->name,
                'buttonLabel' => "مشاهده خدمات {$category->name} {$car->name} در محدوده شما",
                'states' => $location['states'],
                'citiesByState' => $location['citiesByState'],
                'defaultStateId' => null,

            ];
        }
        return $repairLocators;
    }

    /**
     * @return array{
     *     states: Collection<int, State>,
     *     citiesByState: array<int, list<array{id: int, name: string}>>,
     * }
     */
    private function locationFilterData(): array
    {
        return [
            'states' => State::query()->orderBy('name')->get(['id', 'name']),
            'citiesByState' => City::query()
                ->orderBy('name')
                ->get(['id', 'name', 'state_id'])
                ->groupBy('state_id')
                ->map(fn (Collection $cities) => $cities->map(fn (City $city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                ])->values()->all())
                ->all(),
        ];
    }

    private function loadImagesForShops(Shop $shop): void
    {
        $shop->images->each(function (Image $image) use ($shop) : void {
            if($image->type === ImageType::Cover)
            {
                $shop->cover = ShopImageUrlBuilder::build('shop', $image->type, $shop->id, $image->path);
            }
            elseif($image->type === ImageType::Logo)
            {
                $shop->logo = ShopImageUrlBuilder::build('shop', $image->type, $shop->id, $image->path);
            }

            $image->save();
        });
    }

    private function sanitizeDescription(?string $description, Company $company, Car $car): ?string
    {
        if ($description === null || $description === '') {
            return $description;
        }

        return str_replace(
            ['ظظظ', 'rn', 'ططط'],
            [$company->name, '', $car->name],
            $description,
        );
    }

    private function buildTitle(Part $part, Company $company, Car $car, CarModel $model): string
    {
        $modelName = is_numeric($model->name) ? 'سال '.$model->name : $model->name;

        return $part->name.' '.$company->name.' '.$car->name.' '.$modelName;
    }

    /**
     * @return list<array{type: string, cost: ?int, wage_name: ?string}>
     */
    private function buildRepairCards(Part $part, Company $company): array
    {
        $cards = [];
        $wages = $part->wages->values();
        foreach ($wages as $wage) {

            $cards[$wage->name] = [
                'cost' => $wage
                    ? (int) (($wage->variable * ($wage->coefficient??1) * $company->wage_strike) * 100000)
                    : null,
                'wage_name' => $wage?->name,
            ];
        }

        return $cards;
    }

    /** @return Collection<int, Shop> */
    private function loadShopsForPart(Part $part, int $company_id): Collection
    {
        if(in_array($company_id, [1, 2]))
        {
            $query = fn () => Shop::whereIn('id', [1, 2, 3]);
        }
        else
        {
            $query = fn () => Shop::query();
        }
        $query = fn() =>$query()
            ->visibleUnderProduct()
            ->ordered()
            ->with(['phones', 'links', 'state', 'images'])
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating');

        $shops = $query()
            ->whereHas('parts', fn ($q) => $q->whereKey($part->id))
            ->get();

        if ($shops->isEmpty() && $company_id) {
            $shops = $query()
                ->whereHas(
                    'companies',
                    fn ($q) => $q->where('companies.id', $company_id),
                )
                ->whereHas('images', fn ($q) => $q->where('type', ImageType::Logo))
                ->get();
        }

        return $shops;
    }

    /** @return Collection<int, State> */
    private function shopFilterStates(Collection $shops): Collection
    {
        return $shops
            ->filter(fn (Shop $shop): bool => $shop->state_id !== null)
            ->map(fn (Shop $shop) => $shop->state)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }
}
