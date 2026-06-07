<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $company, string $car, string $model, string $part): View
    {
        $company = Company::where('slug', $company)->firstOrFail();

        $car = Car::query()
            ->where('slug', $car)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $model = CarModel::query()
            ->where('slug', $model)
            ->whereHas('cars', fn ($query) => $query->where('cars.id', $car->id))
            ->firstOrFail();

        $part = Part::query()
            ->where('slug', $part)
            ->with(['partsCategory', 'repairCategories', 'wages'])
            ->firstOrFail();

        $repairCards = $this->buildRepairCards($part, $company);
        $shops = $this->loadShopsForPart($part);

        $model_name = is_numeric($model->name) ? 'سال ' . $model->name : $model->name;
        $title = $part->name . ' ' . $company->name . ' ' . $car->name . ' ' . $model_name;

        return view('product.show', compact(
            'company',
            'car',
            'model',
            'part',
            'repairCards',
            'shops',
            'title'
        ));
    }

    /**
     * @return list<array{type: string, cost: ?int, wage_name: ?string}>
     */
    private function buildRepairCards(Part $part, Company $company): array
    {
        $cards = [];
        $wages = $part->wages->values();
        $categories = $part->repairCategories->values();

        for ($i = 0; $i < 3; $i++) {
            $category = $categories->get($i);
            $wage = $wages->get($i) ?? $wages->first();

            if ($category === null && $wage === null) {
                break;
            }

            $cards[] = [
                'type' => $category?->name ?? $wage->name,
                'cost' => $wage
                    ? (int) round($wage->variable * $wage->coefficient * $company->wage_strike)
                    : null,
                'wage_name' => $wage?->name,
            ];
        }

        return $cards;
    }

    /** @return Collection<int, Shop> */
    private function loadShopsForPart(Part $part): Collection
    {
        $query = fn () => Shop::query()
            ->with(['phones', 'links', 'state'])
            ->withAvg(['comments as average_rating' => fn ($q) => $q->where('confirmed', true)], 'rating')
            ->orderBy('order');

        $shops = $query()
            ->whereHas('parts', fn ($q) => $q->where('parts.id', $part->id))
            ->get();

        if ($shops->isEmpty() && $part->parts_category_id) {
            $shops = $query()
                ->whereHas('partsCategories', fn ($q) => $q->where('parts_categories.id', $part->parts_category_id))
                ->limit(3)
                ->get();
        }

        return $shops;
    }
}
